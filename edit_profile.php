<?php
// 1. Core initialization, authentication checks, and session security handling must happen BEFORE any HTML output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once('db_conn.php');

$student_matric_no = $_SESSION['student_matric_no'];
$group_options = ['GK01','GK02','GR01','GR02','GR03','GR04','GR05','GR06','GR07','GR08','GR09','GS01','GS02','GS03','GS04','GS05','GW01','GW02','GW03','GW04','GW05','GW06','GW07','GW08','GW09'];

$success_msg = "";
$error_msg = "";

// 2. Transactional Update Request Context Processing Controller Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $group_no = trim($_POST['group_no'] ?? '');
    $life_motto = trim($_POST['life_motto'] ?? '');
    
    if (empty($full_name) || empty($group_no)) {
        $error_msg = "All required structural profile variables must be provided.";
    } else {
        $life_motto_db = (!empty($life_motto)) ? $life_motto : NULL;
        
        // Check if an image is provided for update
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file_upload = $_FILES['profile_image'];
            $file_extension = pathinfo($file_upload['name'], PATHINFO_EXTENSION);
            $target_dir = "uploads/profiles/";
            
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $new_filename = "avatar_" . preg_replace('/[^A-Za-z0-9]/', '', $student_matric_no) . "_" . time() . "." . $file_extension;
            $target_filepath = $target_dir . $new_filename;
            
            if (move_uploaded_file($file_upload['tmp_name'], $target_filepath)) {
                $update_query = "UPDATE student SET full_name = ?, group_no = ?, life_motto = ?, profile_image_path = ? WHERE student_matric_no = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "sssss", $full_name, $group_no, $life_motto_db, $target_filepath, $student_matric_no);
            } else {
                $error_msg = "Critical error encountered writing file stream content to server paths.";
            }
        } else {
            // Processing path sequence updating textual variables without altering historical image records
            $update_query = "UPDATE student SET full_name = ?, group_no = ?, life_motto = ? WHERE student_matric_no = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssss", $full_name, $group_no, $life_motto_db, $student_matric_no);
        }
        
        if (empty($error_msg)) {
            if (mysqli_stmt_execute($stmt)) {
                // Instantly update current workspace context state values inside the session array 
                $_SESSION['full_name'] = $full_name;
                $_SESSION['group_no'] = $group_no;
                
                // Close transactional statement links explicitly before execution handoff
                mysqli_stmt_close($stmt);
                
                // Perform dynamic redirect routing carrying contextual data keys safely before any HTML output starts
                header("Location: dashboard.php?matric_no=" . urlencode($student_matric_no) . "&group_no=" . urlencode($group_no));
                exit();
            } else {
                $error_msg = "Database constraint conflict generated during save operation execution.";
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// 3. Persistent Pre-load Query Logic Operations Context Engine
$fetch_query = "SELECT full_name, group_no, life_motto, profile_image_path FROM student WHERE student_matric_no = ? LIMIT 1";
$fetch_stmt = mysqli_prepare($conn, $fetch_query);
mysqli_stmt_bind_param($fetch_stmt, "s", $student_matric_no);
mysqli_stmt_execute($fetch_stmt);
$result = mysqli_stmt_get_result($fetch_stmt);
$student_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($fetch_stmt);

// 4. Safely include visual layout template markup components now that redirects are handled
include('menu.php');
?>

<main class="flex-1 p-6 bg-slate-100 min-h-screen">
    
    <div class="mb-4">
        <a href="dashboard.php?matric_no=<?php echo urlencode($student_matric_no); ?>&group_no=<?php echo urlencode($_SESSION['group_no']); ?>" 
           class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-200/80 transition font-medium">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-200/60">
        
        <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-cyan-800 p-6 text-white flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold">Student Profile Synchronization</h2>
                <p class="text-cyan-200 text-xs mt-1">Modify your authorization metadata mappings and platform operational context parameters.</p>
            </div>
            <div class="text-cyan-300 text-3xl opacity-80">
                <i class="fas fa-user-gear"></i>
            </div>
        </div>

        <div class="p-6">
            <?php if (!empty($error_msg)): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-xs rounded-lg flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span><?php echo htmlspecialchars($error_msg); ?></span>
                </div>
            <?php endif; ?>

            <form id="profileEditForm" action="edit_profile.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Corporate/Full Identity Name</label>
                        <input type="text" name="full_name" required value="<?php echo htmlspecialchars($student_data['full_name'] ?? ''); ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Project Allocation Group Set Reference</label>
                        <select name="group_no" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                            <?php foreach($group_options as $group): ?>
                                <option value="<?php echo $group; ?>" <?php echo (($student_data['group_no'] ?? '') === $group) ? 'selected' : ''; ?>><?php echo $group; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Personal Design Life Motto <span class="text-slate-400 lowercase italic">(Optional)</span></label>
                        <textarea name="life_motto" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500 resize-none"><?php echo htmlspecialchars($student_data['life_motto'] ?? ''); ?></textarea>
                    </div>

                    <div class="flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Update Avatar Profile Media Object</label>
                            <input type="file" name="profile_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                        </div>
                        <?php if (!empty($student_data['profile_image_path'])): ?>
                            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400 bg-slate-50 p-2 rounded-lg border border-slate-100">
                                <i class="fas fa-image text-slate-500"></i>
                                <span class="truncate">Active Object Path: <?php echo htmlspecialchars($student_data['profile_image_path']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200 text-sm shadow-md shadow-blue-200">
                        Save Profile Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("profileEditForm");
    let formChanged = false;

    // Monitor fields for changes
    form.querySelectorAll("input, select, textarea").forEach(element => {
        element.addEventListener("input", () => { formChanged = true; });
        element.addEventListener("change", () => { formChanged = true; });
    });

    // Reset indicator safely upon a normal submit action
    form.addEventListener("submit", () => {
        formChanged = false;
    });

    // Intercept navigation events if dirty flag indicators evaluate positive
    window.addEventListener("beforeunload", function (e) {
        if (formChanged) {
            const confirmationMessage = "You have unsaved form fields modified. Confirm to discard changes and leave?";
            (e || window.event).returnValue = confirmationMessage; 
            return confirmationMessage; 
        }
    });
});
</script>

</div> </body>
</html>