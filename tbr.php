<?php
include 'menu.php';

$themes = [];

if(isset($_POST['analyze']))
{
    $text = strtolower($_POST['content']);

    // Motivational
    if(
        str_contains($text,"trying") ||
        str_contains($text,"effort") ||
        str_contains($text,"hard work") ||
        str_contains($text,"determination") ||
        str_contains($text,"never give up")
    ){
        $themes[] = "Motivational";
    }

    // Inspirational
    if(
        str_contains($text,"hope") ||
        str_contains($text,"dream") ||
        str_contains($text,"future") ||
        str_contains($text,"inspire")
    ){
        $themes[] = "Inspirational";
    }

    // Positive Mindset
    if(
        str_contains($text,"positive") ||
        str_contains($text,"success") ||
        str_contains($text,"believe") ||
        str_contains($text,"confidence")
    ){
        $themes[] = "Positive Mindset";
    }

    // Educational
    if(
        str_contains($text,"study") ||
        str_contains($text,"education") ||
        str_contains($text,"learning") ||
        str_contains($text,"research")
    ){
        $themes[] = "Educational";
    }

    // Technology
    if(
        str_contains($text,"technology") ||
        str_contains($text,"software") ||
        str_contains($text,"computer") ||
        str_contains($text,"database") ||
        str_contains($text,"ai")
    ){
        $themes[] = "Technology";
    }

    // Business
    if(
        str_contains($text,"business") ||
        str_contains($text,"marketing") ||
        str_contains($text,"sales") ||
        str_contains($text,"customer")
    ){
        $themes[] = "Business";
    }

    // Creative
    if(
        str_contains($text,"poem") ||
        str_contains($text,"song") ||
        str_contains($text,"music") ||
        str_contains($text,"story")
    ){
        $themes[] = "Creative";
    }

    // Emotional
    if(
        str_contains($text,"love") ||
        str_contains($text,"happy") ||
        str_contains($text,"sad") ||
        str_contains($text,"emotion")
    ){
        $themes[] = "Emotional";
    }

    // Adventure
    if(
        str_contains($text,"travel") ||
        str_contains($text,"journey") ||
        str_contains($text,"hiking") ||
        str_contains($text,"explore")
    ){
        $themes[] = "Adventure";
    }

    // Environment
    if(
        str_contains($text,"nature") ||
        str_contains($text,"forest") ||
        str_contains($text,"ocean") ||
        str_contains($text,"climate")
    ){
        $themes[] = "Environment";
    }

    if(empty($themes))
    {
        $themes[] = "General Content";
    }
}
?>

<main class="flex-1 p-6 bg-slate-100 min-h-screen">

    <!-- Header -->
    <div class="mb-8">

        <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-cyan-800 rounded-xl p-6 shadow-lg">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="text-2xl font-bold text-white">
                        Text-Based Retrieval (TBR)
                    </h2>

                    <p class="text-cyan-200 text-sm mt-1">
                        Content Theme Classification
                    </p>

                </div>

                <div class="text-cyan-300 text-4xl">
                    <i class="fas fa-file-lines"></i>
                </div>

            </div>

        </div>

    </div>

    <!-- Top Section -->
    <div class="grid grid-cols-2 gap-5">

        <!-- Input -->
        <div class="bg-white rounded-3xl p-6 shadow-xl">

            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Content Retrieval
            </h3>

            <form method="post">

                <label class="block text-xs text-slate-500 mb-2">
                    Enter Text Content
                </label>

                <textarea
                    name="content"
                    rows="8"
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm"
                    placeholder="Example: I love hiking to see nature..."
                ></textarea>

                <button
                    type="submit"
                    name="analyze"
                    class="mt-5 bg-gradient-to-r from-blue-500 to-cyan-500 text-white px-5 py-3 rounded-xl">

                    Analyze Theme

                </button>

            </form>

        </div>

        <!-- Result -->
        <div class="bg-white rounded-3xl p-6 shadow-xl">

            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Classification Result
            </h3>

            <div class="text-center mt-5">

                <div class="w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mx-auto">

                    <i class="fas fa-tags text-5xl text-white"></i>

                </div>

                <h1 class="text-2xl font-bold text-slate-800 mt-5">

                    <?php
                    if(isset($_POST['analyze']))
                    {
                        echo count($themes) . " Theme(s)";
                    }
                    else
                    {
                        echo "No Analysis";
                    }
                    ?>

                </h1>

            </div>

        </div>

    </div>

    <!-- Bottom Section -->
    <div class="grid grid-cols-2 gap-5 mt-5">

        <!-- Detected Themes -->
        <div class="bg-white rounded-3xl p-6 shadow-xl">

            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Detected Themes
            </h3>

            <?php

            if(isset($_POST['analyze']))
            {
                foreach($themes as $theme)
                {
                    echo "
                    <div class='bg-blue-50 p-4 rounded-xl mb-3'>
                        <h4 class='font-semibold text-slate-800'>
                            $theme
                        </h4>
                    </div>";
                }
            }

            ?>

        </div>

        <!-- Theme Features -->
        <div class="bg-white rounded-3xl p-6 shadow-xl">

            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Theme Statistics
            </h3>

            <div class="grid grid-cols-2 gap-4">

                <div class="bg-blue-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Words Analysed</p>
                    <h4 class="font-semibold">
                        <?php
                        if(isset($_POST['analyze']))
                            echo str_word_count($_POST['content']);
                        else
                            echo "0";
                        ?>
                    </h4>
                </div>

                <div class="bg-cyan-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Themes Found</p>
                    <h4 class="font-semibold">
                        <?php
                        if(isset($_POST['analyze']))
                            echo count($themes);
                        else
                            echo "0";
                        ?>
                    </h4>
                </div>

                <div class="bg-indigo-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Classification</p>
                    <h4 class="font-semibold">
                        Keyword-Based
                    </h4>
                </div>

                <div class="bg-sky-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Status</p>
                    <h4 class="font-semibold text-green-600">
                        Completed
                    </h4>
                </div>

            </div>

        </div>

    </div>

</main>

</body>
</html>