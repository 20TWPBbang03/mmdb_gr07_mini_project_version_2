<?php
// Enforce strict authenticated environment checking rules across inclusion scopes
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_logged_in'])) {
    header("Location: login.php");
    exit();
}

$current_page = basename($_SERVER['SCRIPT_NAME']);

// Dynamic safe encoding context vector builder
$url_suffix = "?matric_no=" . urlencode($_SESSION['student_matric_no']) . "&group_no=" . urlencode($_SESSION['group_no']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multimedia Retrieval System GR07</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* Base sidebar link styles */
        .sidebar-link {
            transition: background-color 0.2s ease;
        }
        
        /* Hover effect for all links except the active one */
        .sidebar-link:not(.active-module):hover {
            background-color: #1e293b !important; /* Tailwind's bg-slate-800 */
        }
        
        /* Active module styling */
        .active-module {
            background-color: #2563eb !important; /* Tailwind's bg-blue-600 */
        }
    </style>
</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">

        <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between flex-shrink-0">

           <div>
               <div class="p-6 border-b border-slate-700">
                    <a href="dashboard.php<?php echo $url_suffix; ?>" class="text-inherit hover:text-inherit">
                        <h1 class="text-xl font-bold text-white">
                            Multimedia Retrieval GR07
                        </h1>
                    </a>
                </div>

                <nav class="mt-6 text-sm">

                    <a href="dashboard.php<?php echo $url_suffix; ?>"
                        class="sidebar-link flex items-center px-6 py-3 <?php echo ($current_page == 'dashboard.php') ? 'active-module' : ''; ?>">
                        <i class="fas fa-home w-6"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="assignment_management.php<?php echo $url_suffix; ?>"
                        class="sidebar-link flex items-center px-6 py-3 <?php echo ($current_page == 'assignment_management.php') ? 'active-module' : ''; ?>">
                        <i class="fas fa-tasks w-6"></i>
                        <span>Assignment Management</span>
                    </a>

                    <a href="abr.php<?php echo $url_suffix; ?>"
                        class="sidebar-link flex items-center px-6 py-3 <?php echo ($current_page == 'abr.php') ? 'active-module' : ''; ?>">
                        <i class="fas fa-database w-6"></i>
                        <span>ABR Module</span>
                    </a>

                    <a href="tbr.php<?php echo $url_suffix; ?>"
                        class="sidebar-link flex items-center px-6 py-3 <?php echo ($current_page == 'tbr.php') ? 'active-module' : ''; ?>">
                        <i class="fas fa-file-lines w-6"></i>
                        <span>TBR Module</span>
                    </a>

                    <a href="cbr.php<?php echo $url_suffix; ?>"
                        class="sidebar-link flex items-center px-6 py-3 <?php echo ($current_page == 'cbr.php') ? 'active-module' : ''; ?>">
                        <i class="fas fa-face-smile w-6"></i>
                        <span>CBR Module</span>
                    </a>

                </nav>
           </div>

           <div class="p-4 border-t border-slate-800 bg-slate-950 flex flex-col gap-2">
               <div class="flex items-center gap-3">
                   <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-inner border border-blue-400">
                       <i class="fas fa-user-graduate"></i>
                   </div>
                   <div class="overflow-hidden">
                       <p class="text-xs font-semibold text-white truncate"><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                       <p class="text-[11px] text-slate-400 tracking-wider font-mono mt-0.5"><?php echo htmlspecialchars($_SESSION['student_matric_no']); ?></p>
                   </div>
               </div>
               <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1 border-t border-slate-900">
                   <span class="bg-slate-800 text-cyan-400 font-bold px-2 py-0.5 rounded uppercase tracking-wide">Group: <?php echo htmlspecialchars($_SESSION['group_no']); ?></span>
                   <div class="flex items-center gap-2">
                       <a href="edit_profile.php" class="text-blue-400 hover:text-blue-300 flex items-center gap-0.5 transition duration-150 font-medium">
                           <i class="fas fa-user-edit scale-90"></i>
                           <span>Edit</span>
                       </a>
                       <span class="text-slate-800">|</span>
                       <a href="logout.php" class="text-red-400 hover:text-red-300 flex items-center gap-0.5 transition duration-150">
                           <i class="fas fa-arrow-right-from-bracket scale-90"></i>
                           <span>Exit</span>
                       </a>
                   </div>
               </div>
           </div>
        </aside>