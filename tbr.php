<?php
include 'menu.php';
// Include database connections ($conn for gr07, $conn_mmdb for mmdb2026)
include 'db_conn.php';

// Initialize variables
$themes = [];
$authenticated_student = null;
$selected_submission = null;
$text_content = '';
$classification = 'Keyword-Based';
$words_analysed = 0;
$themes_found = 0;
$success_msg = '';

// Fetch all students for the authentication datalist if connection exists
$students_list = [];
if ($conn_mmdb) {
    $stu_query = "SELECT id, matric_no, full_name, phone_no, group_no FROM vstu";
    $stu_result = mysqli_query($conn_mmdb, $stu_query);
    if ($stu_result) {
        while ($row = mysqli_fetch_assoc($stu_result)) {
            $students_list[] = $row;
        }
    }
}

// Handle Student Authentication
if (isset($_POST['proceed_auth'])) {
    $search_input = trim($_POST['auth_search'] ?? '');
    $phone_input = trim($_POST['auth_phone'] ?? '');

    if (!empty($search_input) && !empty($phone_input)) {
        foreach ($students_list as $stu) {
            if (($stu['matric_no'] === $search_input || $stu['full_name'] === $search_input) && $stu['phone_no'] === $phone_input) {
                $authenticated_student = $stu;
                break;
            }
        }
    }
}

// Persist authenticated student session via hidden inputs if already authenticated
if (isset($_POST['auth_id_hidden']) && empty($authenticated_student)) {
    foreach ($students_list as $stu) {
        if ($stu['id'] == $_POST['auth_id_hidden']) {
            $authenticated_student = $stu;
            break;
        }
    }
}

// Fetch available file paths for the authenticated student from gr07 submission table
$available_submissions = [];
if ($authenticated_student && $conn) {
    $sub_query = "SELECT submission_id, file_path, file_validation FROM submission WHERE student_id = " . intval($authenticated_student['id']);
    // Fallback: if student_id is null in database entries, match via matric number
    $sub_result = mysqli_query($conn, $sub_query);
    if ($sub_result && mysqli_num_rows($sub_result) > 0) {
        while ($row = mysqli_fetch_assoc($sub_result)) {
            $available_submissions[] = $row;
        }
    } else {
        // Fallback check matching matric number
        $sub_query_alt = "SELECT submission_id, file_path, file_validation FROM submission WHERE student_matric_no = '" . mysqli_real_escape_string($conn, $authenticated_student['matric_no']) . "'";
        $sub_result_alt = mysqli_query($conn, $sub_query_alt);
        if ($sub_result_alt) {
            while ($row = mysqli_fetch_assoc($sub_result_alt)) {
                $available_submissions[] = $row;
            }
        }
    }
}

// Handle Theme Analysis
if (isset($_POST['analyze']) && $authenticated_student) {
    $text_content = $_POST['content'] ?? '';
    $selected_sub_id = $_POST['submission_id'] ?? '';
    
    foreach ($available_submissions as $sub) {
        if ($sub['submission_id'] == $selected_sub_id) {
            $selected_submission = $sub;
            break;
        }
    }

    if (!empty($text_content) && $selected_submission) {
        $text = strtolower($text_content);

        // Motivational
        if (str_contains($text, "trying") || str_contains($text, "effort") || str_contains($text, "hard work") || str_contains($text, "determination") || str_contains($text, "never give up")) {
            $themes[] = "Motivational";
        }
        // Inspirational
        if (str_contains($text, "hope") || str_contains($text, "dream") || str_contains($text, "future") || str_contains($text, "inspire")) {
            $themes[] = "Inspirational";
        }
        // Positive Mindset
        if (str_contains($text, "positive") || str_contains($text, "success") || str_contains($text, "believe") || str_contains($text, "confidence")) {
            $themes[] = "Positive Mindset";
        }
        // Educational
        if (str_contains($text, "study") || str_contains($text, "education") || str_contains($text, "learning") || str_contains($text, "research")) {
            $themes[] = "Educational";
        }
        // Technology
        if (str_contains($text, "technology") || str_contains($text, "software") || str_contains($text, "computer") || str_contains($text, "database") || str_contains($text, "ai")) {
            $themes[] = "Technology";
        }
        // Business
        if (str_contains($text, "business") || str_contains($text, "marketing") || str_contains($text, "sales") || str_contains($text, "customer")) {
            $themes[] = "Business";
        }
        // Creative
        if (str_contains($text, "poem") || str_contains($text, "song") || str_contains($text, "music") || str_contains($text, "story")) {
            $themes[] = "Creative";
        }
        // Emotional
        if (str_contains($text, "love") || str_contains($text, "happy") || str_contains($text, "sad") || str_contains($text, "emotion")) {
            $themes[] = "Emotional";
        }
        // Adventure
        if (str_contains($text, "travel") || str_contains($text, "journey") || str_contains($text, "hiking") || str_contains($text, "explore")) {
            $themes[] = "Adventure";
        }
        // Environment
        if (str_contains($text, "nature") || str_contains($text, "forest") || str_contains($text, "ocean") || str_contains($text, "climate")) {
            $themes[] = "Environment";
        }

        if (empty($themes)) {
            $themes[] = "General Content";
        }

        $words_analysed = str_word_count($text_content);
        $themes_found = count($themes);
        $classification = implode(', ', $themes);
    }
}

// Handle Save Content Action
if (isset($_POST['save_content']) && $conn) {
    $sub_id = intval($_POST['save_submission_id'] ?? 0);
    $filepath = $_POST['save_file_path'] ?? '';
    $filetype = $_POST['save_file_type'] ?? '';
    $extracted = $_POST['save_extracted_text'] ?? '';
    $theme_cat = $_POST['save_classification'] ?? '';
    $w_analyse = intval($_POST['save_words_analysed'] ?? 0);
    $t_found = intval($_POST['save_themes_found'] ?? 0);

    if ($sub_id > 0) {
        $insert_query = "INSERT INTO multimedia_content (submission_id, content_file_path, content_file_type, extracted_text, tbr_theme_category, word_analyse, theme_found) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issssii", $sub_id, $filepath, $filetype, $extracted, $theme_cat, $w_analyse, $t_found);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Analysis details saved successfully!";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Handle Reset Action
if (isset($_POST['reset_content'])) {
    $themes = [];
    $authenticated_student = null;
    $selected_submission = null;
    $text_content = '';
    $classification = 'Keyword-Based';
    $words_analysed = 0;
    $themes_found = 0;
}
?>

<main class="flex-1 p-6 bg-slate-100 min-h-screen">

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

    <div class="mb-8 bg-white rounded-3xl p-6 shadow-xl">
        <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
            Student Authentication
        </h3>
        <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-2">Matric No or Full Name</label>
                <input type="text" name="auth_search" list="students_data" class="w-full border border-slate-200 rounded-xl p-3 text-sm" placeholder="Type name or matric number..." value="<?php echo htmlspecialchars($_POST['auth_search'] ?? ($authenticated_student ? $authenticated_student['full_name'] : '')); ?>" required>
                <datalist id="students_data">
                    <?php foreach ($students_list as $stu): ?>
                        <option value="<?php echo htmlspecialchars($stu['matric_no']); ?>"><?php echo htmlspecialchars($stu['full_name'] . " (" . $stu['matric_no'] . ") " . $stu['group_no']); ?></option>
                        <option value="<?php echo htmlspecialchars($stu['full_name']); ?>"><?php echo htmlspecialchars($stu['full_name'] . " (" . $stu['matric_no'] . ") " . $stu['group_no']); ?></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-2">Phone Number</label>
                <input type="text" name="auth_phone" class="w-full border border-slate-200 rounded-xl p-3 text-sm" placeholder="Enter phone number..." value="<?php echo htmlspecialchars($_POST['auth_phone'] ?? ($authenticated_student ? $authenticated_student['phone_no'] : '')); ?>" required>
            </div>
            <div>
                <button type="submit" name="proceed_auth" class="w-full bg-gradient-to-r from-slate-900 to-blue-900 text-white px-5 py-3 rounded-xl hover:opacity-90 transition">
                    Proceed Theme Analysis
                </button>
            </div>
        </form>
        <?php if ($authenticated_student): ?>
            <div class="mt-4 p-3 bg-green-50 text-green-800 rounded-xl text-xs font-medium">
                ✓ Authenticated successfully: <strong><?php echo htmlspecialchars($authenticated_student['full_name']); ?></strong> (<?php echo htmlspecialchars($authenticated_student['matric_no']); ?>) - Group <?php echo htmlspecialchars($authenticated_student['group_no']); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div class="bg-white rounded-3xl p-6 shadow-xl">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Content Retrieval
            </h3>

            <form method="post">
                <input type="hidden" name="auth_id_hidden" value="<?php echo $authenticated_student ? htmlspecialchars($authenticated_student['id']) : ''; ?>">

                <label class="block text-xs text-slate-500 mb-2">Select Submission File Path</label>
                <select name="submission_id" class="w-full border border-slate-200 rounded-xl p-3 text-sm mb-4" required>
                    <option value="">-- Choose File Path --</option>
                    <?php if ($authenticated_student): ?>
                        <?php foreach ($available_submissions as $sub): ?>
                            <?php 
                                $display_path = !empty($sub['file_path']) ? $sub['file_path'] : "/storage/docs/" . strtolower($authenticated_student['matric_no']) . "_report.pdf"; 
                            ?>
                            <option value="<?php echo htmlspecialchars($sub['submission_id']); ?>" <?php echo (isset($_POST['submission_id']) && $_POST['submission_id'] == $sub['submission_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($display_path); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Authenticate a student first</option>
                    <?php endif; ?>
                </select>

                <label class="block text-xs text-slate-500 mb-2">
                    Enter Text Content
                </label>
                <textarea
                    name="content"
                    rows="8"
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm mb-4"
                    placeholder="Example: This geodatabase report outlines a structured implementation strategy. Sustaining an active, positive mindset..."
                    required><?php echo htmlspecialchars($text_content ? $text_content : ($_POST['content'] ?? '')); ?></textarea>

                <button
                    type="submit"
                    name="analyze"
                    <?php echo !$authenticated_student ? 'disabled' : ''; ?>
                    class="bg-gradient-to-r from-blue-500 to-cyan-500 text-white px-5 py-3 rounded-xl hover:opacity-90 transition disabled:opacity-50">
                    Analyze Theme
                </button>
            </form>
        </div>

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
                    if (isset($_POST['analyze']) && !empty($themes)) {
                        echo htmlspecialchars($classification);
                    } else {
                        echo "No Analysis";
                    }
                    ?>
                </h1>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

        <div class="space-y-5">
            <div class="bg-white rounded-3xl p-6 shadow-xl">
                <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-4">
                    Retrieval Details
                </h3>
                <div class="text-sm space-y-2 text-slate-700">
                    <div><strong>Matric No:</strong> <?php echo $authenticated_student ? htmlspecialchars($authenticated_student['matric_no']) : '-'; ?></div>
                    <div><strong>Full Name:</strong> <?php echo $authenticated_student ? htmlspecialchars($authenticated_student['full_name']) : '-'; ?></div>
                    <div><strong>Group No:</strong> <?php echo $authenticated_student ? htmlspecialchars($authenticated_student['group_no']) : '-'; ?></div>
                    <div><strong>File Path:</strong> 
                        <?php 
                        if ($selected_submission) {
                            echo htmlspecialchars(!empty($selected_submission['file_path']) ? $selected_submission['file_path'] : "/storage/docs/" . strtolower($authenticated_student['matric_no']) . "_report.pdf");
                        } else { echo '-'; }
                        ?>
                    </div>
                    <div><strong>File Type:</strong> 
                        <?php 
                        if ($selected_submission) {
                            $path = !empty($selected_submission['file_path']) ? $selected_submission['file_path'] : ".pdf";
                            echo htmlspecialchars(strtoupper(pathinfo($path, PATHINFO_EXTENSION)));
                        } else { echo '-'; }
                        ?>
                    </div>
                    <div><strong>Text Content:</strong> <span class="text-xs text-slate-500 italic"><?php echo $text_content ? htmlspecialchars(substr($text_content, 0, 100)) . '...' : '-'; ?></span></div>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-xl">
                <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                    Detected Themes
                </h3>
                <?php
                if (isset($_POST['analyze']) && !empty($themes)) {
                    foreach ($themes as $theme) {
                        echo "
                        <div class='bg-blue-50 p-4 rounded-xl mb-3'>
                            <h4 class='font-semibold text-slate-800'>
                                $theme
                            </h4>
                        </div>";
                    }
                } else {
                    echo "<p class='text-sm text-slate-400 italic'>No themes detected yet.</p>";
                }
                ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-xl flex flex-col justify-between">
            <div>
                <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                    Theme Statistics
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 rounded-2xl p-4">
                        <p class="text-xs text-slate-500">Words Analysed</p>
                        <h4 class="font-semibold text-lg">
                            <?php echo $words_analysed; ?>
                        </h4>
                    </div>

                    <div class="bg-cyan-50 rounded-2xl p-4">
                        <p class="text-xs text-slate-500">Themes Found</p>
                        <h4 class="font-semibold text-lg">
                            <?php echo $themes_found; ?>
                        </h4>
                    </div>

                    <div class="bg-indigo-50 rounded-2xl p-4 col-span-2">
                        <p class="text-xs text-slate-500">Classification</p>
                        <h4 class="font-semibold">
                            <?php echo htmlspecialchars($classification); ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-5 bg-white rounded-3xl p-6 shadow-xl">
        <form method="post" class="grid grid-cols-2 gap-4">
            <input type="hidden" name="auth_id_hidden" value="<?php echo $authenticated_student ? htmlspecialchars($authenticated_student['id']) : ''; ?>">
            
            <input type="hidden" name="save_submission_id" value="<?php echo $selected_submission ? htmlspecialchars($selected_submission['submission_id']) : ''; ?>">
            <input type="hidden" name="save_file_path" value="<?php echo $selected_submission ? htmlspecialchars(!empty($selected_submission['file_path']) ? $selected_submission['file_path'] : "/storage/docs/" . strtolower($authenticated_student['matric_no']) . "_report.pdf") : ''; ?>">
            <input type="hidden" name="save_file_type" value="<?php echo $selected_submission ? htmlspecialchars(strtoupper(pathinfo(!empty($selected_submission['file_path']) ? $selected_submission['file_path'] : ".pdf", PATHINFO_EXTENSION))) : ''; ?>">
            <input type="hidden" name="save_extracted_text" value="<?php echo htmlspecialchars($text_content); ?>">
            <input type="hidden" name="save_classification" value="<?php echo htmlspecialchars($classification); ?>">
            <input type="hidden" name="save_words_analysed" value="<?php echo $words_analysed; ?>">
            <input type="hidden" name="save_themes_found" value="<?php echo $themes_found; ?>">

            <button type="submit" name="reset_content" class="bg-slate-200 text-slate-700 px-4 py-3 rounded-xl font-medium hover:bg-slate-300 transition">
                Reset Content
            </button>
            <button type="submit" name="save_content" <?php echo !$selected_submission ? 'disabled' : ''; ?> class="bg-green-600 text-white px-4 py-3 rounded-xl font-medium hover:bg-green-700 transition disabled:opacity-50">
                Save Content
            </button>
        </form>
        
        <?php if (!empty($success_msg)): ?>
            <div class="mt-3 p-2 bg-green-100 text-green-800 rounded-lg text-xs text-center font-medium">
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-8 bg-white rounded-3xl p-6 shadow-xl overflow-hidden">
        <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
            Theme Classification Analysis History
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-800 text-white text-xs uppercase tracking-wider">
                        <th class="p-3 border border-slate-700">Matric No</th>
                        <th class="p-3 border border-slate-700">Full Name</th>
                        <th class="p-3 border border-slate-700">Group No</th>
                        <th class="p-3 border border-slate-700">File Path</th>
                        <th class="p-3 border border-slate-700">Content File Type</th>
                        <th class="p-3 border border-slate-700">TBR Theme Category</th>
                        <th class="p-3 border border-slate-700">Word Analyse</th>
                        <th class="p-3 border border-slate-700">Themes Found</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    <?php
                    if ($conn) {
                        $history_query = "SELECT s.student_id, s.student_matric_no, s.file_path, mc.content_file_type, mc.tbr_theme_category, mc.word_analyse, mc.theme_found 
                                          FROM multimedia_content mc 
                                          JOIN submission s ON mc.submission_id = s.submission_id 
                                          ORDER BY mc.content_id DESC";
                        $history_result = mysqli_query($conn, $history_query);
                        
                        if ($history_result && mysqli_num_rows($history_result) > 0) {
                            while ($row = mysqli_fetch_assoc($history_result)) {
                                // Match student info from vstu array cache using id or matric_no
                                $matched_stu = null;
                                foreach ($students_list as $stu) {
                                    if (($row['student_id'] && $stu['id'] == $row['student_id']) || ($stu['matric_no'] === $row['student_matric_no'])) {
                                        $matched_stu = $stu;
                                        break;
                                    }
                                }
                                
                                $m_no = $matched_stu ? $matched_stu['matric_no'] : $row['student_matric_no'];
                                $f_name = $matched_stu ? $matched_stu['full_name'] : '-';
                                $g_no = $matched_stu ? $matched_stu['group_no'] : '-';
                                $f_path = !empty($row['file_path']) ? $row['file_path'] : "/storage/docs/" . strtolower($m_no) . "_report.pdf";

                                echo "<tr class='hover:bg-slate-50 border-b border-slate-100'>";
                                echo "<td class='p-3 border border-slate-100'>" . htmlspecialchars($m_no) . "</td>";
                                echo "<td class='p-3 border border-slate-100'>" . htmlspecialchars($f_name) . "</td>";
                                echo "<td class='p-3 border border-slate-100'>" . htmlspecialchars($g_no) . "</td>";
                                echo "<td class='p-3 border border-slate-100 text-xs text-slate-500'>" . htmlspecialchars($f_path) . "</td>";
                                echo "<td class='p-3 border border-slate-100'>" . htmlspecialchars($row['content_file_type']) . "</td>";
                                echo "<td class='p-3 border border-slate-100 font-medium text-slate-900'>" . htmlspecialchars($row['tbr_theme_category']) . "</td>";
                                echo "<td class='p-3 border border-slate-100'>" . htmlspecialchars($row['word_analyse']) . "</td>";
                                echo "<td class='p-3 border border-slate-100'>" . htmlspecialchars($row['theme_found']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='p-4 text-center text-slate-400 italic'>No classification history found.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='p-4 text-center text-red-400 italic'>Database connection failed.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

</body>
</html>