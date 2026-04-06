<?php
// -----------------------------------------------
// email_service.php
// This file handles sending all alert emails in the system.
// It uses PHPMailer (already installed by your teammate).
// This function is called by ai_health_alert.php 
// and medication_alert.php whenever an alert needs to be sent.
// ------------------------------------------------

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer from vendor folder (installed by composer)
// vendor folder is at the project root, one level up from includes/
require_once __DIR__ . '/../vendor/autoload.php';

// ------------------------------------------------
// sendAlertEmail()
// Sends an HTML email to one recipient.
//
// Parameters:
//   $toEmail  - the recipient's email address
//   $toName   - the recipient's name (shown in email client)
//   $subject  - the email subject line
//   $body     - the HTML content of the email
//
// Returns true if sent successfully, false if it failed.
// ------------------------------------------------
function sendAlertEmail($toEmail, $toName, $subject, $body) {

    // Create a new PHPMailer instance
    // true = enable exceptions so errors are catchable
    $mail = new PHPMailer(true);

    try {
        
        // SMTP Configuration
        // Using Gmail SMTP to send emails
        // Same credentials already used in register.php
        
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ronaldo.sony1898@gmail.com'; // Gmail address
        $mail->Password   = 'jqlw fjem goyh ztam';        // Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        
        // Email sender and recipient
        
        $mail->setFrom('ronaldo.sony1898@gmail.com', 'Elder Care System');
        $mail->addAddress($toEmail, $toName); // Who receives the email

        
        // Email content
        
        $mail->isHTML(true);         // Send as HTML email
        $mail->Subject = $subject;   // Email subject line
        $mail->Body    = $body;      // HTML version of the email

        // Send the email
        $mail->send();
        return true; // Email sent successfully

    } catch (Exception $e) {
        // Log the error to PHP error log but don't crash the page
        error_log("Email sending failed to $toEmail: " . $mail->ErrorInfo);
        return false; // Email failed
    }
}
?>