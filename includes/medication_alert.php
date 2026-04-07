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
function checkMissedMedications($conn) {

    // Get the current time to compare against scheduled times
    $now = new DateTime();

    // ---------------------------------------------------
    // Get all medications that have a scheduled time. 
    // We join the medication table with the resident table to get the resident's name for the email.
    // ---------------------------------------------------
    $stmt = $conn->query("
        SELECT 
            m.medID, 
            m.medicine_name, 
            m.residentSIN, 
            m.scheduledTime,
            m.dose,
            r.name AS residentName
        FROM medication m
        JOIN resident r ON m.residentSIN = r.residentSIN
        WHERE m.scheduledTime IS NOT NULL
    ");
    $medications = $stmt->fetchAll();

    // ---------------------------------------------------
    // Loop through each medication and check how many minutes have passed since scheduled time
    // ---------------------------------------------------
    foreach ($medications as $med) {

        // Build a full DateTime object for today's scheduled time
        $scheduled   = new DateTime(date('Y-m-d') . ' ' . $med['scheduledTime']);

        // Calculate how many minutes have passed
        $diffMinutes = ($now->getTimestamp() - $scheduled->getTimestamp()) / 60;

        // If scheduled time hasn't passed by at least 1 minute yet, iot will skip this medication - it's not due yet
        if ($diffMinutes < 1) {
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
            
            // No log exists yet for today - determine the status based on how late it is
            if ($diffMinutes > 15) {
                $status = 'missed';   // more than 15 min late
            } elseif ($diffMinutes >= 1) {
                $status = 'delayed';  // 1 to 15 min late
            }

            // Insert a new log entry into medication_log
            if ($status) {
                $conn->prepare("
                    INSERT INTO medication_log 
                        (medID, residentSIN, status, alert_sent)
                    VALUES (?, ?, ?, 0)
                ")->execute([$med['medID'], $med['residentSIN'], $status]);
            }

        } elseif (
            $existingLog['alert_sent'] == 0 &&
            in_array($existingLog['status'], ['missed', 'delayed'])
        ) {
            
            // A log exists but alert was not sent yet, so we still need to send the alert. If there is nothing to alert about, it will skip.
            
            $status = $existingLog['status'];
        }
    }}