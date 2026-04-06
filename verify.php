<?php
require './includes/db_connection.php';

if (isset($_GET['token'])) {
    $verificationToken = $_GET['token'];

    try {
        // Check if the token exists
        $stmt = $conn->prepare("SELECT user_id FROM verification_tokens WHERE token = ?");
        $stmt->execute([$verificationToken]);
        $row = $stmt->fetch(); // Returns row as array or false if not found

        if ($row) {
            $userId = $row['user_id'];

            // Verify the user
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Delete the token
            $stmt = $conn->prepare("DELETE FROM verification_tokens WHERE user_id = ?");
            $stmt->execute([$userId]);

             // after token is validated, show next step messages
            echo "<div style='text-align:center; margin-top:50px;'>";
            echo "<h2>✅ Email Verified Successfully!</h2>";
            echo "<p>Your account has been verified.</p>";
            echo "<p><strong>Please wait for admin approval before logging in.</strong></p>";
            echo "<a href='login.php'>Go to Login</a>";
            echo "</div>";

        } else {
            echo "Invalid or expired token.";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        $stmt = null;
        $conn = null;
    }

} else {
    echo "No token provided.";
}
?>