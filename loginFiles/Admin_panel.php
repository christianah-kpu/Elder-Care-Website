<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<p style='color:red; text-align:center;'>Access denied. Admins only.</p>";
    exit;
}

// Handle actions: Change role, suspend, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'];
    $user_id = $_POST['user_id'];

    try {
        if ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);

        } elseif ($action === 'suspend') {
            $stmt = $conn->prepare("INSERT INTO user_status (user_id, status) 
                                    VALUES (?, 'suspended') ON DUPLICATE KEY UPDATE status = 'suspended'");
            $stmt->execute([$user_id]);

        } elseif ($action === 'activate') {
            $stmt = $conn->prepare("INSERT INTO user_status (user_id, status) 
                                    VALUES (?, 'active') ON DUPLICATE KEY UPDATE status = 'active'");
            $stmt->execute([$user_id]);

        } elseif ($action === 'change_role') {
            $new_role = $_POST['new_role'];
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $stmt->execute([$new_role, $user_id]);
        }

    } catch (PDOException $e) {
        die("Error performing action: " . $e->getMessage());
    }
}

// Fetch all users
try {
    $stmt  = $conn->query("SELECT u.user_id, u.username, u.email, u.role,
                           COALESCE(us.status, 'active') as status
                           FROM users u
                           LEFT JOIN user_status us ON u.user_id = us.user_id");
    $users = $stmt->fetchAll(); // Fetch all rows into an array

} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            padding: 20px;
        }
        h2 { text-align: center; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        form   { display: inline-block; }
        select, button { margin: 5px; padding: 5px 10px; }
        button {
            cursor: pointer;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
        }
        button:hover  { background-color: #0056b3; }
        .delete       { background-color: #dc3545; }
        .delete:hover { background-color: #c82333; }
    </style>
</head>
<body>
    <h2>Admin Panel - User Management</h2>
    <table>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($users as $user): ?>  
        <tr>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['role']); ?></td>
            <td><?php echo htmlspecialchars($user['status']); ?></td>
            <td>
                <form method="POST" action="">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <select name="new_role">
                        <option value="family"  <?php if ($user['role'] === 'family')  echo 'selected'; ?>>Family Member</option>
                        <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                        <option value="caregiver"  <?php if ($user['role'] === 'caregiver')  echo 'selected'; ?>>Caregiver</option>
                        <option value="resident" <?php if ($user['role'] === 'resident') echo 'selected'; ?>>Resident</option>

                    </select>
                    <button type="submit" name="action" value="change_role">Change Role</button>
                    <?php if ($user['status'] === 'active'): ?>
                        <button type="submit" name="action" value="suspend">Suspend</button>
                    <?php else: ?>
                        <button type="submit" name="action" value="activate">Activate</button>
                    <?php endif; ?>
                    <button type="submit" name="action" value="delete" class="delete">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>
    <p style="text-align:center;"><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>