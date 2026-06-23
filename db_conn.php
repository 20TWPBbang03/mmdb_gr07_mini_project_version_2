<?php
    // Disable global exception throwing to allow custom fallback logic
    mysqli_report(MYSQLI_REPORT_OFF);

    // Common Server configurations
    $host = "bitp3353.utem.edu.my";
    $port = "3306";
    $user = "GR07";
    $password = "password";
    
    $dbname1 = "gr07";
    $dbname2 = "mmdb2026";

    // Initialize connections with a 3-second network timeout limit
    $conn = mysqli_init();
    $conn_mmdb = mysqli_init();
    
    mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 3);
    mysqli_options($conn_mmdb, MYSQLI_OPT_CONNECT_TIMEOUT, 3);

    // Attempt connecting to the remote server databases
    $success1 = @mysqli_real_connect($conn, $host, $user, $password, $dbname1, $port);
    $success2 = @mysqli_real_connect($conn_mmdb, $host, $user, $password, $dbname2, $port);

    // If either connection to the remote server fails, activate local XAMPP fallbacks
    if (!$success1 || !$success2) {
        $local_host = "localhost";
        $local_port = "3306";
        $local_user = "root";
        $local_password = "";
        
        $local_dbname1 = "mmdb_gr07"; // Local clone of gr07

        // Establish fallback local connections
        $conn = mysqli_connect($local_host, $local_user, $local_password, $local_dbname1, $local_port);
        $conn_mmdb = false; // Set to false since mmdb2026 local clone does not exist

        if (!$conn) {
            die("Database connection completely failed: " . mysqli_connect_error());
        }
    }

    // Re-enable default reporting settings for query script handling down the line
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>