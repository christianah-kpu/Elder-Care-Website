<?php
// register.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$page_title = "Sign Up";

require 'vendor/autoload.php';
require './includes/db_connection.php';
require './includes/header.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $fname            = trim($_POST['fname']);
    $lname            = trim($_POST['lname']);
    $phone            = trim($_POST['phone']);
    $role             = 'family';

    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required.";
    } else if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    } else if (empty($password)) {
        $errors[] = "Password is required.";
    } else if (strlen($password) < 10) {
        $errors[] = "Password must be at least 10 characters long.";
    } else if (!preg_match('/[!@#$%^&*()-+]/', $password)) {
        $errors[] = "Password must contain at least one special character.";       
    } else if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    } else if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    } else if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } else if (empty($fname) || empty($lname) || empty($phone)) {
        $errors[] = "First name, last name, and phone are required.";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $createdAt      = date('Y-m-d H:i:s');

        try {
            // Check if username or email already exists
            $check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $check->execute([$username, $email]);

            if ($check->fetch()) {
                $errors[] = "Username or email already taken. Please choose another.";
            } else {
                // Insert user into users table
                $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, is_verified, created_at) 
                                        VALUES (?, ?, ?, ?, 0, ?)");
                $stmt->execute([$username, $email, $hashedPassword, $role, $createdAt]);
                $userId = $conn->lastInsertId();

                // Insert into family_requests table
                $stmt2 = $conn->prepare("INSERT INTO family_requests (user_id, fname, lname, phone) 
                                         VALUES (?, ?, ?, ?)");
                $stmt2->execute([$userId, $fname, $lname, $phone]);

                // Generate verification token
                $verificationToken = bin2hex(random_bytes(16));
                $stmt3 = $conn->prepare("INSERT INTO verification_tokens (user_id, token, created_at) 
                                         VALUES (?, ?, ?)");
                $stmt3->execute([$userId, $verificationToken, $createdAt]);

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
                    $success = 'Registration successful! Please check your email to verify your account. Your account is pending admin approval.';
                } catch (Exception $e) {
                    $errors[] = "Error sending email: {$mail->ErrorInfo}";
                }
            }

        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow p-4" style="width: 100%; max-width: 500px;">
        <h3 class="text-center mb-4">Sign Up</h3>

        <!-- Display errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error) echo "<p class='mb-1'>$error</p>"; ?>
            </div>
        <?php endif; ?>

        <!-- Display success message -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="fname" class="form-label">First Name</label>
                <input type="text" id="fname" name="fname" class="form-control" required value="<?= htmlspecialchars($_POST['fname'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="lname" class="form-label">Last Name</label>
                <input type="text" id="lname" name="lname" class="form-control" required value="<?= htmlspecialchars($_POST['lname'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" id="phone" name="phone" class="form-control" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>

        <p class="text-center mt-3">
            Already have an account? <a href="./login.php">Login</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>