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
        $vstu_query = "SELECT full_name, phone_no, life_motto, photoStu FROM vstu WHERE matric_no = '$matric'";
        $vstu_res = mysqli_query($conn_mmdb, $vstu_query);
        
        if ($vstu_res && mysqli_num_rows($vstu_res) > 0) {
            $vstu_row = mysqli_fetch_assoc($vstu_res);
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
                (student_matric_no, eye_position, mouth_position, eyebrow_position, facial_landmarks, cbr_expression_result, expression_confidence) 
                VALUES ('$matric', '$eye', '$mouth', '$eyebrow', '$landmarks', '$result_expr', $confidence)";
            
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
                    Student Profile
                </label>

                <select id="student_matric_select" name="student_matric" required class="w-full bg-white border border-slate-200 rounded-xl p-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <option value="">Select Student</option>
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
                            $selected = ($m_no == $selected_matric) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($m_no) . "' $selected>$label</option>";
                        }
                    }
                    ?>
                </select>

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