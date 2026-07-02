<?php
session_start();
require_once('db_conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_no = trim($_POST['group_no'] ?? '');
    $student_matric_no = trim($_POST['student_matric_no'] ?? '');
    $phone_no = trim($_POST['phone_no'] ?? '');

    if (empty($group_no) || empty($student_matric_no) || empty($phone_no)) {
        header("Location: login.php?error=" . urlencode("All credential parameters are strictly required."));
        exit();
    }

    // Direct match check on composite fields
    $query = "SELECT student_matric_no, full_name, phone_no, group_no FROM student WHERE student_matric_no = ? AND group_no = ? AND phone_no = ? LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $student_matric_no, $group_no, $phone_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Create dynamic functional session token keys 
        $_SESSION['student_logged_in'] = true;
        $_SESSION['auth_token'] = bin2hex(random_bytes(32)); 
        $_SESSION['student_matric_no'] = $row['student_matric_no'];
        $_SESSION['full_name'] = $row['full_name'];
        $_SESSION['group_no'] = $row['group_no'];

        // Modified: Append tracking contextual keys safely to the dashboard landing redirection target
        header("Location: dashboard.php?matric_no=" . urlencode($row['student_matric_no']) . "&group_no=" . urlencode($row['group_no']));
        exit();
    } else {
        header("Location: login.php?error=" . urlencode("Invalid assignment context combination. Please try again."));
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>