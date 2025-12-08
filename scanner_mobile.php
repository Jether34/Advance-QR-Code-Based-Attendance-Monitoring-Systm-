<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>QR Scanner - Attendance</title>
    <!-- Load Html5Qrcode library -->
    <script>
        // Flag to track library loading
        window.html5QrcodeReady = false;
    </script>
    <script src="html5-qrcode.min.js"></script>
    <script>
        // Check if library loaded from local file
        if (typeof Html5Qrcode !== 'undefined') {
            window.html5QrcodeReady = true;
            console.log('✓ Html5Qrcode loaded from local file');
        } else {
            console.log('Html5Qrcode not available from local file, will use CDN fallback');
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        
        body {
            background: #000;
        }
        
        #reader {
            width: 100vw !important;
            height: 100vh !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #result {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.95);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
            text-align: center;
            color: white;
        }
        
        #result.show {
            display: flex;
        }
        
        .result-content {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            border-radius: 20px;
            padding: 30px;
            max-width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .result-content h2 {
            font-size: 1.8em;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .result-content p {
            font-size: 1.1em;
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .spinner {
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .controls {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 100;
        }
        
        .btn {
            flex: 1;
            padding: 15px 20px;
            background: rgba(33, 140, 33, 0.9);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        
        .btn:active {
            transform: scale(0.95);
        }
        
        .btn.secondary {
            background: rgba(100, 100, 100, 0.9);
        }
        
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 50;
        }
        
        .loading h2 {
            font-size: 1.2em;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div id="reader"></div>
    
    <div id="result">
        <div class="result-content">
            <h2 id="resultTitle">✅ Success!</h2>
            <p id="resultMessage">Processing...</p>
            <p id="resultDetails" style="font-size: 0.9em; color: #ecf0f1; margin-top: 15px;"></p>
            <div class="spinner"></div>
        </div>
    </div>
    
    <div id="loading" class="loading" style="display: none;">
        <div class="spinner"></div>
        <h2>Initializing Camera...</h2>
        <p id="debugInfo" style="margin-top: 15px; font-size: 0.8em; color: #999;">Loading library...</p>
    </div>
    
    <div class="controls">
        <button class="btn" id="backBtn">← Back</button>
        <button class="btn secondary" id="toggleBtn">⏸ Pause</button>
    </div>

    <script>
        let html5QrcodeScanner = null;
        let isScanning = true;
        const resultDiv = document.getElementById('result');
        const loadingDiv = document.getElementById('loading');
        const backBtn = document.getElementById('backBtn');
        const toggleBtn = document.getElementById('toggleBtn');
        const debugInfo = document.getElementById('debugInfo');
        
        function updateDebug(msg) {
            console.log(msg);
            if (debugInfo) {
                debugInfo.textContent = msg;
            }
        }
        
        // Vibration feedback
        function vibrate(pattern = 50) {
            if (navigator.vibrate) {
                navigator.vibrate(pattern);
            }
        }
        
        function successVibration() {
            vibrate([50, 30, 50]);
        }
        
        function startScanning() {
            loadingDiv.style.display = 'flex';
            updateDebug('Starting scanner initialization...');
            
            // Check if library already loaded (from local file)
            if (typeof Html5Qrcode !== 'undefined') {
                updateDebug('✓ Library already loaded');
                setTimeout(() => initializeCamera(), 100);
                return;
            }
            
            // Wait for library to load
            let attempts = 0;
            const checkLibrary = setInterval(() => {
                attempts++;
                updateDebug('Library check: ' + attempts + '/30');
                
                if (typeof Html5Qrcode !== 'undefined') {
                    clearInterval(checkLibrary);
                    updateDebug('✓ Library loaded');
                    setTimeout(() => initializeCamera(), 100);
                } else if (attempts >= 30) {
                    clearInterval(checkLibrary);
                    loadingDiv.style.display = 'none';
                    updateDebug('✗ Library timeout, loading CDN...');
                    showError('Library Error', 'Failed to load QR scanner. Please refresh the page.');
                }
            }, 100);
        }
        
        function initializeCamera() {
            updateDebug('Requesting camera permission...');
            updateDebug('Protocol: ' + window.location.protocol);
            updateDebug('Secure context: ' + window.isSecureContext);
            
            try {
                if (typeof Html5Qrcode === 'undefined') {
                    throw new Error('Html5Qrcode library not available');
                }
                
                updateDebug('Calling getCameras()...');
                Html5Qrcode.getCameras().then(cameras => {
                    updateDebug('✓ Cameras found: ' + (cameras ? cameras.length : 0));
                    
                    if (cameras && cameras.length > 0) {
                        // Try to find the back camera (environment facing)
                        let selectedCamera = cameras[0].id;
                        
                        // Look for back/rear camera
                        for (let i = 0; i < cameras.length; i++) {
                            const label = cameras[i].label.toLowerCase();
                            if (label.indexOf('back') >= 0 || label.indexOf('rear') >= 0 || label.indexOf('environment') >= 0) {
                                selectedCamera = cameras[i].id;
                                updateDebug('Found back camera: ' + cameras[i].label);
                                break;
                            }
                        }
                        
                        // If multiple cameras and didn't find "back", use the last one (usually back camera)
                        if (cameras.length > 1 && selectedCamera === cameras[0].id) {
                            selectedCamera = cameras[cameras.length - 1].id;
                            updateDebug('Using last camera (likely back): ' + cameras[cameras.length - 1].label);
                        }
                        
                        updateDebug('Using camera: ' + selectedCamera);
                        startCamera(selectedCamera);
                    } else {
                        loadingDiv.style.display = 'none';
                        updateDebug('✗ No cameras detected');
                        showError('No Camera', 'No camera found on this device');
                    }
                }).catch(err => {
                    // Extract error details - handle various error formats
                    let errName = 'Unknown Error';
                    let errMsg = 'No error details available';
                    
                    if (err) {
                        // Try different error properties
                        errName = err.name || err.type || err.constructor.name || 'Unknown';
                        errMsg = err.message || err.toString() || 'Unknown error';
                        
                        // Log full error for debugging
                        updateDebug('Full error: ' + JSON.stringify(err));
                    }
                    
                    updateDebug('✗ Camera Error: ' + errName + ' - ' + errMsg);
                    loadingDiv.style.display = 'none';
                    
                    // Check for secure context error
                    if (errMsg.indexOf('secure context') >= 0 || errMsg.indexOf('https') >= 0) {
                        updateDebug('⚠ Secure context error - you are on: ' + window.location.href);
                        if (window.location.protocol === 'https:') {
                            updateDebug('You ARE on HTTPS - trying direct getUserMedia access...');
                            // Try direct access to camera without Html5Qrcode's secure context check
                            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                                .then(stream => {
                                    updateDebug('✓ Direct camera access successful!');
                                    // Stop the stream for now
                                    stream.getTracks().forEach(track => track.stop());
                                    // Now use Html5Qrcode properly
                                    Html5Qrcode.getCameras().then(cameras => {
                                        if (cameras && cameras.length > 0) {
                                            startCamera(cameras[0].id);
                                        }
                                    });
                                }).catch(dirErr => {
                                    updateDebug('Direct access also failed: ' + (dirErr.message || dirErr.toString()));
                                    showError('Camera Error', dirErr.name === 'NotAllowedError' ? 'Camera permission denied. Enable in Settings.' : 'Camera access failed.');
                                });
                        } else {
                            showError('Connection Error', 'You must access this page via HTTPS.');
                        }
                    } else if (errName === 'NotAllowedError' || errMsg.indexOf('Permission') >= 0) {
                        showError('Permission Denied', 'Camera permission is required. Please enable it in Settings and try again.');
                    } else if (errName === 'NotFoundError' || errMsg.indexOf('NotFound') >= 0) {
                        showError('No Camera Found', 'No camera device detected on this device.');
                    } else {
                        showError('Camera Error', errName + ': ' + errMsg);
                    }
                });
            } catch (err) {
                const msg = (err && err.message) ? err.message : 'Unknown error';
                updateDebug('✗ Exception: ' + msg);
                loadingDiv.style.display = 'none';
                showError('Error', msg);
            }
        }
        
        function startCamera(cameraId) {
            updateDebug('Starting camera stream...');
            
            html5QrcodeScanner = new Html5Qrcode("reader");
            
            const config = {
                fps: 15,
                qrbox: { width: 250, height: 250 },
                rememberLastUsedCamera: true,
                aspectRatio: 1.0,
                showTorchButton: true,
                showZoomButton: true
            };
            
            html5QrcodeScanner.start(
                cameraId,
                config,
                onScanSuccess,
                onScanError
            ).then(() => {
                updateDebug('✓ Camera started successfully');
                setTimeout(() => {
                    loadingDiv.style.display = 'none';
                }, 500);
                isScanning = true;
            }).catch(err => {
                updateDebug('✗ Failed to start: ' + err.message);
                loadingDiv.style.display = 'none';
                showError('Camera Error', 'Failed to start camera: ' + err.message);
            });
        }
        
        function onScanSuccess(decodedText, decodedResult) {
            successVibration();
            console.log('QR Code scanned:', decodedText);
            // Pause temporarily but keep isScanning true for auto-resume
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause(false);
            }
            recordAttendance(decodedText);
        }
        
        function onScanError(error) {
            // Ignore scanning errors
        }
        
        function recordAttendance(qrData) {
            // Extract ONLY the student ID from QR code (even if it contains full student info)
            console.log('Raw QR data scanned:', qrData);
            let studentId = '';
            
            // Try to parse as JSON first
            try {
                const parsed = JSON.parse(qrData);
                console.log('Parsed as JSON:', parsed);
                
                // Extract student_id from various possible field names
                studentId = parsed.student_id || parsed.id || parsed.studentId || parsed.studentID || '';
                
                if (!studentId) {
                    // If no ID field found in JSON, use the first value that looks like an ID
                    const values = Object.values(parsed);
                    for (let val of values) {
                        if (typeof val === 'string' && val.length > 0 && val.length < 50) {
                            studentId = val;
                            break;
                        }
                    }
                }
            } catch (e) {
                // Not JSON - treat as plain text student ID
                console.log('Not JSON, using as plain student ID');
                studentId = qrData.trim();
            }
            
            console.log('Extracted student ID:', studentId);
            
            document.getElementById('resultTitle').textContent = '⏳ Recording Attendance...';
            document.getElementById('resultMessage').textContent = 'Student ID: ' + studentId;
            document.getElementById('resultDetails').textContent = 'Validating...';
            resultDiv.classList.add('show');
            
            fetch('record_attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    code: studentId,
                    type: 'qr',
                    period: 'scan'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const student = data.student_info;
                    document.getElementById('resultTitle').textContent = '✅ Attendance Recorded!';
                    document.getElementById('resultMessage').textContent = student.full_name;
                    document.getElementById('resultDetails').innerHTML = 
                        '<strong>Grade & Section:</strong> ' + student.grade_level + ' - ' + student.section_block +
                        '<br><strong>Status:</strong> ' + data.attendance_info.status +
                        '<br><strong>Time:</strong> ' + new Date(data.attendance_info.timestamp).toLocaleTimeString();
                    
                    // Auto-resume after 1.5 seconds
                    setTimeout(() => {
                        resultDiv.classList.remove('show');
                        resumeScanning();
                    }, 1500);
                } else {
                    document.getElementById('resultTitle').textContent = '❌ Error';
                    document.getElementById('resultMessage').textContent = data.error || 'Student not found';
                    document.getElementById('resultDetails').textContent = 'Please try again';
                    
                    setTimeout(() => {
                        resultDiv.classList.remove('show');
                        resumeScanning();
                    }, 2000);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                document.getElementById('resultTitle').textContent = '❌ Network Error';
                document.getElementById('resultMessage').textContent = 'Failed to record attendance';
                document.getElementById('resultDetails').textContent = err.message;
                
                setTimeout(() => {
                    resultDiv.classList.remove('show');
                    resumeScanning();
                }, 2000);
            });
        }
        
        function stopScanning() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause(false);
            }
            isScanning = false;
        }
        
        function resumeScanning() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.resume();
            }
            isScanning = true;
        }
        
        function showError(title, message) {
            document.getElementById('resultTitle').textContent = title;
            document.getElementById('resultMessage').textContent = message;
            document.getElementById('resultDetails').textContent = '';
            resultDiv.classList.add('show');
        }
        
        // Controls
        backBtn.addEventListener('click', () => {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    window.history.back();
                }).catch(err => {
                    console.error('Error stopping scanner:', err);
                    window.history.back();
                });
            } else {
                window.history.back();
            }
        });
        
        toggleBtn.addEventListener('click', () => {
            if (isScanning) {
                stopScanning();
                toggleBtn.textContent = '▶ Resume';
            } else {
                resumeScanning();
                toggleBtn.textContent = '⏸ Pause';
            }
        });
        
        // Start scanning when page loads
        window.addEventListener('load', startScanning);
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().catch(err => console.error('Error:', err));
            }
        });
        
        // CDN fallback if local library didn't load
        setTimeout(() => {
            if (typeof Html5Qrcode === 'undefined') {
                updateDebug('Loading from CDN...');
                window.html5QrcodeReady = false;
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/html5-qrcode@2.3.4/dist/html5-qrcode.min.js';
                script.onerror = () => {
                    updateDebug('✗ CDN also failed');
                };
                script.onload = () => {
                    updateDebug('✓ CDN loaded');
                    window.html5QrcodeReady = true;
                };
                document.head.appendChild(script);
            }
        }, 1500);
    </script>
</body>
</html>
