<?php
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scan - Attendance</title>
    <script src="html5-qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: linear-gradient(135deg, #f8fff8 0%, #eaffea 100%);
            min-height: 100vh;
        }
        
        h1 {
            color: #218c21;
            text-align: center;
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
    <h1>🤖 Automatic Attendance Scanner</h1>
    
    <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #218c21;">
        <strong>⚡ Auto-Record Mode Active:</strong> This scanner automatically records attendance when QR codes are scanned! 
        The system extracts the student ID from any QR code format, validates it against the database, and records attendance 
        using the exact scan timestamp. No manual buttons needed - just scan and go!
    </div>
    
    <div style="background: #fff3cd; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f0ad4e;">
        <strong>📋 Automatic Process:</strong> 
        QR Code Scanned → Student ID Extracted → Database Validated → Attendance Recorded → Confirmation Displayed
    </div>
    
    <div class="scanner-container">
        <div class="scanner-section">
            <h3>📷 QR Code Scanner</h3>
            <div id="reader" style="width:400px"></div>
            <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 6px; font-size: 0.9em; color: #856404;">
                <strong>🎯 Auto-Record Mode:</strong> Point camera at student's QR code. The system will automatically extract the student ID, 
                validate it in the database, and record attendance immediately using the scan timestamp.
            </div>
        </div>
        <div class="scanner-section">
            <h3>⌨️ Manual Barcode Input</h3>
            <p>If you have a hardware barcode scanner, focus the input below and scan — it will auto-submit.</p>
            <input id="barcodeInput" placeholder="Scan or type student ID / barcode" />
            <div style="margin-top: 10px; padding: 10px; background: #e3f2fd; border-radius: 6px; font-size: 0.9em; color: #1976d2;">
                <strong>⚡ Quick Entry:</strong> Enter student ID directly or use barcode scanner. System will automatically validate 
                and record attendance for valid student IDs.
            </div>
        </div>
    </div>
    
    <div id="last"></div>

    <p><a href="index.php">Home</a></p>

    <script>
        const last = document.getElementById('last');

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
                last.innerHTML = '<div style="color: red; font-weight: bold;">❌ Invalid QR Code Format</div>' +
                                '<div style="color: #666; font-size: 0.9em; margin-top: 5px;">Unable to extract student ID from: ' + 
                                (code.length > 50 ? code.substring(0, 50) + '...' : code) + '</div>';
                last.style.display = 'block';
                setTimeout(() => { 
                    last.innerHTML = ''; 
                    last.style.display = 'none'; 
                }, 5000);
                return;
            }
            
            // Show processing feedback with extracted student ID
            last.innerHTML = '<div style="color: #218c21; font-weight: bold;">🔍 Validating Student ID: ' + studentId + '</div>' +
                            '<div style="color: #666; font-size: 0.9em; margin-top: 5px;">Checking database and recording attendance...</div>';
            last.style.display = 'block';
            
            const period = 'scan'; // Default period for simple scanning
            console.log('Auto-recording attendance for student ID:', studentId);
            
            fetch('record_attendance.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({code:studentId, type:type, period:period})})
            .then(r => r.json())
            .then(data => {
                if(data.success){
                    // Auto-record successful - show confirmation
                    displayAutoRecordedAttendance(data, studentId);
                    // Notify parent to refresh attendance
                    if(window.parent){
                        window.parent.postMessage('refreshAttendance', '*');
                    }
                } else {
                    // Show error message
                    last.innerHTML = '<div style="color: red; font-weight: bold;">❌ Attendance Recording Failed</div>' +
                                   '<div style="color: #666; font-size: 0.9em; margin-top: 5px;"><strong>Student ID:</strong> ' + studentId + '</div>' +
                                   '<div style="color: red; font-size: 0.9em;"><strong>Error:</strong> ' + 
                                   (data.error === 'Student not found' ? 'Student ID not found in database' : data.error) + '</div>';
                    last.style.display = 'block';
                    
                    // Auto-hide error after 8 seconds
                    setTimeout(()=> { 
                        last.innerHTML = ''; 
                        last.style.display = 'none'; 
                    }, 8000);
                }
            })
            .catch(e => {
                last.textContent = 'Request error';
                last.style.color = 'red';
                last.style.display = 'block';
                setTimeout(()=> { last.textContent = ''; last.style.display = 'none'; }, 5000);
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
                        <h4 style="margin: 0 0 10px 0; color: #ecf0f1;">⏰ Attendance Recorded</h4>
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
                        <div style="color: #ecf0f1;"><strong>🤖 Auto-Processing Complete</strong></div>
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
                    <div style="font-size: 2em; margin-bottom: 15px;">📱</div>
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
            last.innerHTML = '<div style="color: #218c21; font-weight: bold;">🔍 Recording attendance for: ' + studentId + '</div>';
            
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

        // QR scanner
    const html5Qrcode = new Html5Qrcode("reader");
    Html5Qrcode.getCameras().then(cameras => {
            if(cameras && cameras.length){
                const cameraId = cameras[0].id;
                html5Qrcode.start(cameraId, { fps: 10, qrbox: 250 }, (decodedText, decodedResult) => {
                    // decodedText is the code we encoded earlier
                    record(decodedText, 'qr');
                }, (errorMessage) => {
                    // ignore
                }).catch(err => {
                    console.error(err);
                });
            }
        }).catch(err=>{
            console.error('Camera error', err);
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