<?php
// Initialize workspace context dependencies safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_conn.php';

$alert_msg = "";
$alert_type = "";

// Workspace State Variables for updates
$edit_mode = false;
$edit_id = "";
$form_title = "";
$form_due_date = "";
$form_max_size = "";
$form_status = "Available";

// 1. BACKEND ROUTING & PERSISTENCE OPERATIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION: Create / Insert New Assignment Context
    if (isset($_POST['action_save'])) {
        $title = trim($_POST['title'] ?? '');
        $due_date = trim($_POST['due_date'] ?? '');
        $max_size = trim($_POST['max_file_size_mb'] ?? '');
        $status = trim($_POST['assignment_status'] ?? 'Available');
        
        if (empty($title) || empty($due_date) || empty($max_size) || empty($status)) {
            $alert_msg = "All operational assignment metric constraints must be supplied completely.";
            $alert_type = "error";
        } else {
            // Treat directly as a float value for decimal(6,4) column
            $max_size_mb = floatval($max_size);
            
            // Adjust typo field mapping based on your active .sql dump schema: enum('Available','Cloased')
            $schema_status = ($status === 'Closed') ? 'Cloased' : 'Available';

            $insert_stmt = $conn->prepare("INSERT INTO assignment (title, due_date, max_file_size_mb, assignment_status) VALUES (?, ?, ?, ?)");
            // Changed parameter binding to 'd' for decimal float value
            $insert_stmt->bind_param("ssds", $title, $due_date, $max_size_mb, $schema_status);
            
            if ($insert_stmt->execute()) {
                $alert_msg = "New assignment schema configuration instantiated successfully.";
                $alert_type = "success";
            } else {
                $alert_msg = "Persistence error encountered updating transaction tables: " . $conn->error;
                $alert_type = "error";
            }
            $insert_stmt->close();
        }
    }
    
    // ACTION: Apply Updated Parameters (Save Changes)
    if (isset($_POST['action_update'])) {
        $assignment_id = intval($_POST['assignment_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $due_date = trim($_POST['due_date'] ?? '');
        $max_size = trim($_POST['max_file_size_mb'] ?? '');
        $status = trim($_POST['assignment_status'] ?? 'Available');
        
        if ($assignment_id > 0 && !empty($title) || !empty($due_date) || !empty($max_size)) {
            $max_size_mb = floatval($max_size);
            $schema_status = ($status === 'Closed') ? 'Cloased' : 'Available';

            $update_stmt = $conn->prepare("UPDATE assignment SET title = ?, due_date = ?, max_file_size_mb = ?, assignment_status = ? WHERE assignment_id = ?");
            // Fixed spaced bindings string and updated parameter type to 'd' for decimal
            $update_stmt->bind_param("ssdsi", $title, $due_date, $max_size_mb, $schema_status, $assignment_id);
            
            if ($update_stmt->execute()) {
                $alert_msg = "Assignment structural options updated cleanly.";
                $alert_type = "success";
            } else {
                $alert_msg = "Failed to update record details.";
                $alert_type = "error";
            }
            $update_stmt->close();
        }
    }
}

// 2. GET DISPATCHED REQUEST LISTENER 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // TRIGGER: Load into edit tracking view context state container rules
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        $fetch_stmt = $conn->prepare("SELECT title, due_date, max_file_size_mb, assignment_status FROM assignment WHERE assignment_id = ? LIMIT 1");
        $fetch_stmt->bind_param("i", $edit_id);
        $fetch_stmt->execute();
        $res = $fetch_stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $edit_mode = true;
            $form_title = $row['title'];
            // Normalize temporal schema string mapping format to display in HTML5 input types cleanly
            $form_due_date = date('Y-m-d\TH:i', strtotime($row['due_date']));
            // Removed division math since column is natively structured in MBs already
            $form_max_size = number_format($row['max_file_size_mb'], 4, '.', '');
            $form_status = ($row['assignment_status'] === 'Cloased') ? 'Closed' : 'Available';
        }
        $fetch_stmt->close();
    }
    
    // TRIGGER: Permanent context deletion processing tree sequence with integrity safety parameters
    if (isset($_GET['delete_id'])) {
        $delete_id = intval($_GET['delete_id']);
        $delete_stmt = $conn->prepare("DELETE FROM assignment WHERE assignment_id = ?");
        $delete_stmt->bind_param("i", $delete_id);
        if ($delete_stmt->execute()) {
            $alert_msg = "Assignment instance cleanly purged from relational operational nodes.";
            $alert_type = "success";
        } else {
            $alert_msg = "Error deleting assignment: " . $conn->error;
            $alert_type = "error";
        }
        $delete_stmt->close();
    }
}

// Render Uniform Master Shell Layout
include 'menu.php';
?>

<main class="flex-1 p-6 bg-slate-100 min-h-screen">
    
    <div class="mb-8">
        <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-cyan-800 rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">Assignment Management</h2>
                    <p class="text-cyan-200 text-sm mt-1">Configure and manage active assignment parameters and dimensional metadata boundaries.</p>
                </div>
                <div class="text-cyan-300 text-4xl">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($alert_msg)): ?>
        <div class="mb-6 p-4 rounded-xl text-sm font-medium border <?php echo ($alert_type === 'success') ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700'; ?> flex items-center gap-3">
            <i class="fas <?php echo ($alert_type === 'success') ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
            <span><?php echo htmlspecialchars($alert_msg); ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl p-6 shadow-xl mb-8 border border-slate-200/60">
        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">
            <i class="fas <?php echo $edit_mode ? 'fa-pen-to-square text-amber-500' : 'fa-plus-circle text-blue-500'; ?> mr-2"></i>
            <?php echo $edit_mode ? 'Modify Assignment Settings' : 'New Assignment'; ?>
        </h3>

        <form action="assignment_management.php<?php echo $url_suffix; ?>" method="POST" class="space-y-5">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="assignment_id" value="<?php echo $edit_id; ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Assignment Title String</label>
                    <input type="text" name="title" required value="<?php echo htmlspecialchars($form_title); ?>" placeholder="e.g., Lab Assignment 1 - Features extraction" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Target Submission Limit (Due Date & Time)</label>
                    <input type="datetime-local" name="due_date" required value="<?php echo htmlspecialchars($form_due_date); ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Max Document Load Allocation Size (MB)</label>
                    <input type="number" name="max_file_size_mb" step="0.0001" required value="<?php echo htmlspecialchars($form_max_size); ?>" placeholder="e.g., 25.0000" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Active Visibility Status Filter</label>
                    <select name="assignment_status" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                        <option value="Available" <?php echo ($form_status === 'Available') ? 'selected' : ''; ?>>Available</option>
                        <option value="Closed" <?php echo ($form_status === 'Closed') ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-2 gap-3">
                <?php if ($edit_mode): ?>
                    <a href="assignment_management.php<?php echo $url_suffix; ?>" class="px-5 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 transition font-medium text-sm">Cancel</a>
                    <button type="submit" name="action_update" class="bg-amber-500 hover:bg-amber-600 text-white font-medium py-2 px-6 rounded-lg transition text-sm shadow-md shadow-amber-100">Save Modifications</button>
                <?php else: ?>
                    <button type="submit" name="action_save" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition text-sm shadow-md shadow-blue-200">Save Assignment</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="mt-8">
        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2">Existing Assignments</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $query_list = "SELECT assignment_id, title, due_date, max_file_size_mb, assignment_status FROM assignment ORDER BY assignment_id DESC";
            $res_list = $conn->query($query_list);
            
            if ($res_list && $res_list->num_rows > 0) {
                while ($row = $res_list->fetch_assoc()) {
                    $mb_size = number_format($row['max_file_size_mb'], 4, '.', '');
                    // Correct alignment matching stored database ENUM value spellings
                    $display_status = ($row['assignment_status'] === 'Cloased') ? 'Closed' : 'Available';
                    $badge_class = ($display_status === 'Available') ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100';
                    ?>
                    
                    <div class="bg-white rounded-2xl shadow-md border border-slate-200/60 overflow-hidden flex flex-col justify-between hover:shadow-lg transition">
                        <div class="p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded">ID: <?php echo $row['assignment_id']; ?></span>
                                <span class="text-[11px] font-bold tracking-wider uppercase border px-2 py-0.5 rounded-full <?php echo $badge_class; ?>"><?php echo $display_status; ?></span>
                            </div>
                            
                            <h4 class="font-bold text-slate-800 text-base line-clamp-2 min-h-[3rem]"><?php echo htmlspecialchars($row['title']); ?></h4>
                            
                            <div class="text-xs text-slate-600 space-y-1.5 pt-2 border-t border-slate-50 font-medium">
                                <div class="flex"><span class="text-slate-400 w-28 shrink-0">Due Date</span><span class="mr-1">:</span> <span class="text-slate-800"><?php echo date('d M Y, h:i A', strtotime($row['due_date'])); ?></span></div>
                                <div class="flex"><span class="text-slate-400 w-28 shrink-0">Max File Size (MB)</span><span class="mr-1">:</span> <span class="text-slate-800 font-mono"><?php echo $mb_size; ?> MB</span></div>
                                <div class="flex"><span class="text-slate-400 w-28 shrink-0">Assignment_status</span><span class="mr-1">:</span> <span class="text-slate-800"><?php echo $display_status; ?></span></div>
                            </div>
                        </div>
                        
                        <div class="bg-slate-50/80 px-5 py-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <a href="assignment_management.php<?php echo $url_suffix; ?>&edit_id=<?php echo $row['assignment_id']; ?>" class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 hover:text-amber-700 transition">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </a>
                            <span class="text-slate-200">|</span>
                            <a href="assignment_management.php<?php echo $url_suffix; ?>&delete_id=<?php echo $row['assignment_id']; ?>" onclick="return confirm('Are you sure you want to completely delete assignment context ID #<?php echo $row['assignment_id']; ?>? This action cannot be reverted.');" class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 hover:text-rose-700 transition">
                                <i class="fas fa-trash-alt"></i>
                                <span>Delete</span>
                            </a>
                        </div>
                    </div>

                    <?php
                }
            } else {
                ?>
                <div class="col-span-full bg-white border border-dashed border-slate-300 rounded-2xl p-8 text-center text-slate-400">
                    <i class="fas fa-folder-open text-3xl mb-2 block"></i>
                    <p class="text-sm">No assignment setups are currently configured in the database.</p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</main>

</div> </body> </html>