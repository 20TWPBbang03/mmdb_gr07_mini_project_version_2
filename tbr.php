<?php
// Set maximum/unlimited execution and file upload limits at runtime
@ini_set('upload_max_filesize', '0'); // 0 sets it to unlimited in modern PHP, or use a high value like '4096M'
@ini_set('post_max_size', '0');       // 0 sets it to unlimited, or use a high value like '4096M'
@ini_set('memory_limit', '-1');       // -1 removes memory restrictions for processing large multimedia assets
@ini_set('max_execution_time', '0');  // 0 removes time-out limits for long uploads
@ini_set('max_input_time', '0');      // 0 removes time-out limits for parsing data

include 'menu.php';
// Include database connections ($conn for gr07, $conn_mmdb for mmdb2026)
include 'db_conn.php';

// Initialize variables
$themes = [];
$selected_submission = null;
$text_content = '';
$classification = 'Keyword-Based';
$words_analysed = 0;
$themes_found = 0;
$success_msg = '';

// New initialized inputs for file handling
$input_file_name = '';
$uploaded_file_path = '';
$uploaded_file_type = '';
$highlighted_text = '';

// Setup active student details dynamically from the logged-in session context
$authenticated_student = [
    'matric_no' => $_SESSION['student_matric_no'] ?? '',
    'full_name' => $_SESSION['full_name'] ?? '',
    'group_no'  => $_SESSION['group_no'] ?? ''
];

// Handle Reset Action (Placed up top so it clears inputs immediately before rendering)
if (isset($_POST['reset_content'])) {
    $themes = [];
    $text_content = '';
    $classification = 'Keyword-Based';
    $words_analysed = 0;
    $input_file_name = '';
    $uploaded_file_path = '';
    $uploaded_file_type = '';
    $highlighted_text = '';
}

// Handle Theme Analysis Execution
if (isset($_POST['analyze']) && !empty($authenticated_student['matric_no'])) {
    $text_content = $_POST['content'] ?? '';
    $input_file_name = trim($_POST['file_name'] ?? '');
    
    // Server-side word limitation safeguard matching the 500-word constraint
    $word_count_check = str_word_count($text_content);
    if ($word_count_check > 500) {
        // Truncate to maximum of 500 words safely if passed manually
        $words_array = preg_split('/\s+/', $text_content, 501);
        array_pop($words_array);
        $text_content = implode(' ', $words_array);
    }

    // Process file upload
    if (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] == 0) {
        $target_dir = "uploads/tbr/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $original_filename = basename($_FILES["upload_file"]["name"]);
        $ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        // Sanitize and create uniquely traceable target path
        $new_filename = "tbr_" . $authenticated_student['matric_no'] . "_" . time() . "." . $ext;
        $target_file_path = $target_dir . $new_filename;
        
        // Fixed: Use native standard tmp_name reliably across PHP server deployments
        if (move_uploaded_file($_FILES["upload_file"]["tmp_name"], $target_file_path)) {
            $uploaded_file_path = $target_file_path;
            $uploaded_file_type = strtoupper($ext);
        }
    }

    if (!empty($text_content) && !empty($input_file_name) && !empty($uploaded_file_path)) {
        // --- START OF API THEME DETECTION INTEGRATION ---
        // Official Google Gemini API Endpoint Configuration using your working credential format
        $api_key = 'AQ.Ab8RN6Ltlm0ktYtjm66HasYzA_dIpnQCYC8iXbD2JQR4K3bMnA';
        $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=' . $api_key;

        // Determine the precise MIME type to supply Google Gemini context
        $mime_type = $_FILES['upload_file']['type'] ?: 'application/octet-stream';
        
        // Read file contents and encode to base64 for inline payload transmission
        $file_data_base64 = base64_encode(file_get_contents($uploaded_file_path));

        // Create a precise instructional prompt forcing Gemini to return structured application/json data
        $prompt_instructions = "You are an automated Text-Based Retrieval theme classification module.
        Analyze the accompanying text content along with the uploaded file asset (which could be an image, text, audio, or video stream).
        Identify the overarching classification theme (e.g., 'Spatial Analysis', 'Database Security', 'Web Development', or 'General Content') and return a list of terms inside the text content that directly relate to or triggered that theme.
        
        Input Text Content: \"" . $text_content . "\"
        
        You MUST respond strictly in valid JSON format matching this schema without markdown decorators:
        {
            \"theme\": \"Detected Theme Label Here\",
            \"detected_keywords\": [\"keyword1\", \"keyword2\", \"keyword3\"]
        }";

        // Assemble the payload components matching the strict structured hierarchy of Gemini API
        $payload = [
            'contents' => [
                'parts' => [
                    [
                        'inlineData' => [
                            'mimeType' => $mime_type,
                            'data' => $file_data_base64
                        ]
                    ],
                    [
                        'text' => $prompt_instructions
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json'
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && $response) {
            $response_data = json_decode($response, true);
            
            // Step into the Gemini text extraction sequence to locate our returned JSON string
            $raw_json_output = $response_data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $result_data = json_decode(trim($raw_json_output), true);
            
            $classification = $result_data['theme'] ?? 'General Content';
            $matched_keywords = $result_data['detected_keywords'] ?? [];
        } else {
            // Fallback categorization layer if communication with the web API service encounters an error
            $classification = 'General Content';
            $matched_keywords = [];
        }

        // Dynamically build the regex string pattern tracking keywords to safely inject text highlights
        if (!empty($matched_keywords)) {
            $quoted_keywords = array_map('preg_quote', $matched_keywords);
            $regex_pattern = '/\b(' . implode('|', $quoted_keywords) . ')\b/i';
            
            // Count the total number of highlighted words displayed inside the theme output view
            preg_match_all($regex_pattern, $text_content, $matches);
            $words_analysed = count($matches[0]);
            
            $highlighted_text = preg_replace_callback($regex_pattern, function($matches) {
                return '<span class="bg-yellow-200 text-yellow-950 px-1 rounded font-semibold">' . $matches[0] . '</span>';
            }, $text_content);
        } else {
            $highlighted_text = $text_content;
            $words_analysed = 0;
        }
        // --- END OF API THEME DETECTION INTEGRATION ---
    }
}

// Handle Save Content Action Integration
if (isset($_POST['save_content']) && $conn) {
    $f_name = trim($_POST['save_file_name'] ?? '');
    $filepath = trim($_POST['save_file_path'] ?? '');
    $filetype = trim($_POST['save_file_type'] ?? '');
    $extracted = trim($_POST['save_extracted_text'] ?? '');
    $theme_cat = trim($_POST['save_classification'] ?? '');
    $w_analyse = intval($_POST['save_words_analysed'] ?? 0);
    $matric_no = $authenticated_student['matric_no'];

    // Enforce rigorous server-side non-blank structure assertion rules prior to committing
    if (!empty($f_name) && !empty($filepath) && !empty($filetype) && !empty($extracted) && !empty($theme_cat) && !empty($matric_no)) {
        
        // Note: Modified column insertion structure precisely targeting structural requests
        $insert_query = "INSERT INTO multimedia_content (content_file_name, content_file_type, content_file_path, extracted_text, tbr_theme_category, word_analyse, student_matric_no) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $insert_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssis", $f_name, $filetype, $filepath, $extracted, $theme_cat, $w_analyse, $matric_no);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Analysis details saved successfully!";
                
                // Clear state vector outputs dynamically following data save actions
                $themes = []; $text_content = ''; $classification = 'Keyword-Based'; $words_analysed = 0;
                $input_file_name = ''; $uploaded_file_path = ''; $uploaded_file_type = ''; $highlighted_text = '';
            }
            mysqli_stmt_close($stmt);
        }
    }
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
        <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-3">
            Active Student Profile Context
        </h3>
        <div class="p-4 bg-blue-50/70 border border-blue-100 text-slate-800 rounded-xl text-sm font-medium flex items-center justify-between">
            <div>
                Student: <strong><?php echo htmlspecialchars($authenticated_student['full_name']); ?></strong> (<?php echo htmlspecialchars($authenticated_student['matric_no']); ?>)
            </div>
            <span class="bg-slate-800 text-cyan-400 font-bold px-3 py-1 rounded-lg uppercase tracking-wide text-xs">
                Group <?php echo htmlspecialchars($authenticated_student['group_no']); ?>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div class="bg-white rounded-3xl p-6 shadow-xl">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Content Retrieval
            </h3>

            <form method="post" enctype="multipart/form-data">
                <label class="block text-xs text-slate-500 mb-2">File Name</label>
                <input type="text" name="file_name" value="<?php echo htmlspecialchars($input_file_name ? $input_file_name : ($_POST['file_name'] ?? '')); ?>" class="w-full border border-slate-200 rounded-xl p-3 text-sm mb-4" placeholder="Enter target descriptor file name" required>

                <label class="block text-xs text-slate-500 mb-2">Upload File Path</label>
                <input type="file" name="upload_file" class="w-full border border-slate-200 bg-slate-50 rounded-xl p-2.5 text-sm mb-4" required>

                <label class="block text-xs text-slate-500 mb-2">
                    Enter Text Content (maximum 500 words)
                </label>
                <textarea
                    id="tbr_content_area"
                    name="content"
                    rows="8"
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm mb-4"
                    placeholder="Example: This geodatabase report outlines a structured implementation strategy. Sustaining an active, positive mindset..."
                    required><?php echo htmlspecialchars($text_content ? $text_content : ($_POST['content'] ?? '')); ?></textarea>

                <button
                    type="submit"
                    name="analyze"
                    class="bg-gradient-to-r from-blue-500 to-cyan-500 text-white px-5 py-3 rounded-xl hover:opacity-90 transition w-full md:w-auto font-medium">
                    Analyze Theme
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-xl flex flex-col justify-between">
            <div>
                <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                    Classification Result
                </h3>
            </div>

            <div class="text-center my-auto py-6">
                <div class="w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mx-auto shadow-md">
                    <i class="fas fa-tags text-4xl text-white"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-800 mt-5 tracking-tight">
                    <?php
                    if (isset($_POST['analyze']) && !empty($uploaded_file_path) && !empty($text_content)) {
                        echo htmlspecialchars($classification);
                    } else {
                        echo "No Analysis";
                    }
                    ?>
                </h1>
            </div>
            <div></div>
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

        <div class="space-y-5">
            <div class="bg-white rounded-3xl p-6 shadow-xl">
                <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-4">
                    Retrieval Details
                </h3>
                <div class="text-sm space-y-2 text-slate-700">
                    <div><strong>Matric No:</strong> <?php echo htmlspecialchars($authenticated_student['matric_no']); ?></div>
                    <div><strong>Full Name:</strong> <?php echo htmlspecialchars($authenticated_student['full_name']); ?></div>
                    <div><strong>Group No:</strong> <?php echo htmlspecialchars($authenticated_student['group_no']); ?></div>
                    <div><strong>File Name:</strong> <?php echo !empty($input_file_name) ? htmlspecialchars($input_file_name) : '-'; ?></div>
                    <div><strong>File Path:</strong> <?php echo !empty($uploaded_file_path) ? htmlspecialchars($uploaded_file_path) : '-'; ?></div>
                    <div><strong>File Type:</strong> <?php echo !empty($uploaded_file_type) ? htmlspecialchars($uploaded_file_type) : '-'; ?></div>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 shadow-xl">
                <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-4">
                    Detected Themes
                </h3>
                <?php if (isset($_POST['analyze']) && !empty($uploaded_file_path) && !empty($text_content)): ?>
                    <div class='bg-blue-50/80 p-4 rounded-xl mb-4 border border-blue-100'>
                        <span class="text-xs uppercase font-bold tracking-wider text-blue-500 block mb-1">Target Category Matches:</span>
                        <h4 class='font-bold text-slate-950 text-base'>
                            <?php echo htmlspecialchars($classification); ?>
                        </h4>
                    </div>
                    <div class="mt-2">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Highlighted Source Vector:</span>
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm leading-relaxed text-slate-800 font-mono overflow-y-auto max-h-48 whitespace-pre-wrap">
                            <?php echo $highlighted_text; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class='text-sm text-slate-400 italic'>No themes detected yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-xl flex flex-col justify-between">
            <div>
                <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                    Theme Statistics
                </h3>

                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-blue-50 rounded-2xl p-4 border border-blue-100/50">
                        <p class="text-xs text-slate-500 font-medium">Words Analysed</p>
                        <h4 class="font-bold text-xl text-slate-900 mt-1">
                            <?php echo $words_analysed; ?>
                        </h4>
                    </div>

                    <div class="bg-indigo-50 rounded-2xl p-4 border border-indigo-100/50">
                        <p class="text-xs text-slate-500 font-medium">Classification</p>
                        <h4 class="font-bold text-slate-900 mt-1 text-base">
                            <?php echo (isset($_POST['analyze']) && !empty($uploaded_file_path)) ? htmlspecialchars($classification) : 'Keyword-Based'; ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-5 bg-white rounded-3xl p-6 shadow-xl">
        <form method="post" class="grid grid-cols-2 gap-4">
            <input type="hidden" name="save_file_name" value="<?php echo htmlspecialchars($input_file_name); ?>">
            <input type="hidden" name="save_file_path" value="<?php echo htmlspecialchars($uploaded_file_path); ?>">
            <input type="hidden" name="save_file_type" value="<?php echo htmlspecialchars($uploaded_file_type); ?>">
            <input type="hidden" name="save_extracted_text" value="<?php echo htmlspecialchars($text_content); ?>">
            <input type="hidden" name="save_classification" value="<?php echo htmlspecialchars($classification); ?>">
            <input type="hidden" name="save_words_analysed" value="<?php echo $words_analysed; ?>">

            <button type="submit" name="reset_content" class="bg-slate-200 text-slate-700 px-4 py-3 rounded-xl font-semibold hover:bg-slate-300 transition text-sm">
                Reset Content
            </button>
            
            <?php 
            // Assertion Logic validation: block capability if any metric parameters evaluate to empty strings
            $is_form_valid = (!empty($input_file_name) && !empty($uploaded_file_path) && !empty($uploaded_file_type) && !empty($text_content) && ($classification !== 'Keyword-Based'));
            ?>
            <button type="submit" name="save_content" <?php echo !$is_form_valid ? 'disabled' : ''; ?> class="bg-green-600 text-white px-4 py-3 rounded-xl font-semibold hover:bg-green-700 transition disabled:opacity-40 disabled:cursor-not-allowed text-sm shadow-sm">
                Save Content
            </button>
        </form>
        
        <?php if (!empty($success_msg)): ?>
            <div class="mt-4 p-3 bg-green-100 text-green-800 border border-green-200 rounded-xl text-sm text-center font-semibold">
                ✓ <?php echo $success_msg; ?>
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
                        <th class="p-4 border border-slate-700 font-semibold">Matric No</th>
                        <th class="p-4 border border-slate-700 font-semibold">Full Name</th>
                        <th class="p-4 border border-slate-700 font-semibold">Group No</th>
                        <th class="p-4 border border-slate-700 font-semibold">Content File Name</th>
                        <th class="p-4 border border-slate-700 font-semibold">Content File Path</th>
                        <th class="p-4 border border-slate-700 font-semibold">Content File Type</th>
                        <th class="p-4 border border-slate-700 font-semibold">TBR Theme Category</th>
                        <th class="p-4 border border-slate-700 font-semibold">Word Analyse</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700">
                    <?php
                    if ($conn) {
                        // Fully integrated SQL query structure pulling related student criteria alongside multimedia elements
                        $history_query = "SELECT mc.student_matric_no, st.full_name, st.group_no, mc.content_file_name, mc.content_file_path, mc.content_file_type, mc.tbr_theme_category, mc.word_analyse 
                                          FROM multimedia_content mc 
                                          LEFT JOIN student st ON mc.student_matric_no = st.student_matric_no 
                                          ORDER BY mc.content_id DESC";
                        $history_result = mysqli_query($conn, $history_query);
                        
                        if ($history_result && mysqli_num_rows($history_result) > 0) {
                            while ($row = mysqli_fetch_assoc($history_result)) {
                                echo "<tr class='hover:bg-slate-50 border-b border-slate-100 transition duration-150'>";
                                echo "<td class='p-4 border border-slate-100 font-mono text-xs font-bold text-slate-600'>" . htmlspecialchars($row['student_matric_no'] ?? '') . "</td>";
                                echo "<td class='p-4 border border-slate-100 font-medium text-slate-900'>" . htmlspecialchars($row['full_name'] ?? '-');
                                echo "<td class='p-4 border border-slate-100'><span class='px-2 py-0.5 bg-slate-100 text-slate-700 text-xs font-bold rounded'>" . htmlspecialchars($row['group_no'] ?? '-') . "</span></td>";
                                echo "<td class='p-4 border border-slate-100 font-medium text-slate-900'>" . htmlspecialchars($row['content_file_name'] ?? '-') . "</td>";
                                echo "<td class='p-4 text-xs font-mono text-slate-500'>";
                                if (!empty($row['content_file_path'])) {
                                    echo htmlspecialchars($row['content_file_path']) . " ";
                                    echo "<a href='".htmlspecialchars($row['content_file_path'])."' download class='inline-block ml-2 text-blue-500 hover:underline'><i class='fas fa-download'></i> Download</a>";
                                } else {
                                    echo "N/A";
                                }
                                echo "<td class='p-4 border border-slate-100 font-bold text-xs text-center text-slate-600'>" . htmlspecialchars($row['content_file_type'] ?? '') . "</td>";
                                echo "<td class='p-4 border border-slate-100 font-semibold text-blue-600'>" . htmlspecialchars($row['tbr_theme_category'] ?? '') . "</td>";
                                echo "<td class='p-4 border border-slate-100 font-mono font-medium text-slate-800 text-center'>" . htmlspecialchars($row['word_analyse'] ?? '0') . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='p-5 text-center text-slate-400 italic bg-slate-50/50'>No classification history records identified inside storage layers.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='p-5 text-center text-red-500 font-medium italic bg-red-50'>Database platform tracking module connection verification check failed.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
// Client side input protection listener monitoring dynamic textarea limits to enforce a hard maximum cap of 500 words
document.addEventListener('DOMContentLoaded', function() {
    const contentArea = document.getElementById('tbr_content_area');
    if (contentArea) {
        contentArea.addEventListener('input', function() {
            const words = this.value.trim().split(/\s+/);
            if (words.length > 500) {
                // Parse cleanly back down to structural constraints bounds
                const truncatedWords = words.slice(0, 500);
                this.value = truncatedWords.join(" ");
            }
        });
    }
});
</script>

</body>
</html>