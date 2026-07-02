<?php
// Initialize session handling at the top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connections from db_conn.php
require_once 'db_conn.php'; 

// Check if connections initialized properly
$db_error = false;
if (!$conn) {
    $db_error = true;
}

$alert_msg = "";
$alert_type = "";

// 1. PROCESS ACTIONS & SUBMISSIONS
if (!$db_error && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // ACTION: Submission Retrieval
    if (isset($_POST['action_retrieve'])) {
        $assignment_id = $_POST['assignment_id'] ?? '';
        $uploaded_file = $_FILES['submission_file'] ?? null;

        if (empty($assignment_id) || !$uploaded_file || $uploaded_file['error'] !== UPLOAD_ERR_OK) {
            $alert_msg = "Both assignment selection and file upload are required.";
            $alert_type = "error";
        } else {
            $stmt = $conn->prepare("SELECT assignment_id, title, due_date, max_file_size_mb FROM assignment WHERE assignment_id = ? AND assignment_status = 'Available' LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("i", $assignment_id);
                $stmt->execute();
                $asg = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }

            if (!empty($asg)) {
                $now = new DateTime();
                $due = new DateTime($asg['due_date']);
                
                // Compare submission status
                if ($now > $due) {
                    $status = 'Late';
                    $diff = $due->diff($now);
                    $analysis_text = "Submitted " . $diff->days . " Days and " . $diff->h . " Hours late";
                } else {
                    $status = 'Early';
                    $diff = $now->diff($due);
                    $analysis_text = "Submitted " . $diff->days . " Days and " . $diff->h . " Hours early";
                }

                // Compute file validations in MB unit
                $file_size_bytes = $uploaded_file['size'];
                $file_size_mb = $file_size_bytes / (1024 * 1024);
                $validation = ($file_size_mb > $asg['max_file_size_mb']) ? 'Oversized' : 'Valid';

                // Save the file persistently
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_ext = pathinfo($uploaded_file['name'], PATHINFO_EXTENSION);
                $saved_path = $upload_dir . $_SESSION['student_matric_no'] . '_' . $assignment_id . '_' . time() . '.' . $file_ext;

                if (move_uploaded_file($uploaded_file['tmp_name'], $saved_path)) {
                    $_SESSION['retrieved_submission'] = [
                        'assignment_id'    => $asg['assignment_id'],
                        'title'            => $asg['title'],
                        'file_path'        => $saved_path,
                        'file_size_mb'     => $file_size_mb,
                        'max_file_size_mb' => $asg['max_file_size_mb'],
                        'sub_date_db'      => $now->format('Y-m-d H:i:s'),
                        'sub_date_fmt'     => $now->format('d F Y h:i A'),
                        'due_date_fmt'     => $due->format('d F Y h:i A'),
                        'status'           => $status,
                        'validation'       => $validation,
                        'analysis_text'    => $analysis_text
                    ];
                    $alert_msg = "Submission successfully retrieved and analyzed.";
                    $alert_type = "success";
                } else {
                    $alert_msg = "Failed to store the uploaded file.";
                    $alert_type = "error";
                }
            } else {
                $alert_msg = "Selected assignment is no longer available.";
                $alert_type = "error";
            }
        }
    }

    // ACTION: Save Submission
    if (isset($_POST['action_save'])) {
        if (!isset($_SESSION['retrieved_submission'])) {
            $alert_msg = "No retrieved submission data available to save.";
            $alert_type = "error";
        } else {
            $sub = $_SESSION['retrieved_submission'];

            $stmt = $conn->prepare("INSERT INTO submission (student_matric_no, assignment_id, submission_date, file_path, file_size_mb, file_validation, submission_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sisssss", $_SESSION['student_matric_no'], $sub['assignment_id'], $sub['sub_date_db'], $sub['file_path'], $sub['file_size_mb'], $sub['validation'], $sub['status']);
                if ($stmt->execute()) {
                    $alert_msg = "Submission successfully saved into database!";
                    $alert_type = "success";
                    unset($_SESSION['retrieved_submission']); // Flush temporary tracking session state
                } else {
                    $alert_msg = "Database insertion error: " . $stmt->error;
                    $alert_type = "error";
                }
                $stmt->close();
            }
        }
    }

    // ACTION: Reset Submission Details
    if (isset($_POST['action_reset'])) {
        unset($_SESSION['retrieved_submission']);
        header("Location: abr.php");
        exit;
    }
}

include 'menu.php';
?>

<main class="flex-1 p-6 bg-slate-100 min-h-screen">

    <div class="mb-8">
        <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-cyan-800 rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">
                        Attribute-Based Retrieval (ABR)
                    </h2>
                    <p class="text-cyan-200 text-sm mt-1">
                        Late Submission Detection
                    </p>
                </div>
                <div class="text-cyan-300 text-4xl">
                    <i class="fas fa-database w-6"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($alert_msg)): ?>
        <div class="mb-6 p-4 rounded-xl text-sm font-medium <?php echo ($alert_type === 'success') ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
            <?php echo htmlspecialchars($alert_msg); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-2 gap-5">

        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-blue-100">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Submission Retrieval
            </h3>
            <form action="abr.php" method="POST" enctype="multipart/form-data">
                <label class="block text-xs text-slate-500 mb-2">Assignment Title</label>
                <select name="assignment_id" required class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm text-slate-700 mb-4">
                    <option value="">Select Assignment</option>
                    <?php
                    if (!$db_error) {
                        $asg_rows = $conn->query("SELECT assignment_id, title FROM assignment WHERE assignment_status = 'Available'");
                        if ($asg_rows) {
                            while ($row = $asg_rows->fetch_assoc()) {
                                $selected = (isset($_SESSION['retrieved_submission']['assignment_id']) && $_SESSION['retrieved_submission']['assignment_id'] == $row['assignment_id']) ? 'selected' : '';
                                echo "<option value='".htmlspecialchars($row['assignment_id'])."' {$selected}>".htmlspecialchars($row['title'])."</option>";
                            }
                        }
                    }
                    ?>
                </select>

                <label class="block text-xs text-slate-500 mb-2">Upload Required File</label>
                <input type="file" name="submission_file" required class="w-full bg-white border border-slate-200 rounded-xl p-2 text-sm text-slate-700 mb-4">

                <button type="submit" name="action_retrieve" class="mt-2 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white text-sm font-medium px-5 py-3 rounded-xl transition duration-200 shadow-md">
                    Retrieve Submission
                </button>
            </form>
        </div>

        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-cyan-100">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Submission Analysis
            </h3>
            <div class="text-center mt-4">
                <?php
                $status_check = $_SESSION['retrieved_submission']['status'] ?? 'PENDING';
                $theme_gradient = "from-slate-400 to-slate-500";
                $text_color = "text-slate-600";
                $icon_class = "fa-folder-open";

                if ($status_check === 'Late') {
                    $theme_gradient = "from-red-400 to-orange-500";
                    $text_color = "text-red-600";
                    $icon_class = "fa-triangle-exclamation";
                } elseif ($status_check === 'Early') {
                    $theme_gradient = "from-emerald-400 to-teal-500";
                    $text_color = "text-emerald-600";
                    $icon_class = "fa-circle-check";
                }
                ?>
                <div class="w-24 h-24 rounded-full bg-gradient-to-r <?php echo $theme_gradient; ?> flex items-center justify-center mx-auto shadow-lg transition duration-300">
                    <i class="fas <?php echo $icon_class; ?> text-5xl text-white"></i>
                </div>

                <h1 class="text-3xl font-bold <?php echo $text_color; ?> mt-5 uppercase tracking-wide">
                    <?php echo htmlspecialchars($status_check); ?>
                </h1>

                <p class="text-sm text-slate-500 mt-2 font-medium">
                    <?php echo htmlspecialchars($_SESSION['retrieved_submission']['analysis_text'] ?? 'Awaiting Parameter Processing Retrieval'); ?>
                </p>

                <div class="w-48 mx-auto mt-5">
                    <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-2 bg-gradient-to-r <?php echo $theme_gradient; ?> rounded-full w-[100%] transition-all duration-500"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-5 mt-5">

        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-blue-100">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Submission Information
            </h3>
            <div class="space-y-4">
                <div class="bg-slate-50 p-4 rounded-xl">
                    <p class="text-xs text-slate-500">Student Name</p>
                    <h4 class="font-semibold"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'N/A'); ?></h4>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl">
                    <p class="text-xs text-slate-500">Matric No</p>
                    <h4 class="font-semibold"><?php echo htmlspecialchars($_SESSION['student_matric_no'] ?? 'N/A'); ?></h4>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl">
                    <p class="text-xs text-slate-500">Group No</p>
                    <h4 class="font-semibold"><?php echo htmlspecialchars($_SESSION['group_no'] ?? 'N/A'); ?></h4>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl">
                    <p class="text-xs text-slate-500">Assignment Title</p>
                    <h4 class="font-semibold"><?php echo htmlspecialchars($_SESSION['retrieved_submission']['title'] ?? 'N/A'); ?></h4>
                </div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-cyan-100">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Metadata Analysis
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Assignment Max File Size</p>
                    <h4 class="text-sm font-semibold text-slate-800 mt-1">
                        <?php echo isset($_SESSION['retrieved_submission']['max_file_size_mb']) ? number_format($_SESSION['retrieved_submission']['max_file_size_mb'], 4) . ' MB' : 'N/A'; ?>
                    </h4>
                </div>
                <div class="bg-slate-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">File Size Uploaded</p>
                    <h4 class="text-sm font-semibold text-slate-800 mt-1">
                        <?php echo isset($_SESSION['retrieved_submission']['file_size_mb']) ? number_format($_SESSION['retrieved_submission']['file_size_mb'], 4) . ' MB' : 'N/A'; ?>
                    </h4>
                </div>
                <div class="bg-blue-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Submission Date</p>
                    <h4 class="text-sm font-semibold text-slate-800 mt-1">
                        <?php echo htmlspecialchars($_SESSION['retrieved_submission']['sub_date_fmt'] ?? 'N/A'); ?>
                    </h4>
                </div>
                <div class="bg-cyan-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Due Date</p>
                    <h4 class="text-sm font-semibold text-slate-800 mt-1">
                        <?php echo htmlspecialchars($_SESSION['retrieved_submission']['due_date_fmt'] ?? 'N/A'); ?>
                    </h4>
                </div>
                <div class="bg-indigo-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Status</p>
                    <h4 class="text-sm font-semibold mt-1 <?php echo ($status_check === 'Late') ? 'text-red-600' : ($status_check === 'Early' ? 'text-emerald-600' : 'text-slate-800'); ?>">
                        <?php echo htmlspecialchars($_SESSION['retrieved_submission']['status'] ?? 'N/A'); ?>
                    </h4>
                </div>
                <div class="bg-sky-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">File Validation</p>
                    <?php
                    $val_check = $_SESSION['retrieved_submission']['validation'] ?? 'N/A';
                    $val_color = ($val_check === 'Oversized') ? 'text-orange-500' : (($val_check === 'Valid') ? 'text-emerald-600' : 'text-slate-800');
                    ?>
                    <h4 class="text-sm font-semibold mt-1 <?php echo $val_color; ?>">
                        <?php echo htmlspecialchars($val_check); ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 mb-8 flex items-center justify-end gap-4 bg-white/50 border border-slate-200/60 p-4 rounded-2xl shadow-sm">
        <form action="abr.php" method="POST" class="flex gap-4">
            <button type="submit" name="action_reset" class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-medium px-5 py-3 rounded-xl transition duration-200">
                Reset Submission
            </button>
            <button type="submit" name="action_save" <?php echo !isset($_SESSION['retrieved_submission']) ? 'disabled' : ''; ?> class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white text-sm font-medium px-6 py-3 rounded-xl transition duration-200 shadow-md disabled:opacity-50">
                Save Submission
            </button>
        </form>
    </div>

    <div class="mt-8 bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-blue-50">
        <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
            File Submission Detection History Log
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm text-slate-700">
                <thead>
                    <tr class="border-b border-slate-200 text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/70">
                        <th class="p-4">Matric No</th>
                        <th class="p-4">Full Name</th>
                        <th class="p-4">Group No</th>
                        <th class="p-4">File Path</th>
                        <th class="p-4">Due Date</th>
                        <th class="p-4">Submission Date</th>
                        <th class="p-4">File Validation</th>
                        <th class="p-4">Submission Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    if (!$db_error) {
                        $history_query = "
                            SELECT 
                                s.student_matric_no, 
                                s.full_name, 
                                s.group_no, 
                                su.file_path, 
                                a.due_date, 
                                su.submission_date, 
                                su.file_validation, 
                                su.submission_status 
                            FROM submission su
                            JOIN student s ON su.student_matric_no = s.student_matric_no
                            JOIN assignment a ON su.assignment_id = a.assignment_id
                            ORDER BY su.submission_date DESC";
                        
                        $history_rows = $conn->query($history_query);
                        if ($history_rows && $history_rows->num_rows > 0) {
                            while ($row = $history_rows->fetch_assoc()) {
                                echo "<tr class='hover:bg-slate-50/50 transition duration-150'>";
                                echo "<td class='p-4 font-medium text-slate-900'>".htmlspecialchars($row['student_matric_no'])."</td>";
                                echo "<td class='p-4'>".htmlspecialchars($row['full_name'])."</td>";
                                echo "<td class='p-4'>".htmlspecialchars($row['group_no'])."</td>";
                                echo "<td class='p-4 text-xs font-mono text-slate-500'>";
                                if (!empty($row['file_path'])) {
                                    echo htmlspecialchars($row['file_path']) . " ";
                                    echo "<a href='".htmlspecialchars($row['file_path'])."' download class='inline-block ml-2 text-blue-500 hover:underline'><i class='fas fa-download'></i> Download</a>";
                                } else {
                                    echo "N/A";
                                }
                                echo "</td>";
                                echo "<td class='p-4 text-slate-600'>".htmlspecialchars(date('d M Y h:i A', strtotime($row['due_date'])))."</td>";
                                echo "<td class='p-4 text-slate-600'>".htmlspecialchars(date('d M Y h:i A', strtotime($row['submission_date'])))."</td>";
                                
                                $v_str = htmlspecialchars($row['file_validation']);
                                $v_badge = ($v_str === 'Oversized') ? 'bg-orange-50 text-orange-600' : 'bg-emerald-50 text-emerald-600';
                                echo "<td class='p-4'><span class='px-2.5 py-1 text-xs font-medium rounded-full {$v_badge}'>{$v_str}</span></td>";
                                
                                $s_str = htmlspecialchars($row['submission_status']);
                                $s_badge = ($s_str === 'Late') ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600';
                                echo "<td class='p-4'><span class='px-2.5 py-1 text-xs font-medium rounded-full {$s_badge}'>{$s_str}</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='p-8 text-center text-slate-400'>No submission tracking logs currently present.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='p-8 text-center text-slate-400'>Database context unavailable.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

</body>
</html>