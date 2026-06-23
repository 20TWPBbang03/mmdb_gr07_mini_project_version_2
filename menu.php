<?php
// Get the current page filename (e.g., 'index.php', 'abr.php')
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multimedia Retrieval System</title>

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

        <aside class="w-64 bg-slate-900 text-white">

           <div class="p-6 border-b border-slate-700">
                <a href="dashboard.php" class="text-inherit hover:text-inherit">
                    <h1 class="text-xl font-bold text-white">
                        Multimedia Retrieval
                    </h1>
                </a>
            </div>

            <nav class="mt-6 text-sm">

                <a href="dashboard.php"
                    class="sidebar-link flex items-center px-6 py-3 <?php echo ($current_page == 'dashboard.php') ? 'active-module' : ''; ?>">
                    <i class="fas fa-home w-6"></i>
                    <span>Dashboard</span>
                </a>

                <a href="abr.php"
                    class="sidebar-link flex items-center px-6 py-3 <?php echo ($current_page == 'abr.php') ? 'active-module' : ''; ?>">
                    <i class="fas fa-database w-6"></i>
                    <span>ABR Module</span>
                </a>

                <a href="tbr.php"
                    class="sidebar-link flex items-center px-6 py-3 <?php echo ($current_page == 'tbr.php') ? 'active-module' : ''; ?>">
                    <i class="fas fa-file-lines w-6"></i>
                    <span>TBR Module</span>
                </a>

                <a href="cbr.php"
                    class="sidebar-link flex items-center px-6 py-3 <?php echo ($current_page == 'cbr.php') ? 'active-module' : ''; ?>">
                    <i class="fas fa-face-smile w-6"></i>
                    <span>CBR Module</span>
                </a>

                <!--
                <a href="report.php"
                    class="sidebar-link flex items-center px-6 py-3 <?php echo ($current_page == 'report.php') ? 'active-module' : ''; ?>">
                    <i class="fas fa-chart-bar w-6"></i>
                    <span>Reports</span>
                </a>-->

            </nav>
        </aside>