<?php
// ----------------------------------
// medication_alert.php
// This file checks all scheduled medications and detects if any are missed or delayed based on these rules
// If a medication's scheduled time has passed by more than 15 minutes and it has not been logged as taken, it is "missed"
// It then sends an email alert to the assigned caregiver.
// ----------------------------------

// Load the email sending function
require_once __DIR__ . '/email_service.php';


// ----------------------------------
// checkMissedMedications() is a main function that checks all medications scheduled for today and sends alerts for any that are missed or delayed.
// Parameters:
// $conn - the database connection from db_connection.php
// ----------------------------------
function checkMissedMeds($conn) {
    // Get the current day, time, and the day for yesterday:
    date_default_timezone_set("America/Vancouver");
    $then = date('Y-m-d',strtotime("yesterday"));
    $now =  date('Y-m-d');
    $time = date('H:i');
    // ---------------------------------------------------
    // Get all medications that have a scheduled time and are still pending. 
    // We also join the medication table with the resident table to get the resident's
    //name for the email.
    // ---------------------------------------------------
    $stmt = $conn->query("SELECT me.entryID, CONCAT(r.fname, ' ', r.lname) AS resName, m.medID, m.medName, m.timeScheduled, r.residentSIN
                            FROM medication_entry me
                            LEFT JOIN medication m ON me.medID = m.medID
                            LEFT JOIN resident r ON m.residentSIN = r.residentSIN
                            WHERE m.timeScheduled IS NOT NULL AND me.status='pending'");
    $medications = $stmt->fetchAll();

    // ---------------------------------------------------
    // Check each medication to determine if it is late
    // If the medication was scheduled for the previous day, it is automatically
    // marked 'missed' as well
    // ---------------------------------------------------
    foreach ($medications as $med) {
        $timediff = strtotime($time)-strtotime($med['timeScheduled']);
        if($timediff << 900) {
            continue;
        }

        // ---------------------------------------------------
        // Check if we already logged this medication today in the medication_log table.
        // So we don't want to log or alert for the same medication twice in the same day.
        // Therefore, the code below help us prevent it by checking if a log entry already exists for this medication and resident for today.
        // ---------------------------------------------------
        $logCheck = $conn->prepare("
            SELECT status, alert_sent 
            FROM medication_log
            WHERE medID = ? 
              AND DATE(logged_at) = CURDATE()
            LIMIT 1
        ");
        $logCheck->execute([$med['medID']]);
        $existingLog = $logCheck->fetch();

        $status = null; // will be 'missed', 'delayed', or null

        if (!$existingLog) {
            $body = '';
            // No log exists yet for today - determine the status based on how late it is
            if ($timediff >= 900) {
                $status = 'missed';   // more than 15 min late
                $stmt = $conn->prepare("UPDATE medication_entry
                        SET status = ?
                        WHERE entryID = ?");
                $stmt->execute([$status, $_POST['entry']]);
            } elseif ($timediff >= 60) {
                $status = 'delayed';  // 1 to 15 min late
            }

            // Insert a new log entry into medication_log
            if ($status) {
                $conn->prepare("
                    INSERT INTO medication_log 
                        (medID, status, alert_sent,logged_at)
                    VALUES (?, ?, 0, DATE(NOW()))
                ")->execute([$med['medID'], $status]);
            }

        } elseif (
            $existingLog['alert_sent'] == 0 &&
            in_array($existingLog['status'], ['missed', 'delayed'])
        ) {
            // A log exists but alert was not sent yet, so we still need to send the alert.
            //If there is nothing to alert about, it will skip.

            $status = $existingLog['status'];
            $toEmail = '';
            $toName = '';
            $subject = 'Missed or Delayed Medication';
            $body = "Notice: Resident {$med['resName']} has missed or experiences a delay in recieving their
                    medication: {$med['medName']}!";
            $stmt = $conn->prepare("SELECT c.fname, c.lname, u.email FROM caregiver c
									LEFT JOIN users u ON c.user_id = u.user_id
                                    LEFT JOIN assignment a on c.empID = a.empID
                                    WHERE a.residentSIN = ?");
            $stmt->execute([$med['residentSIN']]);
            $recipients = $stmt->fetchAll();
            foreach($recipients AS $recip) {
                $toEmail = $recip['email'];
                $toName = $recip['fname'] . ' ' . $recip['lname'];
                sendAlertEmail($toEmail, $toName, $subject, $body);
            }
            $stmt = $conn->prepare("UPDATE medication_log
                        SET alert_sent = 1
                        WHERE medID = ? AND logged_at = DATE(NOW())");
            $stmt->execute([$med['medID']]);
        }
    }
    }
