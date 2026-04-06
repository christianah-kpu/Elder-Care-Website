<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'db_connection.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role             = 'family';

    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $createdAt      = date('Y-m-d H:i:s');

        try {
            // Check if username or email already exists
            $check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $check->execute([$username, $email]);

            if ($check->fetch()) {
                echo "<p style='color:red;'>Username or email already taken. Please choose another.</p>";
            } else {
                // Insert user into the database
                $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, is_verified, created_at) 
                                        VALUES (?, ?, ?, ?, 0, ?)");
                $stmt->execute([$username, $email, $hashedPassword, $role, $createdAt]);

                $userId = $conn->lastInsertId();

                // Generate and insert verification token
                $verificationToken = bin2hex(random_bytes(16));
                $stmt = $conn->prepare("INSERT INTO verification_tokens (user_id, token, created_at) 
                                        VALUES (?, ?, ?)");
                $stmt->execute([$userId, $verificationToken, $createdAt]);

                // Send verification email
                $verificationLink = "http://localhost/INFO2413_project/verify.php?token=$verificationToken";
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
                    echo "<p style='color:green;'>Registration successful! Please check your email to verify your account.</p>";
                } catch (Exception $e) {
                    echo "<p style='color:red;'>Error sending email: {$mail->ErrorInfo}</p>";
                }
            }

        } catch (PDOException $e) {
            echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
        } finally {
            $stmt = null;
            $conn = null;
        }
    } else {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #e3f2fd;
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
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        width: 600px;
    }
    label { display: block; margin: 10px 0 5px; color: #555; }
    input[type="text"],
    input[type="email"],
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
        background-color: #28a745;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
    }
    button:hover { background-color: #218838; }
    p { margin-top: 15px; color: #333; }
</style>

<h2>User Registration</h2>
<form method="POST" action="">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br>

    <label for="confirm_password">Confirm Password:</label>
    <input type="password" id="confirm_password" name="confirm_password" required><br>

    <button type="submit">Register</button>
</form>