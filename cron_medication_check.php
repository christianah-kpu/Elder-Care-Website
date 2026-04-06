<?php
// -------------
// cron_medication_check.php- This file simulates an automatic background task that checks for missed or delayed medications every 5 minutes.
// ----------------------------------

// Load the database connection
require_once 'includes/db_connection.php';

// Load the medication alert checking function
require_once 'includes/medication_alert.php';

// Run the medication check
// This function loops through all scheduled medications for today and sends alerts for any missed or delayed ones.
checkMissedMedications($conn);

// Show a confirmation message so you know it ran
echo "<p style='font-family:Arial; color:green;'>
        Medication check completed at: " . date('Y-m-d H:i:s') . "
      </p>";
?>