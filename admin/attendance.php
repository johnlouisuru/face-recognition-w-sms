<!-- Refactored attendance.html with optimized section filtering -->
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
        }
        #video {
            width: 100%;
            height: auto;
            display: block;
        }
        canvas {
            position: absolute;
            top: 0;
            left: 0;
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
        video, canvas {
            width: 100%;
            height: auto;
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
                <div class="video-container">
                    <video id="video" autoplay muted></video>
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
                </div>

                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
    <script>
        let video, canvas;
        let isAttendanceRunning = false;
        let allFaceDescriptors = [];
        let filteredFaceDescriptors = [];
        let modelsLoaded = false;
        let selectedSection = '';
        let timeMode = 1; // 1 = time_in, 2 = time_out
        let sections = [];
        const MATCH_THRESHOLD = 0.6;

        async function loadModels() {
            if (modelsLoaded) return;
            try {
                const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                modelsLoaded = true;
                console.log('✅ Models loaded');
            } catch (error) {
                throw error;
            }
        }

        async function loadSections() {
            try {
                const response = await fetch('get_sections.php');
                sections = await response.json();
                
                const sectionFilter = document.getElementById('sectionFilter');
                sectionFilter.innerHTML = '<option value="">-- Select a Section --</option>';
                
                sections.forEach(section => {
                    sectionFilter.innerHTML += `<option value="${section.section_id}">${section.section_name}</option>`;
                });
                
                console.log(`✅ Loaded ${sections.length} sections`);
            } catch (err) {
                console.error('Error loading sections:', err);
                showStatus('error', 'Failed to load sections');
            }
        }

        // 🔑 REFACTORED: Load only faces for selected section
        async function loadFacesForSection(sectionId) {
            try {
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
                        console.error(`Error parsing descriptor for ${student.student_name}:`, err);
                        return null;
                    }
                }).filter(s => s !== null);
                
                console.log(`✅ Loaded ${filteredFaceDescriptors.length} faces for selected section`);
                return filteredFaceDescriptors.length;
            } catch (err) {
                console.error('Error loading faces:', err);
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

            // 🔑 Load faces only for selected section
            showStatus('info', 'Loading faces for selected section...');
            
            try {
                const faceCount = await loadFacesForSection(selectedSection);
                
                // Get section name
                const section = sections.find(s => s.section_id === selectedSection);
                const sectionName = section ? section.section_name : selectedSection;

                // Update UI
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
            video: { width: 640, height: 480 } 
        });
        video.srcObject = stream;
        
        video.addEventListener('loadedmetadata', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Start a loop to draw mirrored video frames
            function drawMirroredVideo() {
                ctx.save();
                ctx.scale(-1, 1); // flip horizontally
                ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
                ctx.restore();

                requestAnimationFrame(drawMirroredVideo);
            }
            drawMirroredVideo();
        });

        document.getElementById('attendanceLoading').style.display = 'none';
        document.getElementById('attendanceForm').style.display = 'block';
    } catch (err) {
        document.getElementById('attendanceLoading').style.display = 'none';
        showStatus('error', 'Error: ' + err.message);
    }
}


        // async function initCamera() {
        //     document.getElementById('attendanceLoading').style.display = 'block';
        //     document.getElementById('attendanceForm').style.display = 'none';
            
        //     try {
        //         await loadModels();
        //         await loadSections();
                
        //         video = document.getElementById('video');
        //         canvas = document.getElementById('canvas');

        //         const stream = await navigator.mediaDevices.getUserMedia({ 
        //             video: { width: 640, height: 480 } 
        //         });
        //         video.srcObject = stream;
                
        //         video.addEventListener('loadedmetadata', () => {
        //             canvas.width = video.videoWidth;
        //             canvas.height = video.videoHeight;
        //         });

        //         document.getElementById('attendanceLoading').style.display = 'none';
        //         document.getElementById('attendanceForm').style.display = 'block';
        //     } catch (err) {
        //         document.getElementById('attendanceLoading').style.display = 'none';
        //         showStatus('error', 'Error: ' + err.message);
        //     }
        // }

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
            recognizeFaces();
        }

        function stopAttendance() {
            isAttendanceRunning = false;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            showStatus('info', 'Recognition stopped');
        }

        async function recognizeFaces() {
    if (!isAttendanceRunning) return;

    const detections = await faceapi.detectAllFaces(video)
        .withFaceLandmarks()
        .withFaceDescriptors();

    const ctx = canvas.getContext('2d');

    // Flip the canvas horizontally before drawing
    ctx.setTransform(-1, 0, 0, 1, canvas.width, 0); 
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Reset transform so boxes/text are not flipped
    ctx.setTransform(1, 0, 0, 1, 0, 0);

    if (detections.length > 0) {
        const resizedDetections = faceapi.resizeResults(detections, {
            width: canvas.width,
            height: canvas.height
        });

        for (const detection of resizedDetections) {
            let bestMatch = null;
            let bestDistance = Infinity;

            for (const registered of filteredFaceDescriptors) {
                const distance = faceapi.euclideanDistance(detection.descriptor, registered.descriptor);
                if (distance < bestDistance && distance < MATCH_THRESHOLD) {
                    bestDistance = distance;
                    bestMatch = registered;
                }
            }

            const box = detection.detection.box;
            ctx.strokeStyle = bestMatch ? '#00ff00' : '#ff0000';
            ctx.lineWidth = 3;
            ctx.strokeRect(box.x, box.y, box.width, box.height);

            if (bestMatch) {
                ctx.fillStyle = '#00ff00';
                ctx.fillRect(box.x, box.y - 30, box.width, 30);
                ctx.fillStyle = '#000';
                ctx.font = '16px Arial';
                ctx.fillText(bestMatch.name, box.x + 5, box.y - 10);
                await markAttendance(bestMatch.student_id);
            } else {
                ctx.fillStyle = '#ff0000';
                ctx.fillRect(box.x, box.y - 30, box.width, 30);
                ctx.fillStyle = '#fff';
                ctx.font = '16px Arial';
                ctx.fillText('Not in Section', box.x + 5, box.y - 10);
            }
        }
    }

    requestAnimationFrame(recognizeFaces);
}


        // async function recognizeFaces() {
        //     if (!isAttendanceRunning) return;

        //     const detections = await faceapi.detectAllFaces(video)
        //         .withFaceLandmarks()
        //         .withFaceDescriptors();

        //     const ctx = canvas.getContext('2d');
        //     ctx.clearRect(0, 0, canvas.width, canvas.height);

        //     if (detections.length > 0) {
        //         const resizedDetections = faceapi.resizeResults(detections, {
        //             width: canvas.width,
        //             height: canvas.height
        //         });

        //         for (const detection of resizedDetections) {
        //             let bestMatch = null;
        //             let bestDistance = Infinity;

        //             // 🔑 Compare only with filtered descriptors (section-specific)
        //             for (const registered of filteredFaceDescriptors) {
        //                 const distance = faceapi.euclideanDistance(detection.descriptor, registered.descriptor);
        //                 if (distance < bestDistance && distance < MATCH_THRESHOLD) {
        //                     bestDistance = distance;
        //                     bestMatch = registered;
        //                 }
        //             }

        //             const box = detection.detection.box;
        //             ctx.strokeStyle = bestMatch ? '#00ff00' : '#ff0000';
        //             ctx.lineWidth = 3;
        //             ctx.strokeRect(box.x, box.y, box.width, box.height);

        //             if (bestMatch) {
        //                 ctx.fillStyle = '#00ff00';
        //                 ctx.fillRect(box.x, box.y - 30, box.width, 30);
        //                 ctx.fillStyle = '#000';
        //                 ctx.font = '16px Arial';
        //                 ctx.fillText(bestMatch.name, box.x + 5, box.y - 10);
        //                 await markAttendance(bestMatch.student_id);
        //             } else {
        //                 ctx.fillStyle = '#ff0000';
        //                 ctx.fillRect(box.x, box.y - 30, box.width, 30);
        //                 ctx.fillStyle = '#fff';
        //                 ctx.font = '16px Arial';
        //                 ctx.fillText('Not in Section', box.x + 5, box.y - 10);
        //             }
        //         }
        //     }

        //     requestAnimationFrame(recognizeFaces);
        // }

        let lastMarked = {};
        
        async function markAttendance(studentId) {
            const now = Date.now();
            const key = `${studentId}_${timeMode}`;
            if (lastMarked[key] && now - lastMarked[key] < 10000) return;
            
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
                } else {
                    showStatus('error', result.message);
                }
            } catch (err) {
                console.error('Attendance error:', err);
            }
        }

        function showStatus(type, message) {
            const element = document.getElementById('attendanceStatus');
            element.className = `status ${type}`;
            element.textContent = message;
            element.style.display = 'block';
        }

        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => initCamera(), 500);
        });
    </script>
</body>
</html>