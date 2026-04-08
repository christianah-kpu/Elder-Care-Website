<?php
//-----------------------------------------------------------
// ai_health_alert.php  
// HOW IT WORKS:
//   1. Called every time a caregiver saves a health report.
//   2. Collects all health records from the past 7 days for that resident.
//   3. Requires at least 7 records before making any assessment
//      (caregiver enters one record per day = 7 days of data needed).
//   4. Calculates the AVERAGE of each vital sign across those records.
//   5. Compares each average against normal thresholds (both HIGH and LOW).
//   6. If ANY average is outside the normal range  → DECLINE ALERT email.
//   7. If ALL averages are within normal range     → ALL CLEAR email.
//
// DUPLICATE EMAIL SUPPRESSION:
//   - Decline alert:  maximum once per 24 hours per resident.
//   - All-clear email: maximum once per 7 days per resident.
//------------------------------------------------------------

require_once __DIR__ . '/email_service.php';

function checkHealthTrend($conn, $residentSIN) {

    //---------------------------------------------------
    // NORMAL THRESHOLDS
    //
    // BP, HR are true numeric thresholds (INT columns, exact match).
    // SUGAR and TEMP thresholds are adjusted for INT rounding in DB:
    //   SUGAR_HIGH = 7  → catches stored INT > 7 (original ≥ 7.5 mmol/L)
    //   SUGAR_LOW  = 4  → catches stored INT < 4 (original < 3.5 mmol/L)
    //   TEMP_HIGH  = 37 → catches stored INT > 37 (original ≥ 37.5°C → stored as 38)
    //   TEMP_LOW   = 36 → catches stored INT < 36 (original < 35.5°C)
    //---------------------------------------------------
    $BP_HIGH    = 135;
    $BP_LOW     = 90;
    $SUGAR_HIGH = 7;
    $SUGAR_LOW  = 4;
    $HR_HIGH    = 100;
    $HR_LOW     = 60;
    $TEMP_HIGH  = 38;
    $TEMP_LOW   = 35;

    
    // Fetch all health records from the past 7 days
    
    $stmt = $conn->prepare("
        SELECT bloodPressure, bloodSugar, heartRate, temperature
        FROM healthreport
        WHERE residentSIN = ?
          AND dateOfCreation >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY dateOfCreation DESC
    ");
    $stmt->execute([$residentSIN]);
    $records = $stmt->fetchAll();

    // Need at least 7 records before making any assessment
    if (count($records) < 7) {
        return;
    }

    $total = count($records);

    
    // Calculate 7-day averages
    
    $avgBP    = array_sum(array_column($records, 'bloodPressure')) / $total;
    $avgSugar = array_sum(array_column($records, 'bloodSugar'))    / $total;
    $avgHR    = array_sum(array_column($records, 'heartRate'))     / $total;
    $avgTemp  = array_sum(array_column($records, 'temperature'))   / $total;

    
    // Compare each average to thresholds
    
    $issues    = [];
    $declining = false;

    if ($avgBP > $BP_HIGH) {
        $issues[]  = "Blood Pressure avg <strong>" . round($avgBP, 1) . " mmHg</strong> is HIGH (normal: {$BP_LOW}–{$BP_HIGH} mmHg)";
        $declining = true;
    } elseif ($avgBP < $BP_LOW) {
        $issues[]  = "Blood Pressure avg <strong>" . round($avgBP, 1) . " mmHg</strong> is LOW (normal: {$BP_LOW}–{$BP_HIGH} mmHg)";
        $declining = true;
    }

    if ($avgSugar > $SUGAR_HIGH) {
        $issues[]  = "Blood Sugar avg <strong>" . round($avgSugar, 1) . " mmol/L</strong> is HIGH (normal: 3.9–7.0 mmol/L)";
        $declining = true;
    } elseif ($avgSugar < $SUGAR_LOW) {
        $issues[]  = "Blood Sugar avg <strong>" . round($avgSugar, 1) . " mmol/L</strong> is LOW (normal: 3.9–7.0 mmol/L)";
        $declining = true;
    }

    if ($avgHR > $HR_HIGH) {
        $issues[]  = "Heart Rate avg <strong>" . round($avgHR, 1) . " bpm</strong> is HIGH (normal: {$HR_LOW}–{$HR_HIGH} bpm)";
        $declining = true;
    } elseif ($avgHR < $HR_LOW) {
        $issues[]  = "Heart Rate avg <strong>" . round($avgHR, 1) . " bpm</strong> is LOW (normal: {$HR_LOW}–{$HR_HIGH} bpm)";
        $declining = true;
    }

    if ($avgTemp > $TEMP_HIGH) {
        $issues[]  = "Temperature avg <strong>" . round($avgTemp, 1) . "°C</strong> is HIGH / FEVER (normal: 36.0–37.5°C)";
        $declining = true;
    } elseif ($avgTemp < $TEMP_LOW) {
        $issues[]  = "Temperature avg <strong>" . round($avgTemp, 1) . "°C</strong> is LOW (normal: 36.0–37.5°C)";
        $declining = true;
    }

    
    // Get resident name
    
    $rStmt = $conn->prepare("SELECT fname, lname FROM resident WHERE residentSIN = ?");
    $rStmt->execute([$residentSIN]);
    $residentRow  = $rStmt->fetch();
    $residentName = $residentRow['fname'] . ' ' . $residentRow['lname'];

    
    //  Get assigned caregivers' emails
    
    $cgStmt = $conn->prepare("
        SELECT u.email, c.fname, c.lname
        FROM assignment a
        JOIN caregiver c ON a.empID = c.empID
        JOIN users u     ON c.user_id = u.user_id
        WHERE a.residentSIN = ?
    ");
    $cgStmt->execute([$residentSIN]);
    $caregivers = $cgStmt->fetchAll();

    
    // Get linked family members' emails (approved only)
    
    $fmStmt = $conn->prepare("
        SELECT u.email, f.fname, f.lname
        FROM link l
        JOIN familymember f ON l.fmID = f.fmID
        JOIN users u        ON f.user_id = u.user_id
        WHERE l.residentSIN = ?
          AND l.status = 'approved'
    ");
    $fmStmt->execute([$residentSIN]);
    $familyMembers = $fmStmt->fetchAll();

    
    // Build the shared vitals summary table (used in both emails)
    
    $bpStatus    = ($avgBP    > $BP_HIGH    || $avgBP    < $BP_LOW)    ? "<span style='color:red;font-weight:bold;'>Abnormal</span>"  : "<span style='color:green;'>Normal</span>";
    $sugarStatus = ($avgSugar > $SUGAR_HIGH || $avgSugar < $SUGAR_LOW) ? "<span style='color:red;font-weight:bold;'>Abnormal</span>"  : "<span style='color:green;'>Normal</span>";
    $hrStatus    = ($avgHR    > $HR_HIGH    || $avgHR    < $HR_LOW)    ? "<span style='color:red;font-weight:bold;'>Abnormal</span>"  : "<span style='color:green;'>Normal</span>";
    $tempStatus  = ($avgTemp  > $TEMP_HIGH  || $avgTemp  < $TEMP_LOW)  ? "<span style='color:red;font-weight:bold;'>Abnormal</span>"  : "<span style='color:green;'>Normal</span>";

    $avgTable = "
        <table border='1' cellpadding='8' cellspacing='0'
               style='border-collapse:collapse;width:100%;font-family:Arial,sans-serif;font-size:14px;'>
            <thead style='background-color:#f0f0f0;'>
                <tr>
                    <th style='text-align:left;'>Vital Sign</th>
                    <th>7-Day Average</th>
                    <th>Normal Range</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Blood Pressure</td>
                    <td style='text-align:center;'>" . round($avgBP, 1)    . " mmHg</td>
                    <td style='text-align:center;'>90–135 mmHg</td>
                    <td style='text-align:center;'>{$bpStatus}</td>
                </tr>
                <tr>
                    <td>Blood Sugar</td>
                    <td style='text-align:center;'>" . round($avgSugar, 1) . " mmol/L</td>
                    <td style='text-align:center;'>3.9–7.0 mmol/L</td>
                    <td style='text-align:center;'>{$sugarStatus}</td>
                </tr>
                <tr>
                    <td>Heart Rate</td>
                    <td style='text-align:center;'>" . round($avgHR, 1)    . " bpm</td>
                    <td style='text-align:center;'>60–100 bpm</td>
                    <td style='text-align:center;'>{$hrStatus}</td>
                </tr>
                <tr>
                    <td>Temperature</td>
                    <td style='text-align:center;'>" . round($avgTemp, 1)  . " °C</td>
                    <td style='text-align:center;'>36.0–37.5°C</td>
                    <td style='text-align:center;'>{$tempStatus}</td>
                </tr>
            </tbody>
        </table>
        <p style='color:#888;font-size:12px;'>Based on {$total} records collected over the past 7 days.</p>
    ";

    $sentEmails = [];

    
    // DECLINE ALERT (one or more vitals outside range)
    
    if ($declining) {

        // Suppress duplicate decline alert within 24 hours
        $check = $conn->prepare("
            SELECT trendID FROM ai_trend_log
            WHERE residentSIN = ?
              AND alert_sent  = 1
              AND last_checked >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        ");
        $check->execute([$residentSIN]);
        if ($check->fetch()) {
            return; // Alert already sent in the last 24 hours
        }

        $issueList = '<ul><li>' . implode('</li><li>', $issues) . '</li></ul>';

        $subject = "ALERT: Health Decline Detected for $residentName";
        $body    = "
            <h2 style='color:red;'>Health Decline Detected</h2>
            <p>Resident <strong>$residentName</strong>'s 7-day average vital signs
               are outside the normal range.</p>
            <h3>Problems Detected:</h3>
            {$issueList}
            <h3>Full 7-Day Average Summary:</h3>
            {$avgTable}
            <p>Detected at: <strong>" . date('Y-m-d H:i:s') . "</strong></p>
            <p><strong>Please review the resident's health records immediately.</strong></p>
        ";

        foreach ($caregivers as $cg) {
            if (!in_array($cg['email'], $sentEmails)) {
                sendAlertEmail($cg['email'], $cg['fname'] . ' ' . $cg['lname'], $subject, $body);
                $sentEmails[] = $cg['email'];
            }
        }
        foreach ($familyMembers as $fm) {
            if (!in_array($fm['email'], $sentEmails)) {
                sendAlertEmail($fm['email'], $fm['fname'] . ' ' . $fm['lname'], $subject, $body);
                $sentEmails[] = $fm['email'];
            }
        }

        // Log the decline alert
        // ON DUPLICATE KEY uses UNIQUE KEY `unique_resident` (residentSIN) on ai_trend_log
        $conn->prepare("
            INSERT INTO ai_trend_log (residentSIN, consecutive_abnormal_count, alert_sent)
            VALUES (?, 1, 1)
            ON DUPLICATE KEY UPDATE
                alert_sent                 = 1,
                last_checked               = NOW(),
                consecutive_abnormal_count = 1
        ")->execute([$residentSIN]);

    
    // ALL CLEAR (all vitals within normal range)
    
    } else {

        // Suppress duplicate all-clear email within 7 days
        $check = $conn->prepare("
            SELECT trendID FROM ai_trend_log
            WHERE residentSIN = ?
              AND alert_sent  = 0
              AND last_checked >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $check->execute([$residentSIN]);
        if ($check->fetch()) {
            return; // All-clear already sent in the last 7 days
        }

        $subject = "Health Status Normal for $residentName";
        $body    = "
            <h2 style='color:green;'>Health Status: All Clear</h2>
            <p>Resident <strong>$residentName</strong>'s 7-day average vital signs
               are all within the normal range.</p>
            <h3>7-Day Average Summary:</h3>
            {$avgTable}
            <p>Checked at: <strong>" . date('Y-m-d H:i:s') . "</strong></p>
            <p>No action needed. Everything looks good!</p>
        ";

        foreach ($caregivers as $cg) {
            if (!in_array($cg['email'], $sentEmails)) {
                sendAlertEmail($cg['email'], $cg['fname'] . ' ' . $cg['lname'], $subject, $body);
                $sentEmails[] = $cg['email'];
            }
        }
        foreach ($familyMembers as $fm) {
            if (!in_array($fm['email'], $sentEmails)) {
                sendAlertEmail($fm['email'], $fm['fname'] . ' ' . $fm['lname'], $subject, $body);
                $sentEmails[] = $fm['email'];
            }
        }

        // Log the all-clear check
        $conn->prepare("
            INSERT INTO ai_trend_log (residentSIN, consecutive_abnormal_count, alert_sent)
            VALUES (?, 0, 0)
            ON DUPLICATE KEY UPDATE
                alert_sent                 = 0,
                last_checked               = NOW(),
                consecutive_abnormal_count = 0
        ")->execute([$residentSIN]);
    }
}
?>
