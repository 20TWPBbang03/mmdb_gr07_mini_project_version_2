<?php
session_start();
if (isset($_SESSION['student_logged_in'])) {
    header("Location: dashboard.php");
    exit();
}

// Hardcoded enum set array fetched directly from your mmdb_gr07.sql structural dump
$group_options = ['GK01','GK02','GR01','GR02','GR03','GR04','GR05','GR06','GR07','GR08','GR09','GS01','GS02','GS03','GS04','GS05','GW01','GW02','GW03','GW04','GW05','GW06','GW07','GW08','GW09'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Multimedia Retrieval System GR07</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row overflow-hidden min-h-[500px]">
        
        <div class="w-full md:w-1/2 bg-slate-900 text-white p-10 flex flex-col justify-between bg-gradient-to-b from-slate-900 to-slate-950">
            <div>
                <h1 class="text-2xl font-bold tracking-wide">Welcome to Multimedia Retrieval System GR07</h1>
                <p class="text-slate-400 text-sm mt-2">Academic Core Portal Engine</p>
            </div>
            
            <div class="my-8">
                <div class="text-blue-500 text-5xl mb-4">
                    <i class="fas fa-database"></i>
                </div>
                <h2 class="text-xl font-semibold">Student Authentication</h2>
                <p class="text-slate-400 text-xs mt-2 leading-relaxed">
                    Access restricted to enrolled system evaluation users. Authenticate using university profile vectors.
                </p>
            </div>
            
            <div class="text-xs text-slate-500">
                &copy; 2026 Core Platform Framework.
            </div>
        </div>

        <div class="w-full md:w-1/2 p-8 flex flex-col justify-center bg-white">
            <h3 class="text-xl font-bold text-slate-800 mb-1">Student Login</h3>
            <p class="text-slate-500 text-xs mb-6">Please make sure you already registered the account before login</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-xs rounded-lg flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-600 text-xs rounded-lg flex items-center gap-2">
                    <i class="fas fa-circle-check"></i>
                    <span><?php echo htmlspecialchars($_GET['success']); ?></span>
                </div>
            <?php endif; ?>

            <form action="login_action.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Target Project Allocation Group</label>
                    <select name="group_no" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                        <option value="" disabled selected>Select Group Allocation</option>
                        <?php foreach($group_options as $group): ?>
                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Matric Identification No.</label>
                    <input type="text" name="student_matric_no" required placeholder="e.g., B032110001" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Registered Contact Number</label>
                    <input type="password" name="phone_no" required placeholder="e.g., 0123456789" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                </div>

                <div class="pt-2 space-y-3">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm shadow-md shadow-blue-200">
                        Login
                    </button>
                    
                    
                </div>
            </form>
        </div>
    </div>

</body>
</html>