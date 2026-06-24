<?php
include 'menu.php';
require_once 'db_conn.php'; // Integrate database connection

// Initialize variables for selected student
$selected_matric = isset($_POST['student_matric']) ? mysqli_real_escape_string($conn, $_POST['student_matric']) : '';
$entered_phone = isset($_POST['phone_no']) ? trim($_POST['phone_no']) : '';
$profile_image = '';
$student_name = '';
$error_message = '';

// --- BACKGROUND AJAX ENDPOINT FOR SAVING AND SYNCING HISTORICAL DATA ---
if (isset($_GET['action']) && $_GET['action'] === 'save_analysis') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!empty($input['student_matric_no']) && $conn_mmdb) {
        $matric = mysqli_real_escape_string($conn, $input['student_matric_no']);
        $eye = mysqli_real_escape_string($conn, $input['eye_position']);
        $mouth = mysqli_real_escape_string($conn, $input['mouth_position']);
        $eyebrow = mysqli_real_escape_string($conn, $input['eyebrow_position']);
        $landmarks = mysqli_real_escape_string($conn, $input['facial_landmarks']);
        $result_expr = mysqli_real_escape_string($conn, $input['cbr_expression_result']);
        $confidence = floatval($input['expression_confidence']);

        // Fetch absolute structural metrics from vstu view container to execute sync updates
        $vstu_query = "SELECT id, full_name, phone_no, life_motto, photoStu FROM vstu WHERE matric_no = '$matric'";
        $vstu_res = mysqli_query($conn_mmdb, $vstu_query);
        
        if ($vstu_res && mysqli_num_rows($vstu_res) > 0) {
            $vstu_row = mysqli_fetch_assoc($vstu_res);
            $student_id = intval($vstu_row['id']);
            $full_name = mysqli_real_escape_string($conn, $vstu_row['full_name']);
            $phone_no = mysqli_real_escape_string($conn, $vstu_row['phone_no']);
            $life_motto = mysqli_real_escape_string($conn, $vstu_row['life_motto']);
            $photoStu = mysqli_real_escape_string($conn, $vstu_row['photoStu']);

            // Verify entry existence inside target baseline table student (gr07 database)
            $check_stu = mysqli_query($conn, "SELECT student_matric_no FROM student WHERE student_matric_no = '$matric'");
            
            if (mysqli_num_rows($check_stu) > 0) {
                // If exists, execute standard updates targeting student info
                $student_sync_query = "UPDATE student SET 
                    student_name = '$full_name', 
                    phone_no = '$phone_no', 
                    life_motto = '$life_motto', 
                    profile_image_path = '$photoStu' 
                    WHERE student_matric_no = '$matric'";
            } else {
                // If missing completely, safely run structured insert queries
                $student_sync_query = "INSERT INTO student 
                    (student_matric_no, student_name, phone_no, life_motto, profile_image_path) 
                    VALUES ('$matric', '$full_name', '$phone_no', '$life_motto', '$photoStu')";
            }
            
            mysqli_query($conn, $student_sync_query);

            // Log parameters down to structural database layout table metrics history
            $insert_analysis = "INSERT INTO facial_expression_analysis 
                (student_id, student_matric_no, eye_position, mouth_position, eyebrow_position, facial_landmarks, cbr_expression_result, expression_confidence) 
                VALUES ($student_id, '$matric', '$eye', '$mouth', '$eyebrow', '$landmarks', '$result_expr', $confidence)";
            
            if (mysqli_query($conn, $insert_analysis)) {
                echo json_encode(['status' => 'success', 'message' => 'Student synchronized and history logged.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Student structural record missing from target database view tracking.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid processing payload or connection state missing fallback environments.']);
    }
    exit;
}

// Fetch explicit profile information from view vstu when filtering student retrievals
if (!empty($selected_matric) && !empty($entered_phone)) {
    if ($conn_mmdb) {
        $query = "SELECT full_name, phone_no, photoStu FROM vstu WHERE matric_no = '$selected_matric'";
        $result = mysqli_query($conn_mmdb, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if (trim($row['phone_no']) === $entered_phone) {
                $student_name = $row['full_name'];
                $profile_image = $row['photoStu'];
            } else {
                $error_message = "Phone number verification mismatch. Entry denied.";
            }
        } else {
            $error_message = "Student records missing inside verification view definitions.";
        }
    } else {
        // Fallback context: If failed to connect to live mmdb2026, grab profile from local student table as default fallback
        $query = "SELECT student_name, profile_image_path FROM student WHERE student_matric_no = '$selected_matric'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $student_name = $row['student_name'];
            $profile_image = $row['profile_image_path'];
        }
    }
}

// Populate structural select configurations depending on the connectivity layer state
if ($conn_mmdb) {
    $students_query = "SELECT matric_no, full_name, group_no FROM vstu ORDER BY full_name ASC";
    $students_result = mysqli_query($conn_mmdb, $students_query);
} else {
    $students_query = "SELECT student_matric_no, student_name FROM student ORDER BY student_name ASC";
    $students_result = mysqli_query($conn, $students_query);
}
?>

<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>

<main class="flex-1 p-6 bg-slate-100 min-h-screen">

    <div class="mb-8">
        <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-cyan-800 rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">
                        Content-Based Retrieval (CBR)
                    </h2>
                    <p class="text-cyan-200 text-sm mt-1">
                        Facial Expression Detection 
                    </p>
                </div>
                <div class="text-cyan-300 text-4xl">
                    <i class="fas fa-face-smile"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="mb-5 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl text-sm font-medium">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-2 gap-5">

        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-blue-100">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Student Retrieval
            </h3>

            <form method="POST" action="">
                <label class="block text-xs text-slate-500 mb-2">
                    Student Profile (Enter Matric Number)
                </label>

                <input type="text" id="student_matric_input" name="student_matric" value="<?php echo htmlspecialchars($selected_matric); ?>" required autocomplete="off" list="student_list" placeholder="Type Matric Number..." class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                <datalist id="student_list">
                    <?php
                    if ($students_result && mysqli_num_rows($students_result) > 0) {
                        while ($st = mysqli_fetch_assoc($students_result)) {
                            if ($conn_mmdb) {
                                $m_no = $st['matric_no'];
                                $label = htmlspecialchars($st['full_name']) . " (" . htmlspecialchars($st['matric_no']) . ") - " . htmlspecialchars($st['group_no']);
                            } else {
                                $m_no = $st['student_matric_no'];
                                $label = htmlspecialchars($st['student_matric_no']) . " - " . htmlspecialchars($st['student_name']);
                            }
                            echo "<option value='" . htmlspecialchars($m_no) . "'>$label</option>";
                        }
                    }
                    ?>
                </datalist>

                <div class="mt-4">
                    <label class="block text-xs text-slate-500 mb-2">
                        Verification: Enter Phone Number
                    </label>
                    <input type="text" name="phone_no" value="<?php echo htmlspecialchars($entered_phone); ?>" required placeholder="e.g. 0123456789" class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>

                <button type="submit" class="mt-5 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white text-sm font-medium px-5 py-3 rounded-xl transition-all duration-300">
                    Retrieve Image
                </button>
            </form>
        </div>

        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-cyan-100">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Expression Analysis
            </h3>

            <div class="text-center mt-4">
                <div id="expression-bg" class="w-24 h-24 rounded-full bg-gradient-to-r from-yellow-300 to-orange-300 flex items-center justify-center mx-auto shadow-lg">
                    <i id="expression-icon" class="fas fa-face-smile text-5xl text-white"></i>
                </div>

                <h1 id="expression-label" class="text-3xl font-bold text-slate-800 mt-5">
                    READY
                </h1>

                <p class="text-sm text-slate-500 mt-2">
                    Confidence : <span id="confidence-text">0%</span>
                </p>

                <div class="w-48 mx-auto mt-5">
                    <div class="h-2 bg-slate-200 rounded-full">
                        <div id="confidence-bar" class="h-2 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full w-[0%] transition-all duration-500"></div>
                    </div>
                </div>
                 
                <p id="api-status" class="text-xs text-blue-500 mt-3 italic">Select a student image to begin API processing...</p>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-2 gap-5 mt-5">

        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-blue-100">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Retrieved Image
            </h3>

            <div class="h-72 rounded-2xl bg-gradient-to-br from-slate-50 to-blue-50 border border-dashed border-blue-200 flex items-center justify-center overflow-hidden position-relative">
                <?php if (!empty($profile_image)): ?>
                    <img id="student-face-img" src="<?php echo htmlspecialchars($profile_image); ?>" alt="Student Profile" class="max-h-full max-w-full object-contain rounded-xl" crossOrigin="anonymous">
                <?php else: ?>
                    <div class="text-center">
                        <i class="fas fa-user-circle text-7xl text-slate-300 mb-4"></i>
                        <p class="text-sm text-slate-400">
                            Student Profile Image
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-cyan-100">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                Facial Features
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Eye Position</p>
                    <h4 id="feature-eye" class="text-sm font-semibold text-slate-800 mt-1">Pending</h4>
                </div>

                <div class="bg-cyan-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Mouth Position</p>
                    <h4 id="feature-mouth" class="text-sm font-semibold text-slate-800 mt-1">Pending</h4>
                </div>

                <div class="bg-indigo-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Eyebrow Position</p>
                    <h4 id="feature-eyebrow" class="text-sm font-semibold text-slate-800 mt-1">Pending</h4>
                </div>

                <div class="bg-sky-50 rounded-2xl p-4">
                    <p class="text-xs text-slate-500">Facial Landmarks</p>
                    <h4 id="feature-landmarks" class="text-sm font-semibold text-slate-800 mt-1">0 Points</h4>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-8 bg-white backdrop-blur-lg border border-white/50 rounded-3xl p-6 shadow-xl shadow-slate-200">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold">
                Students Facial Analysis Log History
            </h3>
            <span class="text-xs bg-blue-50 text-blue-600 px-3 py-1 rounded-full font-medium">Database Connected</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider bg-slate-50/50">
                        <th class="py-3 px-4 font-semibold">Matric Number</th>
                        <th class="py-3 px-4 font-semibold">Student Name</th>
                        <th class="py-3 px-4 font-semibold">Group No</th>
                        <th class="py-3 px-4 font-semibold text-center">Profile Image</th>
                        <th class="py-3 px-4 font-semibold">Expression Result</th>
                        <th class="py-3 px-4 font-semibold">Confidence</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700">
                    <?php
                    if ($conn) {
                        // Gather analytical logs stored inside target logging tables
                        $history_query = "SELECT s.student_matric_no, s.student_name, s.profile_image_path, 
                                                 f.cbr_expression_result, f.expression_confidence 
                                          FROM facial_expression_analysis f 
                                          JOIN student s ON f.student_matric_no = s.student_matric_no 
                                          ORDER BY f.analysis_id DESC";
                        
                        $history_result = mysqli_query($conn, $history_query);
                        
                        if ($history_result && mysqli_num_rows($history_result) > 0) {
                            while ($row = mysqli_fetch_assoc($history_result)) {
                                $history_matric = $row['student_matric_no'];
                                $group_display = "N/A";

                                // Cross-reference matching group number dynamically from remote vstu view
                                if ($conn_mmdb) {
                                    $grp_query = "SELECT group_no FROM vstu WHERE matric_no = '" . mysqli_real_escape_string($conn_mmdb, $history_matric) . "'";
                                    $grp_res = mysqli_query($conn_mmdb, $grp_query);
                                    if ($grp_res && mysqli_num_rows($grp_res) > 0) {
                                        $grp_row = mysqli_fetch_assoc($grp_res);
                                        $group_display = htmlspecialchars($grp_row['group_no']);
                                    }
                                }

                                // Setup custom styling colors based on detection values
                                $badge_class = "bg-slate-100 text-slate-600";
                                if (strtoupper($row['cbr_expression_result']) === "HAPPY") {
                                    $badge_class = "bg-amber-100 text-amber-700 font-semibold";
                                } elseif (in_array(strtoupper($row['cbr_expression_result']), ["SAD", "ANGRY"])) {
                                    $badge_class = "bg-indigo-100 text-indigo-700 font-semibold";
                                }
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors duration-200">
                                    <td class="py-3 px-4 font-medium text-slate-900"><?php echo htmlspecialchars($row['student_matric_no']); ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['student_name']); ?></td>
                                    <td class="py-3 px-4"><span class="bg-slate-100 px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600"><?php echo $group_display; ?></span></td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if (!empty($row['profile_image_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($row['profile_image_path']); ?>" class="w-10 h-10 object-cover rounded-full inline-block border border-slate-200 shadow-sm" alt="Thumbnail">
                                        <?php else: ?>
                                            <i class="fas fa-user-circle text-2xl text-slate-300"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs uppercase tracking-wide <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($row['cbr_expression_result']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold text-blue-600"><?php echo number_format($row['expression_confidence'] * 100, 0); ?>%</td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" class="py-8 text-center text-slate-400 italic">No cbr metrics history records available in log data.</td></tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6" class="py-8 text-center text-red-400 italic">Failed to securely query transactional historical records layers.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const imgElement = document.getElementById('student-face-img');
    const apiStatus = document.getElementById('api-status');
    const currentMatric = "<?php echo $selected_matric; ?>";
    const hasError = "<?php echo !empty($error_message) ? 'true' : 'false'; ?>";

    if (!imgElement || !currentMatric || hasError === 'true') return;

    try {
        apiStatus.innerText = "Loading face detection models...";
        
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL);

        apiStatus.innerText = "Analyzing facial structure features...";

        const detection = await faceapi.detectSingleFace(imgElement, new faceapi.TinyFaceDetectorOptions())
                                        .withFaceLandmarks()
                                        .withFaceExpressions();

        let eyePos = "Pending", mouthPos = "Pending", eyebrowPos = "Pending", landmarkPoints = "0 Points", rawLandmarksJSON = "{}";
        let finalExpression = "NORMAL", confidenceVal = 0.00;

        if (detection) {
            apiStatus.innerText = "Analysis complete!";

            const expressions = detection.expressions;
            finalExpression = Object.keys(expressions).reduce((a, b) => expressions[a] > expressions[b] ? a : b);
            confidenceVal = expressions[finalExpression];
            const confidencePct = Math.round(confidenceVal * 100);

            document.getElementById('expression-label').innerText = finalExpression.toUpperCase();
            document.getElementById('confidence-text').innerText = confidencePct + "%";
            document.getElementById('confidence-bar').style.width = confidencePct + "%";

            const bgDiv = document.getElementById('expression-bg');
            const iconEl = document.getElementById('expression-icon');
            if (finalExpression === 'happy') {
                bgDiv.className = "w-24 h-24 rounded-full bg-gradient-to-r from-yellow-300 to-orange-300 flex items-center justify-center mx-auto shadow-lg";
                iconEl.className = "fas fa-face-smile text-5xl text-white";
            } else if (finalExpression === 'sad' || finalExpression === 'angry') {
                bgDiv.className = "w-24 h-24 rounded-full bg-gradient-to-r from-purple-600 to-indigo-600 flex items-center justify-center mx-auto shadow-lg";
                iconEl.className = "fas fa-face-sad-tear text-5xl text-white";
            } else {
                bgDiv.className = "w-24 h-24 rounded-full bg-gradient-to-r from-teal-400 to-emerald-500 flex items-center justify-center mx-auto shadow-lg";
                iconEl.className = "fas fa-face-meh text-5xl text-white";
            }

            const landmarks = detection.landmarks;
            landmarkPoints = landmarks.positions.length + " Points";
            rawLandmarksJSON = JSON.stringify(landmarks.positions);
            document.getElementById('feature-landmarks').innerText = landmarkPoints;

            eyePos = "Tracked";
            mouthPos = "Detected";
            eyebrowPos = finalExpression === 'surprised' || finalExpression === 'happy' ? "Raised" : "Normal";
            
            document.getElementById('feature-mouth').innerText = mouthPos;
            document.getElementById('feature-eye').innerText = eyePos;
            document.getElementById('feature-eyebrow').innerText = eyebrowPos;

            // --- TRIGGER ASYNC BACKGROUND DATA SYNC & INSERTION ---
            apiStatus.innerText = "Saving analysis to historical database...";
            const logPayload = {
                student_matric_no: currentMatric,
                eye_position: eyePos,
                mouth_position: mouthPos,
                eyebrow_position: eyebrowPos,
                facial_landmarks: rawLandmarksJSON,
                cbr_expression_result: finalExpression.toUpperCase(),
                expression_confidence: (confidenceVal).toFixed(2)
            };

            fetch('cbr.php?action=save_analysis', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(logPayload)
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    apiStatus.innerText = "Analysis completed and records synced!";
                    // Reload the window after a short delay so the updated row instantly appears in the table
                    setTimeout(() => { window.location.reload(); }, 1200);
                } else {
                    console.error("Database sync failed:", data.message);
                    apiStatus.innerText = "Analysis finished (Data log sync failed).";
                }
            })
            .catch(error => {
                console.error("Network error during logging:", error);
                apiStatus.innerText = "Analysis finished (Network sync error).";
            });

        } else {
            apiStatus.innerText = "Analysis Error: Face elements could not be accurately calculated.";
            document.getElementById('expression-label').innerText = "ERROR";
            document.getElementById('confidence-text').innerText = "0%";
        }

    } catch (err) {
        console.error(err);
        apiStatus.innerText = "Analysis pipeline failed via engine errors.";
    }
});
</script>

</body>
</html>