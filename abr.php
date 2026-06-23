<?php
include 'menu.php';
?>

<main class="flex-1 p-6 bg-slate-100 min-h-screen">

    <!-- Header -->
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
                    <i class="fas fa-file-circle-check"></i>
                </div>

            </div>

        </div>

    </div>

    <!-- Top Section -->
    <div class="grid grid-cols-2 gap-5">

        <!-- Submission Retrieval -->
        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-blue-100">

            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Submission Retrieval
            </h3>

            <label class="block text-xs text-slate-500 mb-2">
                Student ID
            </label>

            <select class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm text-slate-700">

                <option>Select Student</option>
                <option>S001 - Ali Ahmad</option>
                <option>S002 - Siti Nur</option>
                <option>S003 - John Tan</option>

            </select>

            <button class="mt-5 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white text-sm font-medium px-5 py-3 rounded-xl">

                Retrieve Submission

            </button>

        </div>

        <!-- Result -->
        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-cyan-100">

            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Submission Analysis
            </h3>

            <div class="text-center mt-4">

                <div class="w-24 h-24 rounded-full bg-gradient-to-r from-red-400 to-orange-500 flex items-center justify-center mx-auto shadow-lg">

                    <i class="fas fa-triangle-exclamation text-5xl text-white"></i>

                </div>

                <h1 class="text-3xl font-bold text-red-600 mt-5">
                    LATE
                </h1>

                <p class="text-sm text-slate-500 mt-2">
                    Submitted 2 Days After Due Date
                </p>

                <div class="w-48 mx-auto mt-5">

                    <div class="h-2 bg-slate-200 rounded-full">

                        <div class="h-2 bg-gradient-to-r from-red-500 to-orange-500 rounded-full w-[80%]"></div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Bottom Section -->
    <div class="grid grid-cols-2 gap-5 mt-5">

        <!-- Submission Info -->
        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-blue-100">

            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Submission Information
            </h3>

            <div class="space-y-4">

                <div class="bg-slate-50 p-4 rounded-xl">
                    <p class="text-xs text-slate-500">Student Name</p>
                    <h4 class="font-semibold">Ali Ahmad</h4>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl">
                    <p class="text-xs text-slate-500">Assignment Title</p>
                    <h4 class="font-semibold">Multimedia Project</h4>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl">
                    <p class="text-xs text-slate-500">File Size</p>
                    <h4 class="font-semibold">6.2 MB</h4>
                </div>

            </div>

        </div>

        <!-- Metadata Analysis -->
        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-cyan-100">

            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Metadata Analysis
            </h3>

            <div class="grid grid-cols-2 gap-4">

                <div class="bg-blue-50 rounded-2xl p-4">

                    <p class="text-xs text-slate-500">
                        Submission Date
                    </p>

                    <h4 class="text-sm font-semibold text-slate-800 mt-1">
                        10/06/2026
                    </h4>

                </div>

                <div class="bg-cyan-50 rounded-2xl p-4">

                    <p class="text-xs text-slate-500">
                        Due Date
                    </p>

                    <h4 class="text-sm font-semibold text-slate-800 mt-1">
                        08/06/2026
                    </h4>

                </div>

                <div class="bg-indigo-50 rounded-2xl p-4">

                    <p class="text-xs text-slate-500">
                        Status
                    </p>

                    <h4 class="text-sm font-semibold text-red-600 mt-1">
                        Late Submission
                    </h4>

                </div>

                <div class="bg-sky-50 rounded-2xl p-4">

                    <p class="text-xs text-slate-500">
                        File Validation
                    </p>

                    <h4 class="text-sm font-semibold text-orange-500 mt-1">
                        Oversized
                    </h4>

                </div>

            </div>

        </div>

    </div>

</main>

</body>
</html>