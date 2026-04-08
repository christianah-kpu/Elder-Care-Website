<?php
// -----------------------------------------------
// email_service.php  
// Sends all HTML alert emails in the system.
// Called by ai_health_alert.php and medication_alert.php.
// -----------------------------------------------

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// vendor/ is at the project root, one level up from includes/
require_once __DIR__ . '/../vendor/autoload.php';

// -----------------------------------------------
// sendAlertEmail()
// Parameters:
//   $toEmail  — recipient email address
//   $toName   — recipient display name
//   $subject  — email subject line
//   $body     — HTML body content
// Returns true on success, false on failure.
// -----------------------------------------------
function sendAlertEmail($toEmail, $toName, $subject, $body) {

    $mail = new PHPMailer(true);

    try {
        // SMTP via Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ronaldo.sony1898@gmail.com';
        $mail->Password   = 'jqlw fjem goyh ztam';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom('ronaldo.sony1898@gmail.com', 'Elder Care System');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;

        // Wrap body in a clean styled container
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:650px;
                        margin:auto;border:1px solid #ddd;
                        border-radius:8px;padding:24px;'>
                {$body}
                <hr style='margin-top:30px;border:none;border-top:1px solid #eee;'>
                <p style='color:#aaa;font-size:11px;text-align:center;'>
                    Elder Care Home Management System &mdash; Automated Alert
                </p>
            </div>
        ";

        // Plain text fallback (strips HTML tags)
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</li>', '</h2>', '</h3>'], "\n", $body));

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Log error but do not crash the page
        error_log("sendAlertEmail failed to [{$toEmail}]: " . $mail->ErrorInfo);
        return false;
    }
}
?>
