<?php

// 1. Include the dynamic sidebar menu at the very top
include('menu.php'); 
?>

<main class="flex-1 p-6 bg-slate-100 min-h-screen">


    <div class="mb-8">

        <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-cyan-800 rounded-xl p-6 shadow-lg">

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="text-2xl font-bold text-white">
                        Dashboard Group GR07
                    </h2>

                    <p class="text-cyan-200 text-sm mt-1">
                        Welcome to the Multimedia Retrieval System platform.
                    </p>
                </div>

                <div class="text-cyan-300 text-4xl">
                    <i class="fas fa-file-circle-check"></i>
                </div>

            </div>

        </div>

    </div>

    
        
        <div class="grid grid-cols-3 gap-5">

            <a href="abr.php">

                <div class="bg-white rounded-3xl p-6 shadow-xl hover:scale-105 transition duration-300 cursor-pointer">

                    <div class="text-blue-500 text-4xl mb-4">
                        <i class="fas fa-file-circle-check"></i>
                    </div>

                    <h3 class="font-bold text-lg">
                        Late Submission Detection
                    </h3>

                    <p class="text-sm text-slate-500 mt-2">
                        Attribute-Based Retrieval
                    </p>

                </div>

            </a>

            <a href="tbr.php">

                <div class="bg-white rounded-3xl p-6 shadow-xl hover:scale-105 transition duration-300 cursor-pointer">

                    <div class="text-cyan-500 text-4xl mb-4">
                        <i class="fas fa-file-lines"></i>
                    </div>

                    <h3 class="font-bold text-lg">
                        Theme Analyzer
                    </h3>

                    <p class="text-sm text-slate-500 mt-2">
                        Text-Based Retrieval
                    </p>

                </div>

            </a>

            <a href="cbr2.php">

                <div class="bg-white rounded-3xl p-6 shadow-xl hover:scale-105 transition duration-300 cursor-pointer">

                    <div class="text-yellow-500 text-4xl mb-4">
                        <i class="fas fa-face-smile"></i>
                    </div>

                    <h3 class="font-bold text-lg">
                        Facial Expression Detection
                    </h3>

                    <p class="text-sm text-slate-500 mt-2">
                        Content-Based Retrieval
                    </p>

                </div>

            </a>

        </div>

    
</div> </main> </body> </html> ```