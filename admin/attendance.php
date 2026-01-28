<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .nav {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 2px solid #dee2e6;
        }
        .nav a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1em;
        }
        .nav a:hover {
            text-decoration: underline;
        }
        .content {
            padding: 40px;
        }
        .section-filter {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .section-filter h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .filter-group {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        .filter-item {
            flex: 1;
            min-width: 200px;
        }
        .filter-item label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .filter-item select {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1em;
            background: white;
        }
        .filter-item select:focus {
            outline: none;
            border-color: #667eea;
        }
        .section-info {
            background: #d4edda;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .section-info strong {
            color: #155724;
        }
        .video-container {
            position: relative;
            max-width: 640px;
            margin: 0 auto 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: #000;
        }
        #video {
            width: 100%;
            height: auto;
            display: block;
            transform: scaleX(-1); /* Mirror the video */
        }
        canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        .btn {
            padding: 15px 40px;
            font-size: 1.1em;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            margin: 10px 5px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .loading {
            text-align: center;
            padding: 20px;
            color: #667eea;
        }
        .face-match-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .time-mode {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .time-mode h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .time-mode-buttons {
            display: flex;
            gap: 10px;
        }
        .time-mode-btn {
            flex: 1;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }
        .time-mode-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }
        .time-mode-btn:hover {
            border-color: #667eea;
        }
        .debug-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            color: #666;
            margin-top: 10px;
            max-height: 100px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Intelligent Attendance Monitoring System</h1>
            <p>Tinajeros National High School</p>
        </div>

        <div class="nav">
            <a href="index.html">← Back to Dashboard</a>
        </div>

        <div class="content">
            <div class="loading" id="attendanceLoading">Loading face detection models...</div>
            
            <div id="attendanceForm" style="display:none;">
                <!-- Section Filter -->
                <div class="section-filter">
                    <h3>🎓 Select Section</h3>
                    <div class="filter-group">
                        <div class="filter-item">
                            <label for="sectionFilter">Section:</label>
                            <select id="sectionFilter" onchange="onSectionChange()">
                                <option value="">-- Select a Section --</option>
                            </select>
                        </div>
                        <button class="btn btn-success" onclick="loadSections()">🔄 Refresh Sections</button>
                    </div>
                </div>

                <!-- Section Info -->
                <div id="sectionInfo" style="display:none;" class="section-info">
                    <strong>Active Section:</strong> <span id="activeSectionName"></span><br>
                    <strong>Students in Section:</strong> <span id="studentCount">0</span>
                </div>

                <!-- Time Mode Selection -->
                <div class="time-mode">
                    <h3>⏰ Select Time Mode</h3>
                    <div class="time-mode-buttons">
                        <button class="time-mode-btn active" id="timeInBtn" onclick="setTimeMode(1)">
                            🔵 Time In
                        </button>
                        <button class="time-mode-btn" id="timeOutBtn" onclick="setTimeMode(2)">
                            🔴 Time Out
                        </button>
                    </div>
                </div>
                <div id="attendanceStatus"></div>
                
                <!-- Debug Info -->
                <div class="debug-info" id="debugInfo" hidden>
                    Debug info will appear here...
                </div>
                
                <div class="video-container">
                    <video id="video" autoplay muted playsinline></video>
                    <canvas id="canvas"></canvas>
                </div>

                <div class="face-match-info">
                    <strong>Instructions:</strong> 
                    <ol style="margin-left: 20px; margin-top: 10px;">
                        <li>Select a section from the dropdown above</li>
                        <li>Choose Time In or Time Out mode</li>
                        <li>Click "Start Recognition" to begin</li>
                        <li>Position your face in front of the camera</li>
                    </ol>
                </div>

                <div style="text-align: center;">
                    <button class="btn btn-primary" id="startBtn" onclick="startAttendance()" disabled>▶️ Start Recognition</button>
                    <button class="btn btn-danger" onclick="stopAttendance()">⏸️ Stop</button>
                    <!-- <button class="btn" onclick="testCanvasDrawing()">🔧 Test Canvas</button> -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
    <script>
        let video, canvas;
        let isAttendanceRunning = false;
        let filteredFaceDescriptors = [];
        let modelsLoaded = false;
        let selectedSection = '';
        let timeMode = 1;
        let sections = [];
        const MATCH_THRESHOLD = 0.6;
        let recognitionInterval = null;
        
        // Debug logging
        function debugLog(message) {
            const debugElement = document.getElementById('debugInfo');
            const timestamp = new Date().toLocaleTimeString();
            debugElement.innerHTML = `[${timestamp}] ${message}<br>` + debugElement.innerHTML;
            console.log(message);
        }

        async function loadModels() {
            if (modelsLoaded) return;
            try {
                debugLog('Loading face detection models...');
                const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                modelsLoaded = true;
                debugLog('✅ Models loaded successfully');
                showStatus('success', 'Face detection models loaded successfully');
            } catch (error) {
                debugLog(`❌ Error loading models: ${error.message}`);
                throw error;
            }
        }

        async function loadSections() {
            try {
                debugLog('Loading sections...');
                const response = await fetch('get_sections.php');
                sections = await response.json();
                
                const sectionFilter = document.getElementById('sectionFilter');
                sectionFilter.innerHTML = '<option value="">-- Select a Section --</option>';
                
                sections.forEach(section => {
                    sectionFilter.innerHTML += `<option value="${section.section_id}">${section.section_name}</option>`;
                });
                
                debugLog(`✅ Loaded ${sections.length} sections`);
            } catch (err) {
                debugLog(`❌ Error loading sections: ${err.message}`);
                showStatus('error', 'Failed to load sections');
            }
        }

        async function loadFacesForSection(sectionId) {
            try {
                debugLog(`Loading faces for section ${sectionId}...`);
                const response = await fetch(`get_students.php?section_id=${sectionId}`);
                const text = await response.text();
                const data = JSON.parse(text);
                
                filteredFaceDescriptors = data.map(student => {
                    try {
                        const descriptor = JSON.parse(student.face_descriptor);
                        return {
                            id: student.id,
                            student_id: student.student_id,
                            name: student.student_name,
                            section_id: student.section_id,
                            descriptor: new Float32Array(descriptor)
                        };
                    } catch (err) {
                        debugLog(`⚠️ Error parsing descriptor for ${student.student_name}`);
                        return null;
                    }
                }).filter(s => s !== null);
                
                debugLog(`✅ Loaded ${filteredFaceDescriptors.length} faces`);
                return filteredFaceDescriptors.length;
            } catch (err) {
                debugLog(`❌ Error loading faces: ${err.message}`);
                throw err;
            }
        }

        async function onSectionChange() {
            selectedSection = document.getElementById('sectionFilter').value;
            
            if (!selectedSection) {
                document.getElementById('sectionInfo').style.display = 'none';
                document.getElementById('startBtn').disabled = true;
                filteredFaceDescriptors = [];
                return;
            }

            showStatus('info', 'Loading faces for selected section...');
            debugLog(`Section changed to: ${selectedSection}`);
            
            try {
                const faceCount = await loadFacesForSection(selectedSection);
                
                const section = sections.find(s => s.section_id === selectedSection);
                const sectionName = section ? section.section_name : selectedSection;

                document.getElementById('activeSectionName').textContent = sectionName;
                document.getElementById('studentCount').textContent = faceCount;
                document.getElementById('sectionInfo').style.display = 'block';
                document.getElementById('startBtn').disabled = false;

                if (faceCount === 0) {
                    showStatus('error', `No students found in section ${sectionName}`);
                } else {
                    showStatus('success', `✅ Ready to scan ${faceCount} students in ${sectionName}`);
                }
            } catch (err) {
                showStatus('error', 'Failed to load student faces for this section');
                document.getElementById('startBtn').disabled = true;
            }
        }

        function setTimeMode(mode) {
            timeMode = mode;
            document.getElementById('timeInBtn').classList.remove('active');
            document.getElementById('timeOutBtn').classList.remove('active');
            
            if (mode === 1) {
                document.getElementById('timeInBtn').classList.add('active');
                showStatus('info', 'Mode: Time In - Students will be marked as entering');
            } else {
                document.getElementById('timeOutBtn').classList.add('active');
                showStatus('info', 'Mode: Time Out - Students will be marked as leaving');
            }
        }

        async function initCamera() {
            document.getElementById('attendanceLoading').style.display = 'block';
            document.getElementById('attendanceForm').style.display = 'none';
            
            try {
                await loadModels();
                await loadSections();
                
                video = document.getElementById('video');
                canvas = document.getElementById('canvas');
                const ctx = canvas.getContext('2d');

                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                        facingMode: 'user'
                    } 
                });
                video.srcObject = stream;
                
                video.addEventListener('loadeddata', () => {
                    debugLog(`Video dimensions: ${video.videoWidth}x${video.videoHeight}`);
                    
                    // Set canvas dimensions to match video
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    
                    // Test canvas drawing
                    // testCanvasDrawing();
                    
                    document.getElementById('attendanceLoading').style.display = 'none';
                    document.getElementById('attendanceForm').style.display = 'block';
                    showStatus('success', 'Camera initialized successfully');
                });

            } catch (err) {
                document.getElementById('attendanceLoading').style.display = 'none';
                debugLog(`❌ Camera initialization error: ${err.message}`);
                showStatus('error', 'Error: ' + err.message);
            }
        }

        function testCanvasDrawing() {
            const ctx = canvas.getContext('2d');
            
            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Draw a test rectangle
            ctx.fillStyle = 'red';
            ctx.fillRect(50, 50, 100, 50);
            
            // Draw test text
            ctx.fillStyle = 'white';
            ctx.font = '20px Arial';
            // ctx.fillText('Canvas Test', 60, 85);
            
            // debugLog('Canvas test drawing complete');
        }

        async function startAttendance() {
            if (!selectedSection) {
                showStatus('error', 'Please select a section first!');
                return;
            }
            
            if (filteredFaceDescriptors.length === 0) {
                showStatus('error', 'No students in selected section!');
                return;
            }
            
            isAttendanceRunning = true;
            const mode = timeMode === 1 ? 'Time In' : 'Time Out';
            showStatus('info', `👀 Looking for faces... Mode: ${mode}`);
            debugLog('Starting face recognition...');
            
            // Clear any existing interval
            if (recognitionInterval) {
                clearInterval(recognitionInterval);
            }
            
            // Start recognition loop
            recognitionInterval = setInterval(recognizeFaces, 100); // Process every 100ms
        }

        function stopAttendance() {
            isAttendanceRunning = false;
            if (recognitionInterval) {
                clearInterval(recognitionInterval);
                recognitionInterval = null;
            }
            
            // Clear canvas
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            showStatus('info', 'Recognition stopped');
            debugLog('Recognition stopped');
        }

        async function recognizeFaces() {
    if (!isAttendanceRunning || !modelsLoaded) return;
    
    const ctx = canvas.getContext('2d');
    
    try {
        // Clear previous drawings
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        const detections = await faceapi.detectAllFaces(video)
            .withFaceLandmarks()
            .withFaceDescriptors();
        
        debugLog(`Detected ${detections.length} face(s)`);
        
        if (detections.length > 0) {
            // Resize detections to match canvas size
            const resizedDetections = faceapi.resizeResults(detections, {
                width: canvas.width,
                height: canvas.height
            });

            for (let i = 0; i < resizedDetections.length; i++) {
                const detection = resizedDetections[i];
                let bestMatch = null;
                let bestDistance = Infinity;

                // Find the best match
                for (const registered of filteredFaceDescriptors) {
                    const distance = faceapi.euclideanDistance(detection.descriptor, registered.descriptor);
                    if (distance < bestDistance && distance < MATCH_THRESHOLD) {
                        bestDistance = distance;
                        bestMatch = registered;
                    }
                }

                const box = detection.detection.box;
                
                // MIRROR THE X COORDINATE
                // Since video is mirrored with CSS, we need to mirror the x coordinate
                const mirroredX = canvas.width - box.x - box.width;
                
                debugLog(`Face ${i}: orig x=${box.x.toFixed(0)}, mirrored x=${mirroredX.toFixed(0)}, y=${box.y.toFixed(0)}, w=${box.width.toFixed(0)}, h=${box.height.toFixed(0)}`);
                
                // Draw face bounding box with mirrored X coordinate
                ctx.strokeStyle = bestMatch ? '#00ff00' : '#ff0000';
                ctx.lineWidth = 3;
                ctx.strokeRect(mirroredX, box.y, box.width, box.height);

                if (bestMatch) {
                    // Draw name background
                    const name = bestMatch.name;
                    ctx.fillStyle = '#00ff00';
                    ctx.fillRect(mirroredX, box.y - 30, box.width, 30);
                    
                    // Draw name text
                    ctx.fillStyle = '#000000';
                    ctx.font = 'bold 16px Arial';
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(name, mirroredX + 5, box.y - 15);
                    
                    // Draw confidence
                    const confidence = ((1 - bestDistance) * 100).toFixed(1);
                    ctx.font = '12px Arial';
                    ctx.fillText(`${confidence}%`, mirroredX + box.width - 45, box.y - 15);
                    
                    debugLog(`Matched: ${name} (confidence: ${confidence}%)`);
                    
                    // Mark attendance
                    await markAttendance(bestMatch.student_id);
                } else {
                    // Draw unknown face indicator
                    ctx.fillStyle = '#ff0000';
                    ctx.fillRect(mirroredX, box.y - 30, box.width, 30);
                    
                    // Draw unknown text
                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 14px Arial';
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('Not in Section', mirroredX + 5, box.y - 15);
                    
                    debugLog('No match found for this face');
                }
            }
        }
    } catch (error) {
        debugLog(`Recognition error: ${error.message}`);
    }
}

        let lastMarked = {};
        
        async function markAttendance(studentId) {
            const now = Date.now();
            const key = `${studentId}_${timeMode}`;
            
            // Prevent multiple marks within 10 seconds
            if (lastMarked[key] && now - lastMarked[key] < 10000) {
                debugLog(`Skipping duplicate mark for ${studentId}`);
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('student_id', studentId);
                formData.append('time_in', timeMode);

                const response = await fetch('mark_attendance.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    const student = filteredFaceDescriptors.find(s => s.student_id === studentId);
                    const mode = timeMode === 1 ? 'Time In' : 'Time Out';
                    showStatus('success', `✅ ${mode} marked for ${student.name}`);
                    lastMarked[key] = now;
                    debugLog(`Attendance marked: ${mode} for ${student.name}`);
                } else {
                    showStatus('error', result.message);
                    debugLog(`Attendance error: ${result.message}`);
                }
            } catch (err) {
                debugLog(`Network error: ${err.message}`);
            }
        }

        function showStatus(type, message) {
            const element = document.getElementById('attendanceStatus');
            element.className = `status ${type}`;
            element.textContent = message;
            element.style.display = 'block';
            debugLog(`Status: ${message}`);
        }

        // Initialize when page loads
        window.addEventListener('DOMContentLoaded', () => {
            debugLog('Page loaded, initializing...');
            initCamera();
        });
        
        // Clean up on page unload
        window.addEventListener('beforeunload', () => {
            stopAttendance();
            if (video && video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html>