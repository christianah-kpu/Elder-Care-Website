<?php
session_start();
require_once 'db_connection.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT user_id, password_hash, role, is_verified FROM users WHERE username = ?");
        $stmt->execute([$username]);    // ← no bind_param
        $user = $stmt->fetch();         // ← returns full row as array or false

        if ($user && password_verify($password, $user['password_hash'])) {
            if (!$user['is_verified']) {
                $error_message = "Your account is not verified. Please check your email.";
            } else {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role']    = $user['role'];
                header("Location: dashboard.php");
                exit;
            }
        } else {
            $error_message = "Invalid username or password.";
        }

    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    } finally {
        $stmt = null;
        $conn = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        h2 { color: #333; }
        form {
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 600px;
        }
        label { display: block; margin-bottom: 8px; color: #555; }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background-color: #0056b3; }
        p { margin-top: 15px; color: #333; }
        a { color: #007BFF; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .error-message { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Login</h2>

    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</body>
</html>