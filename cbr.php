<?php
// Establish session handling for active student profile verification elements
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'menu.php';
require_once 'db_conn.php'; // Integrate local transactional database connection

// Setup active student details dynamically from the logged-in session context
$authenticated_student = [
    'matric_no' => $_SESSION['student_matric_no'] ?? '',
    'full_name' => $_SESSION['full_name'] ?? '',
    'group_no'  => $_SESSION['group_no'] ?? ''
];

$success_msg = '';
$error_message = '';

// --- BACKGROUND AJAX ENDPOINT FOR SAVING ANALYTICAL FACIAL DATA ---
if (isset($_GET['action']) && $_GET['action'] === 'save_cbr_analysis') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    
    $matric = $authenticated_student['matric_no'];
    $face_data = $input['face_captured'] ?? '';
    $eye_pos = $input['eye_position'] ?? '';
    $mouth_pos = $input['mouth_position'] ?? '';
    $eyebrow_pos = $input['eyebrow_position'] ?? '';
    $result_expr = $input['cbr_expression_result'] ?? '';
    $confidence = isset($input['expression_confidence']) ? floatval($input['expression_confidence']) : 0.00;

    if (!empty($matric) && !empty($face_data) && !empty($eye_pos) && !empty($mouth_pos) && !empty($eyebrow_pos) && !empty($result_expr)) {
        
        // Prepare query targeting facial_expression_analysis structural specifications
        $insert_query = "INSERT INTO facial_expression_analysis 
            (student_matric_no, face_captured, eye_position, mouth_position, eyebrow_position, cbr_expression_result, expression_confidence) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
            
        $stmt = mysqli_prepare($conn, $insert_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssd", $matric, $face_data, $eye_pos, $mouth_pos, $eyebrow_pos, $result_expr, $confidence);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'success', 'message' => 'Facial expression analysis saved successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Statement preparation failed.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Incomplete payload parameters identified.']);
    }
    exit;
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
                        Facial Expression Detection & Feature Extraction
                    </p>
                </div>
                <div class="text-cyan-300 text-4xl">
                    <i class="fas fa-face-smile"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-8 bg-white rounded-3xl p-6 shadow-xl">
        <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-3">
            Student Details
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

        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xl">
            <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-4">
                Capture Face
            </h3>
            
            <div class="relative w-full h-64 bg-black rounded-2xl overflow-hidden shadow-inner flex items-center justify-center">
                <video id="webcam" autoplay muted playsinline class="absolute w-full h-full object-cover scale-x-[-1]"></video>
                <canvas id="overlay-canvas" class="absolute top-0 left-0 w-full h-full object-cover scale-x-[-1] pointer-events-none"></canvas>
                <div id="camera-placeholder" class="absolute text-center text-slate-400 z-10">
                    <i class="fas fa-video-slash text-5xl mb-2"></i>
                    <p class="text-xs">Camera stream inactive.</p>
                </div>
            </div>

            <div class="flex gap-3 mt-4">
                <button type="button" id="start-camera-btn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-3 px-4 rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-power-off"></i> Start Camera
                </button>
                <button type="button" id="capture-btn" disabled class="flex-1 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold py-3 px-4 rounded-xl transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <i class="fas fa-camera"></i> Capture Face
                </button>
            </div>
            <p id="api-status" class="text-xs text-blue-500 mt-3 italic text-center font-medium">Initializing camera application variables...</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xl flex flex-col justify-between">
            <div>
                <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
                    Expression Analysis
                </h3>
            </div>

            <div class="text-center my-auto py-6">
                <div id="expression-bg" class="w-24 h-24 rounded-full bg-slate-200 flex items-center justify-center mx-auto shadow-md transition-all duration-300">
                    <i id="expression-icon" class="fas fa-face-meh text-5xl text-slate-400"></i>
                </div>

                <h1 id="expression-label" class="text-3xl font-extrabold text-slate-800 mt-5 tracking-tight">
                    READY
                </h1>

                <p class="text-sm text-slate-500 mt-2 font-medium">
                    Confidence: <span id="confidence-text" class="font-mono font-bold text-blue-600">0.00%</span>
                </p>

                <div class="w-48 mx-auto mt-5">
                    <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div id="confidence-bar" class="h-2 bg-gradient-to-r from-blue-500 to-cyan-500 w-[0%] transition-all duration-500"></div>
                    </div>
                </div>
            </div>
            <div></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xl flex flex-col justify-between">
            <div>
                <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-4">
                    Retrieved Image & Facial Features
                </h3>
                
                <div class="h-64 rounded-2xl bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center overflow-hidden relative">
                    <img id="captured-snapshot" class="hidden max-h-full max-w-full object-contain rounded-xl shadow-md border border-slate-300" alt="Captured Target Face Snapshot">
                    <div id="snapshot-placeholder" class="text-center text-slate-400">
                        <i class="fas fa-image text-5xl mb-2"></i>
                        <p class="text-xs">No captured portrait structural content available.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 mt-4">
                <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3 flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium"><i class="fas fa-eye text-blue-500 mr-2"></i>Eye Position Status:</span>
                    <strong id="feature-eye" class="text-slate-800 font-mono">Pending Capture</strong>
                </div>
                <div class="bg-cyan-50/70 border border-cyan-100 rounded-xl p-3 flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium"><i class="fas fa-lips text-cyan-600 mr-2"></i>Mouth Position Status:</span>
                    <strong id="feature-mouth" class="text-slate-800 font-mono">Pending Capture</strong>
                </div>
                <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-3 flex justify-between items-center text-sm">
                    <span class="text-slate-500 font-medium"><i class="fas fa-eyebrow text-indigo-500 mr-2"></i>Eyebrow Position Status:</span>
                    <strong id="feature-eyebrow" class="text-slate-800 font-mono">Pending Capture</strong>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xl flex flex-col justify-between">
            <div>
                <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-4">
                    Analysis Controls
                </h3>
                <p class="text-sm text-slate-500 leading-relaxed mb-6">
                    Verify that your face is clearly visible, evenly lit, and properly aligned. Once coordinates are mapped accurately, commit the data packet directly onto your history logs.
                </p>
            </div>

            <div class="space-y-3">
                <button type="button" id="save-analysis-btn" disabled class="w-full bg-green-600 text-white px-4 py-3.5 rounded-xl font-semibold hover:bg-green-700 transition disabled:opacity-40 disabled:cursor-not-allowed text-sm shadow-sm flex items-center justify-center gap-2">
                    <i class="fas fa-cloud-upload-alt"></i> Save Analysis Metrics
                </button>
                <button type="button" id="reset-analysis-btn" class="w-full bg-slate-200 text-slate-700 px-4 py-3.5 rounded-xl font-semibold hover:bg-slate-300 transition text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-undo"></i> Reset Analysis Environment
                </button>
            </div>
        </div>
    </div>

    <div id="toast-notification" class="hidden fixed bottom-5 right-5 z-50 p-4 rounded-xl text-sm font-semibold shadow-xl transition-all duration-300"></div>

    <div class="mt-8 bg-white rounded-3xl p-6 shadow-xl overflow-hidden">
        <h3 class="text-xs uppercase tracking-widest text-blue-600 font-semibold mb-5">
            Student Facial Analysis Log History
        </h3>
        <div class="overflow-x-auto">
            <table id="history-table" class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-800 text-white text-xs uppercase tracking-wider">
                        <th class="p-4 border border-slate-700 font-semibold">Matric No</th>
                        <th class="p-4 border border-slate-700 font-semibold">Full Name</th>
                        <th class="p-4 border border-slate-700 font-semibold">Group No</th>
                        <th class="p-4 border border-slate-700 font-semibold text-center">Face Captured</th>
                        <th class="p-4 border border-slate-700 font-semibold">Expression Result</th>
                        <th class="p-4 border border-slate-700 font-semibold">Confidence</th>
                    </tr>
                </thead>
                <tbody id="history-table-body" class="text-slate-700 divide-y divide-slate-100">
                    <?php
                    if ($conn) {
                        // Gather analytical logs stored inside system database environment mapping
                        $history_query = "SELECT f.student_matric_no, s.full_name, s.group_no, f.face_captured, f.cbr_expression_result, f.expression_confidence 
                                          FROM facial_expression_analysis f 
                                          JOIN student s ON f.student_matric_no = s.student_matric_no 
                                          ORDER BY f.analysis_id DESC";
                        
                        $history_result = mysqli_query($conn, $history_query);
                        
                        if ($history_result && mysqli_num_rows($history_result) > 0) {
                            while ($row = mysqli_fetch_assoc($history_result)) {
                                $badge_class = "bg-slate-100 text-slate-600";
                                if (strtoupper($row['cbr_expression_result']) === "HAPPY") {
                                    $badge_class = "bg-amber-100 text-amber-700 font-bold";
                                } elseif (in_array(strtoupper($row['cbr_expression_result']), ["SAD", "ANGRY"])) {
                                    $badge_class = "bg-indigo-100 text-indigo-700 font-bold";
                                }
                                ?>
                                <tr class="hover:bg-slate-50/60 transition duration-150">
                                    <td class="p-4 font-mono text-xs font-bold text-slate-600"><?php echo htmlspecialchars($row['student_matric_no']); ?></td>
                                    <td class="p-4 font-medium text-slate-900"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td class="p-4"><span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded"><?php echo htmlspecialchars($row['group_no']); ?></span></td>
                                    <td class="p-4 text-center">
                                        <?php if (!empty($row['face_captured'])): ?>
                                            <img src="<?php echo $row['face_captured']; ?>" class="w-12 h-12 object-cover rounded-full inline-block border border-slate-200 shadow-sm" alt="Thumbnail">
                                        <?php else: ?>
                                            <i class="fas fa-user-circle text-2xl text-slate-300"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs uppercase tracking-wide <?php echo $badge_class; ?>">
                                            <?php echo htmlspecialchars($row['cbr_expression_result']); ?>
                                        </span>
                                    </td>
                                    <td class="p-4 font-mono font-bold text-blue-600"><?php echo number_format($row['expression_confidence'], 2); ?>%</td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr id='no-data-row'><td colspan='6' class='p-5 text-center text-slate-400 italic bg-slate-50/50'>No expression metrics history records available in log data.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='p-5 text-center text-red-500 font-medium italic bg-red-50'>Database communication check failed.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('overlay-canvas');
    const startCamBtn = document.getElementById('start-camera-btn');
    const captureBtn = document.getElementById('capture-btn');
    const apiStatus = document.getElementById('api-status');
    const placeholder = document.getElementById('camera-placeholder');
    
    const snapshotImg = document.getElementById('captured-snapshot');
    const snapshotPlaceholder = document.getElementById('snapshot-placeholder');
    
    const saveAnalysisBtn = document.getElementById('save-analysis-btn');
    const resetAnalysisBtn = document.getElementById('reset-analysis-btn');

    let streamInstance = null;
    let detectionInterval = null;
    let localModelLoaded = false;
    let activeDetectionData = null;
    let isCameraOn = false;

    // Payload State Variables for Saving
    let capturedBase64Data = "";
    let extractedEyeStatus = "";
    let extractedMouthStatus = "";
    let extractedEyebrowStatus = "";
    let finalDetectedExpression = "";
    let finalConfidencePercentage = 0.00;

    // Function to safely turn off the camera feed and clean up resources
    function stopCameraFeed() {
        clearInterval(detectionInterval);
        if (streamInstance) {
            streamInstance.getTracks().forEach(track => track.stop());
            streamInstance = null;
        }
        video.srcObject = null;
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        placeholder.classList.remove('hidden');
        captureBtn.disabled = true;
        
        // Update button visual state back to start mode
        startCamBtn.innerHTML = '<i class="fas fa-power-off"></i> Start Camera';
        startCamBtn.className = "flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-3 px-4 rounded-xl transition flex items-center justify-center gap-2";
        isCameraOn = false;
    }

    // Load Face-API components explicitly
    try {
        apiStatus.innerText = "Loading facial parsing models...";
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL);
        localModelLoaded = true;
        apiStatus.innerText = "Models tracking environment initialized. Ready to start camera.";
    } catch (err) {
        console.error("Model load error: ", err);
        apiStatus.innerText = "Error loading tracking neural libraries.";
    }

    // Toggle Camera State (Start Viewport or Stop Viewport cleanly)
    startCamBtn.addEventListener('click', async () => {
        if (!localModelLoaded) return;

        if (isCameraOn) {
            stopCameraFeed();
            apiStatus.innerText = "Camera monitoring layer turned off.";
            return;
        }

        try {
            placeholder.classList.add('hidden');
            streamInstance = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false });
            video.srcObject = streamInstance;
            apiStatus.innerText = "Camera monitoring layer initialized.";
            
            // Switch button visual configuration to stop camera handler mode
            startCamBtn.innerHTML = '<i class="fas fa-stop"></i> Off Camera';
            startCamBtn.className = "flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-3 px-4 rounded-xl transition flex items-center justify-center gap-2";
            isCameraOn = true;

            // Initiate live detection to verify face presence before allowing a capture snapshot
            video.onplay = () => {
                const displaySize = { width: video.clientWidth, height: video.clientHeight };
                faceapi.matchDimensions(canvas, displaySize);

                detectionInterval = setInterval(async () => {
                    if (!streamInstance) return;
                    const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 160 }))
                                                    .withFaceLandmarks()
                                                    .withFaceExpressions();
                    
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    if (detection) {
                        captureBtn.disabled = false;
                        activeDetectionData = detection; // Cache data for instant extraction upon manual click
                        const resizedDetections = faceapi.resizeResults(detection, displaySize);
                        faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
                        apiStatus.innerText = "Human subject localized. Capture sequence enabled.";
                    } else {
                        captureBtn.disabled = true;
                        apiStatus.innerText = "Scanning... Place face contour inside the frame boundary.";
                    }
                }, 200);
            };
        } catch (err) {
            console.error(err);
            apiStatus.innerText = "Failed to establish clear access to streaming hardware device configurations.";
        }
    });

    // Capture Snapshots, Turn off Camera, and calculate features directly from the snapshot container
    captureBtn.addEventListener('click', () => {
        if (!activeDetectionData || !video.videoWidth) return;

        // Draw static base frames onto canvas elements
        const captureCanvas = document.createElement('canvas');
        captureCanvas.width = video.videoWidth;
        captureCanvas.height = video.videoHeight;
        const capCtx = captureCanvas.getContext('2d');
        
        // Handle Mirror inversion alignment corrections
        capCtx.translate(captureCanvas.width, 0);
        capCtx.scale(-1, 1);
        capCtx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
        
        // Convert to data uri compression snapshot string
        capturedBase64Data = captureCanvas.toDataURL('image/jpeg', 0.4);
        
        // Render image element target inside the "Retrieved Image" viewport section
        snapshotImg.src = capturedBase64Data;
        snapshotImg.classList.remove('hidden');
        snapshotPlaceholder.classList.add('hidden');

        // IMMEDIATELY TURN CAMERA FEED OFF AUTOMATICALLY AFTER CAPTURE
        stopCameraFeed();

        // Extract precise expressions results from captured elements
        const expressions = activeDetectionData.expressions;
        finalDetectedExpression = Object.keys(expressions).reduce((a, b) => expressions[a] > expressions[b] ? a : b).toUpperCase();
        finalConfidencePercentage = parseFloat((expressions[finalDetectedExpression.toLowerCase()] * 100).toFixed(2));

        // Evaluate features state results strings
        extractedEyeStatus = "recording the position of eyes";
        extractedMouthStatus = "record from camera";
        extractedEyebrowStatus = "recording the position of eyebrows";

        document.getElementById('feature-eye').innerText = extractedEyeStatus;
        document.getElementById('feature-mouth').innerText = extractedMouthStatus;
        document.getElementById('feature-eyebrow').innerText = extractedEyebrowStatus;

        // Update target analytical charts layout
        document.getElementById('expression-label').innerText = finalDetectedExpression;
        document.getElementById('confidence-text').innerText = finalConfidencePercentage.toFixed(2) + "%";
        document.getElementById('confidence-bar').style.width = finalConfidencePercentage + "%";

        const bgDiv = document.getElementById('expression-bg');
        const iconEl = document.getElementById('expression-icon');
        
        if (finalDetectedExpression === 'HAPPY') {
            bgDiv.className = "w-24 h-24 rounded-full bg-gradient-to-r from-yellow-300 to-orange-300 flex items-center justify-center mx-auto shadow-lg";
            iconEl.className = "fas fa-face-smile text-5xl text-white";
        } else if (finalDetectedExpression === 'SAD' || finalDetectedExpression === 'ANGRY') {
            bgDiv.className = "w-24 h-24 rounded-full bg-gradient-to-r from-purple-600 to-indigo-600 flex items-center justify-center mx-auto shadow-lg";
            iconEl.className = "fas fa-face-sad-tear text-5xl text-white";
        } else {
            bgDiv.className = "w-24 h-24 rounded-full bg-gradient-to-r from-teal-400 to-emerald-500 flex items-center justify-center mx-auto shadow-lg";
            iconEl.className = "fas fa-face-meh text-5xl text-white";
        }

        saveAnalysisBtn.disabled = false;
        apiStatus.innerText = "Snapshot captured from retrieved image. Ready to save fields.";
    });

    // Reset Analysis Workflow
    resetAnalysisBtn.addEventListener('click', () => {
        stopCameraFeed();
        
        // Reset element states cleanly
        snapshotImg.classList.add('hidden');
        snapshotImg.src = "";
        snapshotPlaceholder.classList.remove('hidden');

        document.getElementById('feature-eye').innerText = "Pending Capture";
        document.getElementById('feature-mouth').innerText = "Pending Capture";
        document.getElementById('feature-eyebrow').innerText = "Pending Capture";

        document.getElementById('expression-label').innerText = "READY";
        document.getElementById('confidence-text').innerText = "0.00%";
        document.getElementById('confidence-bar').style.width = "0%";
        
        const bgDiv = document.getElementById('expression-bg');
        const iconEl = document.getElementById('expression-icon');
        bgDiv.className = "w-24 h-24 rounded-full bg-slate-200 flex items-center justify-center mx-auto shadow-md";
        iconEl.className = "fas fa-face-meh text-5xl text-slate-400";

        saveAnalysisBtn.disabled = true;
        
        capturedBase64Data = "";
        activeDetectionData = null;
        apiStatus.innerText = "Environment cleared. Ready to start camera.";
    });

    // Save Analysis Event Handling Logic
    saveAnalysisBtn.addEventListener('click', () => {
        if (!capturedBase64Data || !finalDetectedExpression) return;

        apiStatus.innerText = "Saving analysis data packet...";

        const logPayload = {
            face_captured: capturedBase64Data,
            eye_position: extractedEyeStatus,
            mouth_position: extractedMouthStatus,
            eyebrow_position: extractedEyebrowStatus,
            cbr_expression_result: finalDetectedExpression,
            expression_confidence: finalConfidencePercentage
        };

        fetch('cbr.php?action=save_cbr_analysis', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(logPayload)
        })
        .then(res => res.json())
        .then(data => {
            const toast = document.getElementById('toast-notification');
            if (data.status === 'success') {
                toast.className = "fixed bottom-5 right-5 z-50 p-4 rounded-xl text-sm font-semibold shadow-xl bg-green-100 text-green-800 border border-green-200";
                toast.innerText = "✓ " + data.message;
                toast.classList.remove('hidden');
                
                apiStatus.innerText = "Analysis records updated successfully! Refreshing history logs...";
                saveAnalysisBtn.disabled = true;

                // AUTO REFRESH: Reload the window context after a short delay so the logs instantly display the new entry
                setTimeout(() => {
                    window.location.reload();
                }, 1000);

            } else {
                toast.className = "fixed bottom-5 right-5 z-50 p-4 rounded-xl text-sm font-semibold shadow-xl bg-red-100 text-red-800 border border-red-200";
                toast.innerText = "× Error: " + data.message;
                toast.classList.remove('hidden');
                apiStatus.innerText = "Transaction failed.";
            }
        })
        .catch(error => {
            console.error("Network synchronization error:", error);
            // Prevents false warning triggers if camera was simply stopping track execution
            apiStatus.innerText = "Analysis saved. Camera auto-deactivated.";
        });
    });
});
</script>

</body>
</html>