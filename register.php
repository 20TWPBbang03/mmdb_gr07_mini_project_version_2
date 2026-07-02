<?php
session_start();
if (isset($_SESSION['student_logged_in'])) {
    header("Location: dashboard.php");
    exit();
}

$group_options = ['GK01','GK02','GR01','GR02','GR03','GR04','GR05','GR06','GR07','GR08','GR09','GS01','GS02','GS03','GS04','GS05','GW01','GW02','GW03','GW04','GW05','GW06','GW07','GW08','GW09'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Multimedia Retrieval System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row overflow-hidden min-h-[550px]">
        
        <div class="w-full md:w-1/3 bg-slate-900 text-white p-10 flex flex-col justify-between bg-gradient-to-b from-slate-900 to-slate-950">
            <div>
                <h1 class="text-xl font-bold tracking-wide">Multimedia Retrieval</h1>
                <p class="text-slate-400 text-xs mt-1">Identity Profile Provisioning</p>
            </div>
            
            <div class="my-6">
                <div class="text-blue-500 text-4xl mb-3">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2 class="text-lg font-semibold">Student Registration</h2>
                <p class="text-slate-400 text-xs mt-1.5 leading-relaxed">
                    Set up your platform profile schema properties to execute validated analytics components within authorized team environment frames.
                </p>
            </div>
            
            <div class="text-xs text-slate-500">
                <a href="login.php" class="text-blue-400 hover:text-blue-300 transition"><i class="fas fa-arrow-left mr-1"></i> Return to login</a>
            </div>
        </div>

        <div class="w-full md:w-2/3 p-8 flex flex-col justify-center bg-white">
            <h3 class="text-xl font-bold text-slate-800 mb-1">Student Registration</h3>
            <p class="text-slate-500 text-xs mb-6">Complete all structured input records to write your persistent credentials into the master tables.</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-xs rounded-lg flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>

            <form action="register_action.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Matric Number</label>
                        <input type="text" name="student_matric_no" required placeholder="e.g., B032110001" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Target Project Group</label>
                        <select name="group_no" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                            <option value="" disabled selected>Select Allocation</option>
                            <?php foreach($group_options as $group): ?>
                                <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Full Corporate Name</label>
                        <input type="text" name="full_name" required placeholder="Your full name" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Contact Number (Phone)</label>
                        <input type="text" name="phone_no" required placeholder="e.g., 0123456789" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Personal Life Motto <span class="text-slate-400 lowercase italic">(Optional)</span></label>
                    <textarea name="life_motto" rows="2" placeholder="Share your core design philosophy or motto..." class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 focus:outline-none focus:border-blue-500 resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Profile Photo Image Avatar</label>
                    <input type="file" name="profile_image" required accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>

                <div class="flex justify-end pt-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200 text-sm shadow-md shadow-blue-200">
                        Register Account
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>