<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$page_title = "Admin - Manage Users";

require '../vendor/autoload.php';
session_start();
require_once '../includes/db_connection.php';
include '../includes/header.php';

// FLASH MESSAGE
if (isset($_SESSION['flash_message'])) {
    echo "<div class='alert alert-success text-center'>".$_SESSION['flash_message']."</div>";
    unset($_SESSION['flash_message']);
}

// CHECK ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// -------------------------
// HANDLE USER STATUS / DELETE
// -------------------------
if (isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];
    $current_status = $_POST['current_status'];
    $new_status = ($current_status === 'active') ? 'suspended' : 'active';

    $stmt = $conn->prepare("UPDATE user_status SET status=? WHERE user_id=?");
    $stmt->execute([$new_status, $user_id]);

    if ($stmt->rowCount() === 0) {
        $stmt = $conn->prepare("INSERT INTO user_status (user_id, status) VALUES (?, ?)");
        $stmt->execute([$user_id, $new_status]);
    }

    $_SESSION['flash_message'] = "User status updated to $new_status.";
    header("Location: manage_users.php");
    exit;
}

if(isset($_POST['delete_acct'])) {
    $user_id = $_POST['user_id'];
    $current_status = $_POST['current_status'];

    if ($current_status === 'suspended') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
        $stmt->execute([$user_id]);
    }

    $_SESSION['flash_message'] = "User successfully deleted.";
    header("Location: manage_users.php");
    exit;
}

// -------------------------
// CREATE UPLOAD FOLDER CROSS-PLATFORM
// -------------------------
function ensureUploadFolder($folder) {
    $uploadDir = __DIR__ . "/../uploads/$folder/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    @chmod($uploadDir, 0777);
    return $uploadDir;
}

// -------------------------
// IMAGE UPLOAD FUNCTION
// -------------------------
function uploadImage($file, $folder) {
    $maxFileSize = 10 * 1024 * 1024; // 10 MB
    $allowedTypes = ['jpg','jpeg','png','gif'];

    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    if ($file['size'] > $maxFileSize) return null;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) return null;

    $uploadDir = ensureUploadFolder($folder);
    $filename = uniqid() . "." . $ext;
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) return null;

    return $filename;
}

// -------------------------
// CREATE USER
// -------------------------
if(isset($_POST['create_user'])){
    $role = $_POST['role'];
    $username = trim($_POST['username']);
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Limit residentSIN to 9 chars, phone max 15
    if(isset($_POST['residentSIN'])) $_POST['residentSIN'] = substr($_POST['residentSIN'],0,9);
    if(isset($_POST['phone'])) $_POST['phone'] = substr($_POST['phone'],0,15);
    if(isset($_POST['cg_phone'])) $_POST['cg_phone'] = substr($_POST['cg_phone'],0,15);

    $errors = [];
    if(empty($username) || empty($fname) || empty($lname) || empty($email) || empty($password)) {
        $errors[] = "All basic fields are required.";
    }
    if($role === 'resident'){
        if(empty($_POST['residentSIN']) || empty($_POST['DoB']) || empty($_POST['phone']) || empty($_POST['ECname']) || empty($_POST['ECphone']) || empty($_POST['ECemail'])){
            $errors[] = "All resident fields are required.";
        }
    } else if($role === 'caregiver'){
        if(empty($_POST['cg_phone'])){
            $errors[] = "All caregiver fields are required.";
        }
    }

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username=? OR email=?");
    $stmt->execute([$username, $email]);
    if($stmt->fetch()) $errors[] = "Username or Email already exists.";

    if(empty($errors)){
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username,email,password_hash,role,is_verified) VALUES (?,?,?,?,0)");
        $stmt->execute([$username,$email,$hashed,$role]);
        $user_id = $conn->lastInsertId();

        $profileImage = null;
        if($role === 'resident' && isset($_FILES['residentImage']) && $_FILES['residentImage']['name']!==''){
            $profileImage = uploadImage($_FILES['residentImage'],'residents');
        } else if($role === 'caregiver' && isset($_FILES['caregiverImage']) && $_FILES['caregiverImage']['name']!==''){
            $profileImage = uploadImage($_FILES['caregiverImage'],'caregivers');
        }

        if($role === 'resident'){
            $stmt = $conn->prepare("INSERT INTO resident (residentSIN,user_id,phone,profilePhoto,ECname,ECphone,ECemail,fname,lname,DoB) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $_POST['residentSIN'],
                $user_id,
                $_POST['phone'],
                $profileImage,
                $_POST['ECname'],
                $_POST['ECphone'],
                $_POST['ECemail'],
                $fname,
                $lname,
                $_POST['DoB']
            ]);
        } else if($role === 'caregiver'){
            $stmt = $conn->prepare("INSERT INTO caregiver (user_id,phone,profilePhoto,fname,lname) VALUES (?,?,?,?,?)");
            $stmt->execute([
                $user_id,
                $_POST['cg_phone'],
                $profileImage,
                $fname,
                $lname
            ]);
        }

        // Generate verification token
        $verificationToken = bin2hex(random_bytes(16));
        $createdAt = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO verification_tokens (user_id, token, created_at) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $verificationToken, $createdAt]);

        // Send verification email
        $verificationLink = "http://localhost/Elder-Care-Website/verify.php?token=$verificationToken";
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ronaldo.sony1898@gmail.com';
            $mail->Password   = 'jqlw fjem goyh ztam';
            $mail->Port       = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->setFrom('ronaldo.sony1898@gmail.com', 'Sonny');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Account';
            $mail->Body    = "Click the link to verify your account: <a href='$verificationLink'>$verificationLink</a>";
            $mail->AltBody = "Copy and paste this link to verify your account: $verificationLink";
            $mail->send();
            $_SESSION['flash_message'] = "User created successfully! Verification email sent to $email.";
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "User created but email failed to send. Error: {$mail->ErrorInfo}";
        }

        header("Location: manage_users.php");
        exit;
    } else {
        foreach($errors as $err){
            echo "<div class='alert alert-danger text-center'>$err</div>";
        }
    }

}

// -------------------------
// FETCH USERS
// -------------------------
$stmt = $conn->query("
SELECT u.user_id,u.username,u.email,u.role,COALESCE(us.status,'active') as status
FROM users u
LEFT JOIN user_status us ON u.user_id=us.user_id
");
$users = $stmt->fetchAll();
?>

<div class="container py-4">

<!-- COLLAPSIBLE ADD NEW USER -->
<div class="card shadow-sm mb-4">
<div class="card-header d-flex justify-content-between align-items-center bg-primary text-white" style="cursor:pointer;" id="addUserHeader">
    <strong>Add New User</strong>
    <span id="toggleIcon" style="transition: transform 0.3s;">▼</span>
</div>
<div class="card-body collapse border-top p-3" id="addUserBody">
<form method="POST" enctype="multipart/form-data" id="createUserForm">

<!-- ROLE TOGGLE -->
<div class="mb-3 d-flex align-items-center">
<label class="form-label me-2 mb-0"><strong>User Type:</strong></label>
<div id="roleToggle" class="btn-group btn-group-sm">
    <button type="button" class="btn btn-primary" id="residentBtn">Resident</button>
    <button type="button" class="btn btn-success" id="caregiverBtn">Caregiver</button>
</div>
<input type="hidden" name="role" id="roleInput" value="resident">
</div>

<div class="row g-3">
<div class="col-md-3"><label>Username</label><input name="username" class="form-control form-control-sm" required></div>
<div class="col-md-3"><label>First Name</label><input name="fname" class="form-control form-control-sm" required></div>
<div class="col-md-3"><label>Last Name</label><input name="lname" class="form-control form-control-sm" required></div>
<div class="col-md-3"><label>Email</label><input type="email" name="email" class="form-control form-control-sm" required></div>
<div class="col-md-3"><label>Password</label><input type="password" name="password" class="form-control form-control-sm" required></div>
</div>

<hr>

<!-- RESIDENT FIELDS -->
<div id="residentFields" class="row g-3">
<div class="col-md-3"><label>Health Number</label><input name="residentSIN" class="form-control form-control-sm" maxlength="9"></div>
<div class="col-md-3"><label>Date of Birth</label><input type="date" name="DoB" class="form-control form-control-sm"></div>
<div class="col-md-3"><label>Phone</label><input name="phone" class="form-control form-control-sm" maxlength="15"></div>
<div class="col-md-3"><label>Profile Image</label><input type="file" name="residentImage" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.gif"></div>
<div class="col-md-3"><label>Emergency Name</label><input name="ECname" class="form-control form-control-sm"></div>
<div class="col-md-3"><label>Emergency Phone</label><input name="ECphone" class="form-control form-control-sm" maxlength="15"></div>
<div class="col-md-3"><label>Emergency Email</label><input name="ECemail" class="form-control form-control-sm"></div>
</div>

<!-- CAREGIVER FIELDS -->
<div id="caregiverFields" class="row g-3" style="display:none;">
<div class="col-md-3"><label>Phone</label><input name="cg_phone" class="form-control form-control-sm" maxlength="15"></div>
<div class="col-md-3"><label>Profile Image</label><input type="file" name="caregiverImage" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.gif"></div>
</div>

<div class="text-end mt-3">
<button type="submit" name="create_user" class="btn btn-sm btn-success">Create User</button>
</div>
</form>
</div>
</div>

<!-- ALL USERS -->
<h2 class="mb-3">All Users</h2>
<div class="card shadow-sm p-2">
<div style="max-height:400px; overflow-y:auto;">
<table class="table table-bordered text-center table-sm mb-0">
<thead class="table-light sticky-top">
<tr>
<th>Username</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach($users as $u): ?>
<tr class="<?= $u['status']==='suspended'?'table-secondary':'' ?>">
<td><?= htmlspecialchars($u['username']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= htmlspecialchars($u['role']) ?></td>
<td><span class="badge <?= $u['status']==='active'?'bg-success':'bg-danger' ?>"><?= ucfirst($u['status']) ?></span></td>
<td>
<form method="POST" style="display:inline-block;">
<input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
<input type="hidden" name="current_status" value="<?= $u['status'] ?>">
<button type="submit" name="toggle_status" class="btn btn-sm <?= $u['status']==='active'?'btn-danger':'btn-success' ?>"><?= $u['status']==='active'?'Suspend':'Unsuspend' ?></button>
</form>
<form method="POST" style="display:inline-block;">
<input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
<input type="hidden" name="current_status" value="<?= $u['status'] ?>">
<button type="submit" name="delete_acct" class="btn btn-sm btn-danger" <?= $u['status']==='active'?'hidden':'' ?>>Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<script>
const residentBtn = document.getElementById('residentBtn');
const caregiverBtn = document.getElementById('caregiverBtn');
const roleInput = document.getElementById('roleInput');
const residentFields = document.getElementById('residentFields');
const caregiverFields = document.getElementById('caregiverFields');
const addUserHeader = document.getElementById('addUserHeader');
const addUserBody = document.getElementById('addUserBody');
const toggleIcon = document.getElementById('toggleIcon');

addUserHeader.addEventListener('click', () => {
    addUserBody.classList.toggle('show');
    toggleIcon.style.transform = addUserBody.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
});

function setRole(role){
    roleInput.value = role;
    if(role==='resident'){
        residentFields.style.display='flex';
        caregiverFields.style.display='none';
        residentBtn.classList.add('btn-primary','text-white');
        residentBtn.classList.remove('btn-outline-primary');
        caregiverBtn.classList.remove('btn-success','text-white');
        caregiverBtn.classList.add('btn-outline-success');
    } else {
        caregiverFields.style.display='flex';
        residentFields.style.display='none';
        caregiverBtn.classList.add('btn-success','text-white');
        caregiverBtn.classList.remove('btn-outline-success');
        residentBtn.classList.remove('btn-primary','text-white');
        residentBtn.classList.add('btn-outline-primary');
    }
}
setRole('resident');
residentBtn.onclick=()=>setRole('resident');
caregiverBtn.onclick=()=>setRole('caregiver');

document.getElementById('createUserForm').addEventListener('submit',function(e){
    const role = roleInput.value;
    if(role==='resident'){
        const fields = ['residentSIN','DoB','phone','ECname','ECphone','ECemail'];
        for(let f of fields){
            if(!document.querySelector(`[name="${f}"]`).value){
                alert('Please fill all resident fields!');
                e.preventDefault();
                return false;
            }
        }
    } else if(role==='caregiver'){
        if(!document.querySelector('[name="cg_phone"]').value){
            alert('Please fill caregiver phone!');
            e.preventDefault();
            return false;
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>