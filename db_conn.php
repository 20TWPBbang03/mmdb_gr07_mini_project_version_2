<?php
// Enable standard exception and error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$local_host = "localhost";
$local_port = "3306";
$local_user = "root";
$local_password = "";
$local_dbname = "mmdb_gr07"; 

// Establish the single consolidated connection resource
$conn = mysqli_connect($local_host, $local_user, $local_password, $local_dbname, $local_port);

// Maintain the secondary connection variable alias to prevent breaking existing module scripts
$conn_mmdb = $conn;
if (!$conn) {
    die("Database connection completely failed: " . mysqli_connect_error());
}
?>