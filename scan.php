<?php
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Scan - Attendance</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #1a1a1a;
            min-height: 100vh;
        }
        
        /* Desktop layout */
        body.desktop {
            background: linear-gradient(135deg, #f8fff8 0%, #eaffea 100%);
        }
        
        body.desktop h1 {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            color: #218c21;
        }
        
        body.desktop .scanner-container {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            gap: 20px;
            padding: 20px;
        }
        
        /* Mobile layout */
        body.mobile {
            background: #000;
        }
        
        body.mobile h1 {
            display: none;
        }
        
        body.mobile #reader {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 10;
        }
        
        h1 {
            text-align: center;
            color: #218c21;
            margin-bottom: 20px;
        }
        
        h3 {
            color: #176617;
            margin-bottom: 15px;
        }
        
        .scanner-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .scanner-section {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(33, 140, 33, 0.1);
        }
        
        #barcodeInput {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #d4edda;
            border-radius: 8px;
            transition: border-color 0.3s;
        }
        
        #barcodeInput:focus {
            border-color: #218c21;
            outline: none;
            box-shadow: 0 0 5px rgba(33, 140, 33, 0.3);
        }
        
        #last {
            margin-top: 15px;
            padding: 15px;
            border-radius: 10px;
            font-weight: 500;
        }
        
        /* Mobile controls */
        .mobile-controls {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            gap: 12px;
            z-index: 11;
        }
        
        body.mobile .mobile-controls {
            display: flex;
        }
        
        .control-btn {
            flex: 1;
            padding: 16px;
            background: rgba(33, 140, 33, 0.9);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .control-btn:active {
            transform: scale(0.95);
        }
        
        .control-btn.secondary {
            background: rgba(100, 100, 100, 0.9);
        }
        
        /* Mobile result display */
        body.mobile #last {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            max-height: 100vh;
            margin: 0;
            padding: 20px;
            background: rgba(0,0,0,0.95);
            z-index: 100;
            overflow-y: auto;
        }
        
        @media (max-width: 768px) {
            .scanner-container {
                flex-direction: column;
            }
            
            #reader {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <h1>📱 Automatic Attendance Scanner</h1>
    
    <!-- iOS Camera Permission Notice -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
                const notice = document.createElement('div');
                notice.style.cssText = `
                    background: #fff3cd;
                    border: 2px solid #ffc107;
                    border-radius: 12px;
                    padding: 16px;
                    margin: 20px;
                    color: #856404;
                    font-size: 0.95em;
                    line-height: 1.6;
                `;
                notice.innerHTML = `
                    <strong>📱 iOS Users:</strong> When prompted, tap "Allow" to enable camera access for QR scanning.
                    If you denied permission, go to Settings → Camera → Palawan National School and enable it.
                `;
                document.body.insertBefore(notice, document.body.firstChild.nextSibling);
            }
        });
    </script>
    
    <div class="scanner-container">
        <div class="scanner-section">
            <h3>📷 QR Code Scanner</h3>
            <div id="reader" style="width:400px"></div>
        </div>
        <div class="scanner-section">
            <h3>⌨ Manual Barcode Input</h3>
            <input id="barcodeInput" placeholder="Scan or type student ID / barcode" />
        </div>
    </div>
    
    <div id="last"></div>
    
    <div class="mobile-controls">
        <button class="control-btn" id="toggleCamera">📷 Start Camera</button>
        <button class="control-btn secondary" id="stopScan">✋ Stop</button>
    </div>

    <script>
        // Load Html5Qrcode library with fallback
        function loadHtml5QrcodeLibrary(callback) {
            if (typeof Html5Qrcode !== 'undefined') {
                callback();
                return;
            }
            
            const script = document.createElement('script');
            script.src = 'html5-qrcode.min.js';
            script.onload = function() {
                if (typeof Html5Qrcode !== 'undefined') {
                    console.log('Html5Qrcode loaded successfully');
                    callback();
                } else {
                    console.warn('Local html5-qrcode failed, trying CDN');
                    loadFromCDN(callback);
                }
            };
            script.onerror = function() {
                console.warn('Local html5-qrcode failed to load, trying CDN');
                loadFromCDN(callback);
            };
            document.head.appendChild(script);
        }
        
        function loadFromCDN(callback) {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode@2.3.4/dist/html5-qrcode.min.js';
            script.onload = function() {
                if (typeof Html5Qrcode !== 'undefined') {
                    console.log('Html5Qrcode loaded from CDN');
                    callback();
                } else {
                    console.error('Failed to load Html5Qrcode from both sources');
                }
            };
            script.onerror = function() {
                console.error('Failed to load Html5Qrcode from CDN');
            };
            document.head.appendChild(script);
        }
        
        // Detect mobile device

        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const last = document.getElementById('last');
        let html5Qrcode = null;
        let isScanning = false;
        let cameraId = null;
        
        // Check if HTTPS is available (camera requires secure context on deployed sites)
        // Allow localhost and IP addresses for development
        const isSecureContext = true; // For local development, we'll handle errors from the library
        
        // Apply device class
        if (isMobile) {
            document.body.classList.add('mobile');
        } else {
            document.body.classList.add('desktop');
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
        
        function errorVibration() {
            vibrate([200, 100, 200]);
        }

        function record(code, type){

            // Extract student ID from any QR code format (embedded, URL, or legacy)
            let studentId = null;
            
            // Check if this is a formatted student info QR code
            if (code.includes('=== STUDENT INFORMATION ===')) {
                const studentInfo = parseStudentInfoFromQR(code);
                if (studentInfo && studentInfo.student_id) {
                    studentId = studentInfo.student_id;
                    console.log('Extracted student ID from embedded QR code:', studentId);
                } else {
                    last.innerHTML = '<div style="color: red; font-weight: bold;">❌ Could not parse student information from embedded QR code</div>';
                    last.style.display = 'block';
                    setTimeout(() => { 
                        last.innerHTML = ''; 
                        last.style.display = 'none'; 
                    }, 5000);
                    return;
                }
            } else {
                // Extract student ID from URL or legacy formats
                studentId = extractStudentId(code);
            }
            
            // Check if student ID extraction was successful
            if (!studentId) {
                errorVibration();
                last.innerHTML = '<div style="color: red; font-weight: bold;">❌ Invalid QR Code Format</div>' +
                                '<div style="color: #666; font-size: 0.9em; margin-top: 5px;">Unable to extract student ID from: ' + 
                                (code.length > 50 ? code.substring(0, 50) + '...' : code) + '</div>';
                last.style.display = 'block';
                setTimeout(() => { 
                    last.innerHTML = ''; 
                    last.style.display = 'none'; 
                    if (isMobile && isScanning) startScanning();
                }, 3000);
                return;
            }
            
            // Show processing feedback with extracted student ID
            last.innerHTML = '<div style="color: #218c21; font-weight: bold;"> Validating Student ID: ' + studentId + '</div>' +
                            '<div style="color: #666; font-size: 0.9em; margin-top: 5px;">Checking database and recording attendance...</div>';
            last.style.display = 'block';
            
            const period = 'scan'; // Default period for simple scanning
            console.log('Auto-recording attendance for student ID:', studentId);
            
            fetch('record_attendance.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({code:studentId, type:type, period:period})})
            .then(r => r.json())
            .then(data => {
                if(data.success){
                    successVibration();
                    // Auto-record successful - show confirmation
                    displayAutoRecordedAttendance(data, studentId);
                    // Notify parent to refresh attendance
                    if(window.parent){
                        window.parent.postMessage('refreshAttendance', '*');
                    }
                    // On mobile, auto-resume scanning after 3 seconds
                    if (isMobile) {
                        setTimeout(() => {
                            last.innerHTML = '';
                            last.style.display = 'none';
                            if (isScanning) startScanning();
                        }, 3000);
                    }
                } else {
                    errorVibration();
                    // Show error message
                    last.innerHTML = '<div style="color: red; font-weight: bold;">❌ Attendance Recording Failed</div>' +
                                   '<div style="color: #666; font-size: 0.9em; margin-top: 5px;"><strong>Student ID:</strong> ' + studentId + '</div>' +
                                   '<div style="color: red; font-size: 0.9em;"><strong>Error:</strong> ' + 
                                   (data.error === 'Student not found' ? 'Student ID not found in database' : data.error) + '</div>';
                    last.style.display = 'block';
                    
                    // Auto-hide error after 5 seconds on mobile, 8 on desktop
                    setTimeout(()=> { 
                        last.innerHTML = ''; 
                        last.style.display = 'none'; 
                        if (isMobile && isScanning) startScanning();
                    }, isMobile ? 5000 : 8000);
                }
            })
            .catch(e => {
                errorVibration();
                last.textContent = 'Request error';
                last.style.color = 'red';
                last.style.display = 'block';
                setTimeout(()=> { 
                    last.textContent = ''; 
                    last.style.display = 'none'; 
                    if (isMobile && isScanning) startScanning();
                }, 5000);
            })
        }

        function displayStudentInfo(data) {
            const student = data.student_info;
            const attendance = data.attendance_info;
            
            last.innerHTML = `
                <div style="
                    background: linear-gradient(135deg, #27ae60, #2ecc71);
                    border-radius: 15px;
                    padding: 20px;
                    color: white;
                    box-shadow: 0 8px 25px rgba(46, 204, 113, 0.3);
                    text-align: center;
                    max-width: 400px;
                    margin: 0 auto;
                    animation: slideIn 0.5s ease-out;
                ">
                    <div style="font-size: 2em; margin-bottom: 15px;">✅</div>
                    <h3 style="margin: 0 0 15px 0; font-size: 1.4em; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        Attendance Recorded Successfully!
                    </h3>
                    
                    <div style="
                        background: rgba(255,255,255,0.2);
                        border-radius: 10px;
                        padding: 15px;
                        margin: 15px 0;
                        backdrop-filter: blur(10px);
                    ">
                        <h4 style="margin: 0 0 10px 0; color: #ecf0f1;">Student Information</h4>
                        <div style="text-align: left; font-size: 0.95em; line-height: 1.6;">
                            <div><strong>Name:</strong> ${student.full_name}</div>
                            <div><strong>Student ID:</strong> ${student.student_id}</div>
                            <div><strong>Email:</strong> ${student.email}</div>
                            <div><strong>Grade & Section:</strong> ${student.grade_level} - ${student.section_block}</div>
                            ${student.strand ? `<div><strong>Strand:</strong> ${student.strand}</div>` : ''}
                            <div><strong>Gender:</strong> ${student.gender}</div>
                        </div>
                    </div>
                    
                    <div style="
                        background: rgba(255,255,255,0.2);
                        border-radius: 10px;
                        padding: 15px;
                        margin: 15px 0;
                        backdrop-filter: blur(10px);
                    ">
                        <h4 style="margin: 0 0 10px 0; color: #ecf0f1;">Attendance Details</h4>
                        <div style="text-align: left; font-size: 0.95em; line-height: 1.6;">
                            <div><strong>Status:</strong> <span style="color: #f1c40f;">${attendance.status}</span></div>
                            <div><strong>Time:</strong> ${new Date(attendance.timestamp).toLocaleTimeString()}</div>
                            <div><strong>Date:</strong> ${new Date(attendance.timestamp).toLocaleDateString()}</div>
                            ${attendance.morning_time ? `<div><strong>Morning:</strong> ${attendance.morning_time}</div>` : ''}
                            ${attendance.afternoon_time ? `<div><strong>Afternoon:</strong> ${attendance.afternoon_time}</div>` : ''}
                        </div>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <button onclick="clearDisplay()" style="
                            background: rgba(255,255,255,0.2);
                            border: 2px solid rgba(255,255,255,0.3);
                            color: white;
                            padding: 10px 20px;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 0.9em;
                            transition: all 0.3s ease;
                        " onmouseover="this.style.background='rgba(255,255,255,0.3)'" 
                           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                            Close & Scan Next
                        </button>
                    </div>
                </div>
                
                <style>
                    @keyframes slideIn {
                        from { transform: translateY(-20px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                </style>
            `;
            last.style.display = 'block';
        }

        function clearDisplay() {
            last.innerHTML = '';
            last.style.display = 'none';
        }

        function displayAutoRecordedAttendance(data, scannedStudentId) {
            // Display automatic attendance recording results
            const student = data.student_info;
            const attendance = data.attendance_info;
            
            last.innerHTML = `
                <div style="
                    background: linear-gradient(135deg, #27ae60, #2ecc71);
                    border-radius: 15px;
                    padding: 20px;
                    color: white;
                    box-shadow: 0 8px 25px rgba(46, 204, 113, 0.3);
                    text-align: center;
                    max-width: 450px;
                    margin: 0 auto;
                    animation: slideIn 0.5s ease-out;
                ">
                    <div style="font-size: 2.5em; margin-bottom: 15px;">✅</div>
                    <h3 style="margin: 0 0 15px 0; font-size: 1.5em; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        Attendance Auto-Recorded!
                    </h3>
                    
                    <div style="
                        background: rgba(255,255,255,0.2);
                        border-radius: 10px;
                        padding: 15px;
                        margin: 15px 0;
                        backdrop-filter: blur(10px);
                    ">
                        <h4 style="margin: 0 0 10px 0; color: #ecf0f1;">📋 Student Validated</h4>
                        <div style="text-align: left; font-size: 0.95em; line-height: 1.6;">
                            <div><strong>Scanned ID:</strong> ${scannedStudentId}</div>
                            <div><strong>Student Name:</strong> ${student.full_name}</div>
                            <div><strong>Email:</strong> ${student.email}</div>
                            <div><strong>Grade & Section:</strong> ${student.grade_level} - ${student.section_block}</div>
                            ${student.strand ? `<div><strong>Strand:</strong> ${student.strand}</div>` : ''}
                        </div>
                    </div>
                    
                    <div style="
                        background: rgba(255,255,255,0.2);
                        border-radius: 10px;
                        padding: 15px;
                        margin: 15px 0;
                        backdrop-filter: blur(10px);
                    ">
                        <h4 style="margin: 0 0 10px 0; color: #ecf0f1;"> Attendance Recorded</h4>
                        <div style="text-align: left; font-size: 0.95em; line-height: 1.6;">
                            <div><strong>Status:</strong> <span style="color: #f1c40f; font-weight: bold;">${attendance.status}</span></div>
                            <div><strong>Time Recorded:</strong> ${new Date(attendance.timestamp).toLocaleTimeString()}</div>
                            <div><strong>Date:</strong> ${new Date(attendance.timestamp).toLocaleDateString()}</div>
                            ${attendance.morning_time ? `<div><strong>Morning Time:</strong> ${attendance.morning_time}</div>` : ''}
                            ${attendance.afternoon_time ? `<div><strong>Afternoon Time:</strong> ${attendance.afternoon_time}</div>` : ''}
                        </div>
                    </div>
                    
                    <div style="
                        background: rgba(255,255,255,0.15);
                        border-radius: 8px;
                        padding: 12px;
                        margin: 15px 0;
                        font-size: 0.9em;
                    ">
                        <div style="color: #ecf0f1;"><strong> Auto-Processing Complete</strong></div>
                        <div style="color: #d5f4e6; margin-top: 5px;">
                            ✓ Student ID validated in database<br>
                            ✓ Attendance recorded automatically<br>
                            ✓ Teacher dashboard updated
                        </div>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <button onclick="clearDisplay()" style="
                            background: rgba(255,255,255,0.2);
                            border: 2px solid rgba(255,255,255,0.3);
                            color: white;
                            padding: 12px 25px;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 1em;
                            font-weight: 600;
                            transition: all 0.3s ease;
                        " onmouseover="this.style.background='rgba(255,255,255,0.3)'" 
                           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                            📷 Scan Next Student
                        </button>
                    </div>
                </div>
                
                <style>
                    @keyframes slideIn {
                        from { transform: translateY(-20px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                </style>
            `;
            last.style.display = 'block';
            
            // Auto-clear after 10 seconds for faster workflow
            setTimeout(() => {
                if (last.innerHTML.includes('Auto-Recorded')) {
                    clearDisplay();
                }
            }, 10000);
        }

        function parseStudentInfoFromQR(code) {
            // Parse student information directly from QR code text
            if (!code.includes('=== STUDENT INFORMATION ===')) {
                return null;
            }
            
            const lines = code.split('\n');
            const studentInfo = {};
            
            for (let line of lines) {
                if (line.includes(':')) {
                    const parts = line.split(':');
                    if (parts.length >= 2) {
                        const key = parts[0].trim();
                        const value = parts.slice(1).join(':').trim();
                        
                        switch (key) {
                            case 'Student ID':
                                studentInfo.student_id = value;
                                break;
                            case 'Name':
                                studentInfo.full_name = value;
                                break;
                            case 'Email':
                                studentInfo.email = value;
                                break;
                            case 'Grade':
                                studentInfo.grade_level = value;
                                break;
                            case 'Strand':
                                studentInfo.strand = value;
                                break;
                            case 'Section':
                                studentInfo.section_block = value;
                                break;
                            case 'Gender':
                                studentInfo.gender = value;
                                break;
                            case 'LRN':
                                studentInfo.lrn = value;
                                break;
                            case 'Enrolled':
                                studentInfo.created_at = value;
                                break;
                            case 'QR Generated':
                                studentInfo.qr_generated = value;
                                break;
                        }
                    }
                }
            }
            
            return studentInfo;
        }

        function displayQRStudentInfo(studentInfo) {
            // Display student info extracted directly from QR code
            last.innerHTML = `
                <div style="
                    background: linear-gradient(135deg, #4a90e2, #357abd);
                    border-radius: 15px;
                    padding: 20px;
                    color: white;
                    box-shadow: 0 8px 25px rgba(74, 144, 226, 0.3);
                    text-align: center;
                    max-width: 400px;
                    margin: 0 auto;
                    animation: slideIn 0.5s ease-out;
                ">
                    <div style="font-size: 2em; margin-bottom: 15px;"> </div>
                    <h3 style="margin: 0 0 15px 0; font-size: 1.4em; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        Student Information from QR Code
                    </h3>
                    
                    <div style="
                        background: rgba(255,255,255,0.2);
                        border-radius: 10px;
                        padding: 15px;
                        margin: 15px 0;
                        backdrop-filter: blur(10px);
                    ">
                        <h4 style="margin: 0 0 10px 0; color: #ecf0f1;">Student Details</h4>
                        <div style="text-align: left; font-size: 0.95em; line-height: 1.6;">
                            <div><strong>Name:</strong> ${studentInfo.full_name}</div>
                            <div><strong>Student ID:</strong> ${studentInfo.student_id}</div>
                            <div><strong>Email:</strong> ${studentInfo.email}</div>
                            <div><strong>Grade & Section:</strong> ${studentInfo.grade_level} - ${studentInfo.section_block}</div>
                            ${studentInfo.strand ? `<div><strong>Strand:</strong> ${studentInfo.strand}</div>` : ''}
                            <div><strong>Gender:</strong> ${studentInfo.gender}</div>
                            ${studentInfo.lrn ? `<div><strong>LRN:</strong> ${studentInfo.lrn}</div>` : ''}
                            ${studentInfo.created_at ? `<div><strong>Enrolled:</strong> ${studentInfo.created_at}</div>` : ''}
                        </div>
                    </div>
                    
                    <div style="
                        background: rgba(255,255,255,0.2);
                        border-radius: 10px;
                        padding: 15px;
                        margin: 15px 0;
                        backdrop-filter: blur(10px);
                    ">
                        <h4 style="margin: 0 0 10px 0; color: #ecf0f1;">QR Code Info</h4>
                        <div style="text-align: left; font-size: 0.9em; line-height: 1.6;">
                            <div><strong>Data Source:</strong> Embedded QR Code</div>
                            <div><strong>Scanned:</strong> ${new Date().toLocaleString()}</div>
                            ${studentInfo.qr_generated ? `<div><strong>QR Generated:</strong> ${studentInfo.qr_generated}</div>` : ''}
                            <div style="color: #b3d9ff; margin-top: 10px;"><strong>✅ Complete offline information available!</strong></div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <button onclick="recordAttendanceFromQR('${studentInfo.student_id}')" style="
                            background: rgba(46, 204, 113, 0.9);
                            border: 2px solid rgba(255,255,255,0.3);
                            color: white;
                            padding: 10px 20px;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 0.9em;
                            transition: all 0.3s ease;
                            margin: 5px;
                        " onmouseover="this.style.background='rgba(46, 204, 113, 1)'" 
                           onmouseout="this.style.background='rgba(46, 204, 113, 0.9)'">
                            ✅ Record Attendance
                        </button>
                        <button onclick="clearDisplay()" style="
                            background: rgba(255,255,255,0.2);
                            border: 2px solid rgba(255,255,255,0.3);
                            color: white;
                            padding: 10px 20px;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 0.9em;
                            transition: all 0.3s ease;
                            margin: 5px;
                        " onmouseover="this.style.background='rgba(255,255,255,0.3)'" 
                           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                            📷 Scan Next
                        </button>
                    </div>
                </div>
            `;
            last.style.display = 'block';
        }

        function recordAttendanceFromQR(studentId) {
            // Record attendance using the student ID from QR
            last.innerHTML = '<div style="color: #218c21; font-weight: bold;"> Recording attendance for: ' + studentId + '</div>';
            
            fetch('record_attendance.php', {
                method: 'POST', 
                headers: {'Content-Type': 'application/json'}, 
                body: JSON.stringify({code: studentId, type: 'qr', period: 'scan'})
            })
            .then(r => r.json())
            .then(data => {
                if(data.success){
                    displayStudentInfo(data);
                    // Notify parent to refresh attendance
                    if(window.parent){
                        window.parent.postMessage('refreshAttendance', '*');
                    }
                } else {
                    last.innerHTML = '<div style="color: red; font-weight: bold;">❌ ' + 
                                   (data.error === 'Student not found' ? 'No student associated with this student ID.' : 'Error: ' + data.error) +
                                   '</div>';
                    setTimeout(() => { 
                        last.innerHTML = ''; 
                        last.style.display = 'none'; 
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Attendance recording error:', error);
                last.innerHTML = '<div style="color: red; font-weight: bold;">❌ Network error occurred</div>';
                setTimeout(() => { 
                    last.innerHTML = ''; 
                    last.style.display = 'none'; 
                }, 5000);
            });
        }

        function extractStudentId(code) {
            // Function to extract student ID from various QR code formats
            
            // Check if it's a formatted student information block
            if (code.includes('=== STUDENT INFORMATION ===') || code.includes('Student ID:')) {
                // Extract student ID from formatted text
                const lines = code.split('\n');
                for (let line of lines) {
                    if (line.startsWith('Student ID:')) {
                        const studentId = line.replace('Student ID:', '').trim();
                        console.log('Extracted student ID from formatted QR:', studentId);
                        return studentId;
                    }
                }
                console.warn('Could not find Student ID in formatted QR code');
                return null;
            }
            
            // Check if it's a URL (starts with http:// or https://)
            if (code.toLowerCase().startsWith('http://') || code.toLowerCase().startsWith('https://')) {
                try {
                    const url = new URL(code);
                    
                    // Check for our QR redirect format: qr.php?s=STUDENTID
                    if (url.pathname.includes('qr.php') && url.searchParams.has('s')) {
                        const studentId = url.searchParams.get('s');
                        console.log('Extracted student ID from QR URL:', studentId);
                        return studentId;
                    }
                    
                    // Check for direct student_info format: student_info.php?id=STUDENTID
                    if (url.pathname.includes('student_info.php') && url.searchParams.has('id')) {
                        const studentId = url.searchParams.get('id');
                        console.log('Extracted student ID from student_info URL:', studentId);
                        return studentId;
                    }
                    
                    // If it's a URL but not our format, show error
                    console.warn('Unknown URL format in QR code:', code);
                    return null;
                } catch (e) {
                    console.error('Invalid URL in QR code:', code, e);
                    return null;
                }
            }
            
            // If it's not a URL, assume it's a direct student ID (legacy format)
            if (code && code.trim().length > 0) {
                console.log('Using direct student ID:', code.trim());
                return code.trim();
            }
            
            // Invalid or empty code
            console.warn('Empty or invalid QR code:', code);
            return null;
        }

        // QR scanner initialization
        function initializeScanner() {
            // Check if Html5Qrcode library is available
            if (typeof Html5Qrcode === 'undefined') {
                console.error('Html5Qrcode library not loaded');
                showError('❌ Library Error', 'QR code scanner library failed to load. Please refresh the page or clear browser cache.');
                return;
            }
            
            html5Qrcode = new Html5Qrcode("reader");
            Html5Qrcode.getCameras().then(cameras => {
                if(cameras && cameras.length){
                    cameraId = cameras[0].id;
                    console.log('Camera found:', cameraId);
                    startScanning();
                } else {
                    console.error('No cameras found');
                    showError('❌ No camera detected', 'Your device does not have a camera or it was not detected. Please try again.');
                }
            }).catch(err=>{
                console.error('Camera initialization error:', err);
                
                // Handle specific error messages
                if (err.name === 'NotAllowedError' || err.toString().includes('Permission')) {
                    showError('📷 Camera Permission Denied', 'Please enable camera permission and refresh the page. You can also go to Settings to enable it.');
                } else if (err.name === 'NotFoundError') {
                    showError('❌ No Camera Found', 'No camera device was found on this device.');
                } else if (err.name === 'NotReadableError') {
                    showError('❌ Camera Error', 'Your camera is in use by another application. Please close it and try again.');
                } else {
                    showError('❌ Camera Error', 'Unable to access camera. Error: ' + err.message + '. Try refreshing the page or checking browser permissions.');
                }
            });
        }
        
        function showError(title, message) {
            errorVibration();
            last.innerHTML = '<div style="color: red; font-weight: bold; font-size: 1.1em; margin-bottom: 12px;">' + title + '</div>' +
                           '<div style="color: #666; font-size: 0.95em; line-height: 1.6;">' + message + '</div>' +
                           '<div style="margin-top: 20px;"><button onclick="location.reload()" style="padding: 12px 24px; background: #218c21; color: white; border: none; border-radius: 8px; font-size: 1em; cursor: pointer;">🔄 Refresh</button></div>';
            last.style.display = 'block';
        }
        
        function startScanning() {
            if (isScanning || !html5Qrcode || !cameraId) return;
            
            const config = {
                fps: 10,
                qrbox: isMobile ? { width: 250, height: 250 } : 250,
                aspectRatio: 1.0,
                disableFlip: false
            };
            
            html5Qrcode.start(cameraId, config, (decodedText, decodedResult) => {
                // Successfully decoded QR code
                successVibration();
                record(decodedText, 'qr');
            }, (errorMessage) => {
                // Ignore scanning errors
            }).then(() => {
                isScanning = true;
                console.log('QR Code scanning started');
                last.innerHTML = '';
                last.style.display = 'none';
            }).catch(err => {
                console.error('Failed to start scanning:', err);
                errorVibration();
                
                // Handle permission errors
                if (err.name === 'NotAllowedError' || err.toString().includes('Permission')) {
                    showError('📷 Camera Access Required', 'The app needs camera permission to scan QR codes. Please check your device settings.');
                } else {
                    showError('❌ Camera Error', 'Failed to start camera: ' + err.message);
                }
            });
        }
        
        function stopScanning() {
            if (!isScanning || !html5Qrcode) return;
            
            html5Qrcode.stop().then(() => {
                isScanning = false;
                console.log('QR Code scanning stopped');
                last.innerHTML = '';
                last.style.display = 'none';
            }).catch(err => {
                console.error('Failed to stop scanning:', err);
            });
        }
        
        function toggleCamera() {
            if (isScanning) {
                stopScanning();
                document.getElementById('toggleCamera').textContent = '📷 Start Camera';
            } else {
                startScanning();
                document.getElementById('toggleCamera').textContent = '⏹ Stop Camera';
            }
        }
        
        // Mobile control buttons
        if (isMobile) {
            document.getElementById('toggleCamera').addEventListener('click', toggleCamera);
            document.getElementById('stopScan').addEventListener('click', () => {
                stopScanning();
                last.innerHTML = '';
                last.style.display = 'none';
            });
        }

        // Initialize scanner on page load
        window.addEventListener('load', () => {
            loadHtml5QrcodeLibrary(initializeScanner);
        });

        // Barcode input (hardware scanner will type then send Enter)

        const input = document.getElementById('barcodeInput');
        let buffer = '';
        input.addEventListener('input', e=>{
            const val = input.value.trim();
            // If input is at least 6 chars (typical student ID), auto-validate
            if(val.length >= 6){
                record(val, 'barcode');
                input.value = '';
            }
        });

    </script>
</body>
</html>