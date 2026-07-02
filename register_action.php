<?php
session_start();
require_once('db_conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_matric_no = trim($_POST['student_matric_no'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone_no = trim($_POST['phone_no'] ?? '');
    $group_no = trim($_POST['group_no'] ?? '');
    $life_motto = trim($_POST['life_motto'] ?? '');

    // Strict validation verification check parameters
    if (empty($student_matric_no) || empty($full_name) || empty($phone_no) || empty($group_no) || !isset($_FILES['profile_image'])) {
        header("Location: register.php?error=" . urlencode("All mandatory fields and profile image uploads are required."));
        exit();
    }

    // Capture and clean optional text area input context arrays
    $life_motto_db = (!empty($life_motto)) ? $life_motto : NULL;

    // Direct Check for duplicate candidate key records to protect data integrity
    $check_query = "SELECT student_matric_no FROM student WHERE student_matric_no = ? LIMIT 1";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $student_matric_no);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        header("Location: register.php?error=" . urlencode("Matric No. is already registered under an existing platform identity."));
        exit();
    }
    mysqli_stmt_close($check_stmt);

    // Profile Binary Media Storage Execution Workflow
    $file_upload = $_FILES['profile_image'];
    $file_extension = pathinfo($file_upload['name'], PATHINFO_EXTENSION);
    
    // Enforce folder directory configuration availability matches local storage context paths
    $target_dir = "uploads/profiles/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Name files systematically using identification attributes
    $new_filename = "avatar_" . preg_replace('/[^A-Za-z0-9]/', '', $student_matric_no) . "_" . time() . "." . $file_extension;
    $target_filepath = $target_dir . $new_filename;

    if (move_uploaded_file($file_upload['tmp_name'], $target_filepath)) {
        // Prepare data insertion query parameters mapping to exact student structural configurations
        $insert_query = "INSERT INTO student (student_matric_no, full_name, phone_no, group_no, life_motto, profile_image_path) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssssss", $student_matric_no, $full_name, $phone_no, $group_no, $life_motto_db, $target_filepath);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: login.php?success=" . urlencode("Account successfully created. Please authenticate."));
            exit();
        } else {
            header("Location: register.php?error=" . urlencode("Database system error failed to register profile array properties."));
            exit();
        }
    } else {
        header("Location: register.php?error=" . urlencode("Critical failure saving upload destination content objects to server workspace paths."));
        exit();
    }
} else {
    header("Location: register.php");
    exit();
}
?>