<?php
// ============================================================
// ai_health_alert.php- This is the AI health trend detection system.
// It checks the last 3 health records of a resident within the past 7 days. If 2 or more vital signs are abnormal
// Basically, in all 3 consecutive records, it sends email alerts to assigned caregivers and linked family members.
// ============================================================

require_once __DIR__ . '/email_service.php';

function checkHealthTrend($conn, $residentSIN) {

    
    // THRESHOLDS from design document
    
    $BP_LIMIT    = 130;
    $SUGAR_LIMIT = 7.0;
    $HR_LOW      = 60;
    $HR_HIGH     = 100;
    $TEMP_LIMIT  = 37.5;

    
    // Get last 3 health records within 7 days
    
    $stmt = $conn->prepare("
        SELECT bloodPressure, bloodSugar, heartRate, temperature
        FROM healthreport
        WHERE residentSIN = ?
          AND dateOfCreation >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY dateOfCreation DESC
        LIMIT 3
    ");
    $stmt->execute([$residentSIN]);
    $records = $stmt->fetchAll();

    // Need at least 3 records to detect a trend
    if (count($records) < 3) {
        return;
    }

    
    // Count how many records have 2+ abnormal vitals
    $decliningCount = 0;

    foreach ($records as $record) {
        $abnormalCount = 0;

        if ($record['bloodPressure'] > $BP_LIMIT)                              $abnormalCount++;
        if ($record['bloodSugar']    > $SUGAR_LIMIT)                           $abnormalCount++;
        if ($record['heartRate'] < $HR_LOW || $record['heartRate'] > $HR_HIGH) $abnormalCount++;
        if ($record['temperature']   > $TEMP_LIMIT)                            $abnormalCount++;

        if ($abnormalCount >= 2) {
            $decliningCount++;
        }
    }

    
    // All 3 records must be declining to trigger
    if ($decliningCount < 3) {
        return;
    }

    
    // Check if alert already sent in last 24 hours
    $check = $conn->prepare("
        SELECT trendID FROM ai_trend_log
        WHERE residentSIN = ?
          AND alert_sent = 1
          AND last_checked >= DATE_SUB(NOW(), INTERVAL 1 DAY)
    ");
    $check->execute([$residentSIN]);

    if ($check->fetch()) {
        return;
    }

    
    // Get resident name
    $rStmt = $conn->prepare("
    SELECT fname, lname FROM resident WHERE residentSIN = ?
    ");
    $rStmt->execute([$residentSIN]);
    $residentRow = $rStmt->fetch();
    $residentName = $residentRow['fname'] . ' ' . $residentRow['lname'];

    
    // Get assigned caregivers emails
    $cgStmt = $conn->prepare("
        SELECT u.email, c.fname, c.lname
        FROM assignment a
        JOIN caregiver c ON a.empID = c.empID
        JOIN users u ON c.user_id = u.user_id
        WHERE a.residentSIN = ?
    ");
    $cgStmt->execute([$residentSIN]);
    $caregivers = $cgStmt->fetchAll();

    
    // Get linked family members emails
    $fmStmt = $conn->prepare("
        SELECT u.email, f.fname, f.lname
        FROM link l
        JOIN familymember f ON l.fmID = f.fmID
        JOIN users u ON f.user_id = u.user_id
        WHERE l.residentSIN = ?
    ");
    $fmStmt->execute([$residentSIN]);
    $familyMembers = $fmStmt->fetchAll();

    
    // Build the alert email
    $subject = "Health Decline Alert: $residentName";
    $body    = "
        <h3 style='color:red;'>Health Decline Detected</h3>
        <p>Resident <strong>$residentName</strong> has shown 
        declining health across <strong>3 consecutive records</strong> 
        in the past 7 days.</p>
        <p>At least 2 or more vital signs were abnormal in each record.</p>
        <p><strong>Thresholds used:</strong></p>
        <ul>
            <li>Blood Pressure above 130 mmHg</li>
            <li>Blood Sugar above 7.0 mmol/L</li>
            <li>Heart Rate below 60 or above 100 bpm</li>
            <li>Temperature above 37.5 C</li>
        </ul>
        <p><strong>Detected at:</strong> " . date('Y-m-d H:i:s') . "</p>
        <p>Please review the resident health records immediately.</p>
    ";

    
    // Send emails to caregivers and family members
   $sentEmails = []; // track which emails we already sent to

foreach ($caregivers as $cg) {
    $fullName = $cg['fname'] . ' ' . $cg['lname'];

    if (!in_array($cg['email'], $sentEmails)) {
        sendAlertEmail($cg['email'], $fullName, $subject, $body);
        $sentEmails[] = $cg['email'];
    }
}

foreach ($familyMembers as $fm) {
    $fullName = $fm['fname'] . ' ' . $fm['lname'];

    if (!in_array($fm['email'], $sentEmails)) {
        sendAlertEmail($fm['email'], $fullName, $subject, $body);
        $sentEmails[] = $fm['email'];
    }
}

    
    // Log alert in ai_trend_log to prevent duplicate emails within 24 hours
    $conn->prepare("
        INSERT INTO ai_trend_log 
            (residentSIN, consecutive_abnormal_count, alert_sent)
        VALUES (?, 3, 1)
        ON DUPLICATE KEY UPDATE 
            alert_sent = 1,
            last_checked = NOW(),
            consecutive_abnormal_count = 3
    ")->execute([$residentSIN]);
}
?>