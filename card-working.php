<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
$pdo = get_db();
$id = intval($_GET['id'] ?? 0);
// Try students table first
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
$stmt->execute([':id'=>$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$user){ echo "Student not found"; exit; }
$code = $user['student_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student QR Card - <?php echo htmlspecialchars($user['full_name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        
        .card-container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(33, 140, 33, 0.2);
            padding: 40px;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        
        .student-header {
            border-bottom: 3px solid #218c21;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .student-header h1 {
            color: #218c21;
            font-size: 2.2em;
            margin: 0 0 10px 0;
            font-weight: 700;
        }
        
        .student-info {
            background: linear-gradient(135deg, #f0fff0 0%, #e8ffe8 100%);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border-left: 5px solid #218c21;
        }
        
        .student-info p {
            margin: 8px 0;
            color: #176617;
            font-weight: 600;
            font-size: 1.1em;
        }
        
        .qr-section {
            margin: 30px 0;
        }
        
        .qr-section h3 {
            color: #218c21;
            font-size: 1.5em;
            margin-bottom: 20px;
        }
        
        .qr-display {
            border: 3px dashed #218c21;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            background: #f8f9fa;
        }
        
        #qr-container {
            display: inline-block;
            border: 3px solid #218c21;
            border-radius: 10px;
            padding: 10px;
            background: white;
            margin: 10px 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        
        .btn {
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #218c21;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1a6b1a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 140, 33, 0.3);
        }
        
        .btn-blue {
            background: #007bff;
            color: white;
        }
        
        .btn-blue:hover {
            background: #0056b3;
        }
        
        .btn-orange {
            background: #fd7e14;
            color: white;
        }
        
        .btn-orange:hover {
            background: #e8681a;
        }
        
        .btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            font-weight: bold;
        }
        
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        .warning { background: #fff3cd; color: #856404; }
        
        .method-indicator {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .method-binary {
            background: #28a745;
            color: white;
        }
        
        .instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: left;
            border-left: 5px solid #218c21;
        }
        
        .instructions h4 {
            color: #218c21;
            margin-top: 0;
        }
        
        .footer-links {
            margin: 20px 0;
            padding: 20px 0;
            border-top: 2px solid #e9ecef;
        }
        
        .footer-links a {
            color: #218c21;
            text-decoration: none;
            margin: 0 15px;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .footer-links a:hover {
            background: #e8f5e8;
        }
        
        @media (max-width: 600px) {
            .card-container {
                margin: 10px;
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Local vendored QRCode library (offline support) -->
    <script src="assets/qrcode.min.js"></script>
    <div class="card-container">
        <div class="student-header">
            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p style="color: #666; font-size: 1.1em; margin: 0;">Student QR Code Card</p>
        </div>
        
        <div class="student-info">
            <h4 style="color: #218c21; margin-top: 0;">📊 Student Information</h4>
            
            <!-- Essential Student Data -->
            <p><strong>🆔 Student ID:</strong> <?php echo htmlspecialchars($user['student_id']); ?></p>
            <p><strong>📚 LRN:</strong> <?php echo htmlspecialchars($user['lrn'] ?? 'N/A'); ?></p>
            <p><strong>� Full Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            
            <!-- Contact Information -->
            <p><strong>�📧 Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>

            

            
            <p><strong>👤 Full Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p><strong>🎓 Grade Level:</strong> <?php echo htmlspecialchars($user['grade_level']); ?></p>
            <p><strong>🎯 Strand:</strong> <?php echo htmlspecialchars($user['strand']); ?></p>
            <p><strong>📋 Section/Block:</strong> <?php echo htmlspecialchars($user['section_block']); ?></p>
            <p><strong>⚧️ Gender:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
            

            
            <div style="background: #d1ecf1; padding: 10px; border-radius: 5px; margin-top: 15px; font-size: 0.9em;">
                <strong>🔒 QR Security:</strong> Essential student information encoded in binary QR code for secure attendance tracking.
            </div>
        </div>
        
        <div class="qr-section">
            <h3>⚡ Student QR Code <span class="method-indicator method-binary" id="qrModeTag">Standard</span></h3>
            <div class="qr-display">
                <div id="qrNotice" style="font-size:12px;color:#0c5460;background:#d1ecf1;padding:8px;border-radius:6px;margin-bottom:10px;line-height:1.3;">
                    📢 <strong>Standard Mode:</strong> This QR is compliant and scannable by normal camera / QR apps. Payload is a compact encoded JSON.
                </div>
                <div id="qr-container">
                    <!-- QR code will be generated here -->
                </div>
                <div id="status" class="status info">Ready to generate QR code...</div>
            </div>
        </div>
        
        <div class="action-buttons">
            <button onclick="generateQR()" class="btn btn-primary" id="genBtn">
                ⚡ Generate QR
            </button>
            <button onclick="toggleMode()" class="btn" style="background:#6c757d;color:#fff;" id="modeBtn">🔁 Switch to Binary</button>
            <button onclick="downloadPNG()" class="btn btn-blue" id="pngBtn" disabled>
                🖼️ Download PNG Image
            </button>
            <button onclick="downloadPDF()" class="btn btn-orange" id="pdfBtn" disabled>
                📄 Download PDF Card
            </button>
        </div>
        
        <div style="margin: 20px 0; text-align: center;">
            <h4>Alternative Methods (if above fails):</h4>
            <button onclick="openQRWindow()" class="btn" style="background: #6c757d; margin: 5px;">
                🪟 Open QR in New Window
            </button>
            <button onclick="showQRAsImage()" class="btn" style="background: #6c757d; margin: 5px;">
                🖼️ Show as Image
            </button>
        </div>
        
        <div class="instructions">
            <h4>⚡ Pure Binary QR Features</h4>
            <ul>
                <li><strong>🧬 Pure Binary Matrix:</strong> Custom pattern generated from full JSON payload</li>
                <li><strong>🔐 Integrity Hash:</strong> Embedded hash for tamper detection</li>
                <li><strong>🧾 Complete Record:</strong> All available student fields encoded</li>
                <li><strong>🎯 Deterministic:</strong> Same data always produces same pattern</li>
                <li><strong>🚫 Not Public-Scannable:</strong> Requires custom decoder (non-standard)</li>
                <li><strong>⚠️ Recommendation:</strong> Keep a standard QR for interoperability</li>
            </ul>
            
            <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin-top: 15px;">
                <strong>� Binary Technology:</strong> This QR code uses advanced binary matrix generation with hash-based encoding. 
                Contains: Student ID, LRN, Full Name, Grade Level, Strand, Section/Block, Gender, and Email Address.
            </div>
        </div>
        
        <div class="footer-links">
            <a href="student_dashboard.php">← Back to Dashboard</a>
            <a href="scan.php">📷 Open Scanner</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <script>
        let qrImageData = null;
        let librariesLoaded = {
            qrcode: false,
            jspdf: false
        };
        let qrCodeLoadAttempted = false;
        let qrCodeLoadPromise = null;
        
        let qrGenerationMethod = 'binary'; // Always use binary generation
        
        // COMPLETE student data (all available database fields) for Pure Binary QR
        const studentData = {
            // Primary identifiers
            database_id: "<?php echo htmlspecialchars($user['id'] ?? ''); ?>",
            student_id: "<?php echo htmlspecialchars($user['student_id'] ?? ''); ?>",
            lrn: "<?php echo htmlspecialchars($user['lrn'] ?? ''); ?>",
            // Personal
            full_name: "<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>",
            email: "<?php echo htmlspecialchars($user['email'] ?? ''); ?>",
            gender: "<?php echo htmlspecialchars($user['gender'] ?? ''); ?>",
            contact_number: "<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>",
            address: "<?php echo htmlspecialchars($user['address'] ?? ''); ?>",
            birth_date: "<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>",
            parent_guardian: "<?php echo htmlspecialchars($user['parent_guardian'] ?? ''); ?>",
            emergency_contact: "<?php echo htmlspecialchars($user['emergency_contact'] ?? ''); ?>",
            // Academic
            grade_level: "<?php echo htmlspecialchars($user['grade_level'] ?? ''); ?>",
            strand: "<?php echo htmlspecialchars($user['strand'] ?? ''); ?>",
            section_block: "<?php echo htmlspecialchars($user['section_block'] ?? ''); ?>",
            enrollment_status: "<?php echo htmlspecialchars($user['enrollment_status'] ?? 'Active'); ?>",
            // System
            created_at: "<?php echo htmlspecialchars($user['created_at'] ?? ''); ?>",
            updated_at: "<?php echo htmlspecialchars($user['updated_at'] ?? ''); ?>",
            // Institution / metadata
            school: "Palawan National School",
            school_year: "2024-2025",
            qr_generated_at: new Date().toISOString(),
            schema_version: "2.0"
        };

        // Load libraries with multiple fallbacks
        function loadLibraries() {
            console.log('Loading libraries...');
            
            // Load QRCode.js
            loadScript('https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js')
                .then(() => {
                    if (typeof QRCode !== 'undefined') {
                        librariesLoaded.qrcode = true;
                        console.log('✅ QRCode.js loaded successfully');
                    } else {
                        return loadScript('https://unpkg.com/qrcode@1.5.3/build/qrcode.min.js');
                    }
                })
                .then(() => {
                    if (typeof QRCode !== 'undefined') {
                        librariesLoaded.qrcode = true;
                        console.log('✅ QRCode.js loaded from alternative CDN');
                    }
                })
                .catch(() => {
                    console.log('❌ QRCode.js failed to load, using fallback');
                    librariesLoaded.qrcode = 'fallback';
                });

            // Load jsPDF with multiple attempts
            loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js')
                .then(() => {
                    if (typeof window.jsPDF !== 'undefined') {
                        librariesLoaded.jspdf = true;
                        console.log('✅ jsPDF loaded successfully');
                    } else {
                        return loadScript('https://unpkg.com/jspdf@2.5.1/dist/jspdf.umd.min.js');
                    }
                })
                .then(() => {
                    if (typeof window.jsPDF !== 'undefined') {
                        librariesLoaded.jspdf = true;
                        console.log('✅ jsPDF loaded from alternative CDN');
                    } else {
                        return loadScript('https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js');
                    }
                })
                .then(() => {
                    if (typeof window.jsPDF !== 'undefined') {
                        librariesLoaded.jspdf = true;
                        console.log('✅ jsPDF loaded from third CDN');
                    }
                })
                .catch(() => {
                    console.log('❌ jsPDF failed to load from all CDNs');
                    librariesLoaded.jspdf = false;
                });
        }

        // Ensure QRCode.js is available before generating
        function ensureQRCodeLibrary(timeoutMs = 6000){
            if (typeof QRCode !== 'undefined') {
                librariesLoaded.qrcode = true;
                return Promise.resolve(true);
            }
            if (qrCodeLoadPromise) return qrCodeLoadPromise; // reuse in-flight
            qrCodeLoadAttempted = true;
            const cdns = [
                // Local first
                'assets/qrcode.min.js',
                'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js',
                'https://unpkg.com/qrcode@1.5.3/build/qrcode.min.js',
                'https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js' // fallback alt lib (different API)
            ];
            let idx = 0;
            qrCodeLoadPromise = new Promise((resolve)=>{
                const start = Date.now();
                function tryNext(){
                    if (typeof QRCode !== 'undefined'){ librariesLoaded.qrcode = true; return resolve(true); }
                    if (idx >= cdns.length){
                        return resolve(false);
                    }
                    const src = cdns[idx++];
                    loadScript(src).then(()=>{
                        setTimeout(()=>{ // allow script to execute
                            if (typeof QRCode !== 'undefined'){ librariesLoaded.qrcode = true; resolve(true); }
                            else tryNext();
                        },100);
                    }).catch(()=>{
                        tryNext();
                    });
                }
                function poll(){
                    if (typeof QRCode !== 'undefined'){ librariesLoaded.qrcode = true; return resolve(true); }
                    if (Date.now() - start > timeoutMs){ return resolve(false); }
                    setTimeout(poll,120);
                }
                tryNext();
                poll();
            });
            return qrCodeLoadPromise;
        }

        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        function updateStatus(msg, type = 'info') {
            const el = document.getElementById('status');
            el.className = `status ${type}`;
            el.textContent = msg;
        }

        // Binary QR generation is always enabled
        function initializeBinaryMode() {
            console.log('� Binary QR Generation initialized');
            updateStatus('⚡ Binary QR Generation ready - Advanced encoding enabled', 'info');
        }

        let currentMode = 'standard'; // 'standard' | 'binary'

        function toggleMode(){
            currentMode = currentMode === 'standard' ? 'binary' : 'standard';
            const tag = document.getElementById('qrModeTag');
            const notice = document.getElementById('qrNotice');
            const modeBtn = document.getElementById('modeBtn');
            if(currentMode === 'standard'){
                tag.textContent = 'Standard';
                notice.style.background = '#d1ecf1';
                notice.style.color = '#0c5460';
                notice.innerHTML = '📢 <strong>Standard Mode:</strong> Scannable by normal QR apps. Compact encoded JSON.';
                modeBtn.textContent = '🔁 Switch to Binary';
            } else {
                tag.textContent = 'Binary';
                notice.style.background = '#fff3cd';
                notice.style.color = '#856404';
                notice.innerHTML = '⚠️ <strong>Binary Mode:</strong> Custom non-standard matrix. NOT scannable by generic apps.';
                modeBtn.textContent = '🔁 Switch to Standard';
            }
            generateQR();
        }

        async function generateQR(){
            if(currentMode === 'standard'){
                await generateStandardQR();
            } else {
                await generateFullBinaryQR();
            }
        }

        async function generateStandardQR(){
            const btn = document.getElementById('genBtn');
            btn.disabled = true;
            try {
                updateStatus('🛠️ Generating scannable QR with FULL student record...', 'info');

                // Wait for library (retry if not yet loaded)
                const available = await ensureQRCodeLibrary();
                if(!available){
                    updateStatus('❌ Could not load QR library from CDNs. Showing fallback pattern (NOT scannable).', 'error');
                    generateFallbackQR(JSON.stringify(studentData));
                    return; // exit early
                }

                // Build full payload (ALL fields) to embed in scannable QR
                // Keep a metadata wrapper so a scanner/receiver can identify structure.
                const fullPayload = {
                    type: 'pns_student_full',
                    version: '1.0',
                    generated_at: new Date().toISOString(),
                    student: { ...studentData }
                };

                let json = JSON.stringify(fullPayload); // full verbose JSON

                // Optional size guard: if very large, shrink keys (basic minification step)
                if (json.length > 2500) {
                    const s = studentData;
                    const compact = {
                        t:'pns',v:1,ts:Date.now(),
                        s:{
                            id:s.student_id,db:s.database_id,lrn:s.lrn,fn:s.full_name,em:s.email,gn:s.gender,
                            cn:s.contact_number,ad:s.address,bd:s.birth_date,pg:s.parent_guardian,ec:s.emergency_contact,
                            gl:s.grade_level,st:s.strand,sc:s.section_block,en:s.enrollment_status,
                            ca:s.created_at,ua:s.updated_at,sy:s.school_year,sch:s.school
                        }
                    };
                    json = JSON.stringify(compact);
                }

                // Add checksum and base64 encode for transport safety
                const checksum = hashString(json).toString(36);
                const b64 = btoa(unescape(encodeURIComponent(json)));
                const packed = 'PNSFULL|' + b64 + '|' + checksum;
                console.log('Full QR JSON length:', json.length, 'Packed length:', packed.length);

                const container = document.getElementById('qr-container');
                container.innerHTML = '';
                if(librariesLoaded.qrcode && typeof QRCode !== 'undefined'){
                    const canvas = document.createElement('canvas');
                    await new Promise((res, rej)=>{
                        QRCode.toCanvas(
                            canvas,
                            packed,
                            {
                                width: 300,
                                errorCorrectionLevel: json.length > 1200 ? 'L' : 'M' // allow more capacity if big
                            },
                            err=> err?rej(err):res()
                        );
                    });
                    qrImageData = canvas.toDataURL('image/png');
                    container.appendChild(canvas);
                    const meta = document.createElement('div');
                    meta.style.cssText='font-size:11px;color:#218c21;margin-top:8px;font-weight:bold;text-align:center;';
                    meta.textContent='✅ Scannable FULL Student QR';
                    container.appendChild(meta);
                    const info = document.createElement('div');
                    info.style.cssText='font-size:9px;color:#555;margin-top:4px;text-align:center;';
                    info.textContent=`Fields: ${Object.keys(studentData).length} • JSON: ${json.length} chars`;
                    container.appendChild(info);
                    updateStatus('✅ Full student record QR ready.', 'success');
                } else {
                    updateStatus('❌ QR library still unavailable after attempts. Rendering fallback.', 'error');
                    generateFallbackQR(json);
                }
                document.getElementById('pngBtn').disabled = false;
                document.getElementById('pdfBtn').disabled = false;
            } catch(e){
                console.error(e);
                updateStatus('❌ Full record QR failed: '+e.message, 'error');
            } finally {
                btn.disabled = false;
            }
        }

        async function generateFullBinaryQR(){
            const btn = document.getElementById('genBtn');
            btn.disabled = true;
            try {
                updateStatus('🧬 Generating pure binary matrix...', 'info');
                const fullPayload = {
                    meta:{type:'pns_student_record',mode:'binary',ver:'2.0',ts:new Date().toISOString()},
                    student:{...studentData},
                    integrity:{hash:hashString(JSON.stringify(studentData))}
                };
                await generateBinaryQR(JSON.stringify(fullPayload), fullPayload);
                updateStatus('✅ Binary matrix generated (non-standard).', 'success');
                document.getElementById('pngBtn').disabled = false;
                document.getElementById('pdfBtn').disabled = false;
            } catch(e){
                console.error(e);
                updateStatus('❌ Binary generation failed: '+e.message,'error');
            } finally { btn.disabled = false; }
        }

        // Pure Binary Matrix Generation (custom, non-QR) that truly maps every bit of JSON payload.
        async function generateBinaryQR(rawJson, fullPayload) {
            console.log('🔬 Pure Binary encoding start');
            updateStatus('🧬 Encoding full JSON into custom binary matrix…', 'info');

            // Convert JSON to bytes
            const encoder = new TextEncoder();
            const bytes = encoder.encode(rawJson);
            const bitCount = bytes.length * 8;

            // Compute matrix size: reserve 8px margin & finder-like markers (7x7) in 3 corners.
            // We pack bits row-wise skipping reserved areas.
            function estimateSize(bits) {
                // Let usable area ~ (n-16)^2 (rough heuristic). Solve (n-16)^2 >= bits.
                let n = 33; // minimum to fit three 7x7 markers + spacing
                while ((n - 16) * (n - 16) < bits) n += 2; // grow by 2 to keep odd/even pattern stable
                return n;
            }
            const matrixSize = estimateSize(bitCount);
            console.log('📐 Matrix size chosen:', matrixSize, 'for', bitCount, 'bits');

            // Build empty matrix
            const matrix = Array.from({ length: matrixSize }, () => Array(matrixSize).fill(0));

            // Draw 3 corner markers (simple 7x7 frames) similar to QR for orientation (not standard)
            function drawMarker(x0, y0) {
                for (let y = 0; y < 7; y++) {
                    for (let x = 0; x < 7; x++) {
                        const edge = (x === 0 || y === 0 || x === 6 || y === 6);
                        const inner = (x >= 2 && x <= 4 && y >= 2 && y <= 4);
                        matrix[y0 + y][x0 + x] = (edge || inner) ? 1 : 0;
                    }
                }
            }
            drawMarker(0, 0);
            drawMarker(matrixSize - 7, 0);
            drawMarker(0, matrixSize - 7);

            // Reserve area occupied by markers & 1 pixel spacing around them when placing data.
            function isReserved(x, y) {
                // Padding of 1 cell around markers
                const inTopLeft = x <= 7 && y <= 7;
                const inTopRight = x >= matrixSize - 8 && y <= 7;
                const inBottomLeft = x <= 7 && y >= matrixSize - 8;
                return inTopLeft || inTopRight || inBottomLeft;
            }

            // Write bits sequentially
            let bitIndex = 0;
            for (let byte of bytes) {
                for (let b = 7; b >= 0; b--) {
                    const bit = (byte >> b) & 1;
                    // Find next free cell
                    while (bitIndex < matrixSize * matrixSize) {
                        const y = Math.floor(bitIndex / matrixSize);
                        const x = bitIndex % matrixSize;
                        bitIndex++;
                        if (!isReserved(x, y)) {
                            matrix[y][x] = bit;
                            break;
                        }
                    }
                }
            }

            // Add simple footer checksum row (XOR of each column) at bottom if space free
            if (matrixSize > 16) {
                const checksumRow = matrixSize - 1;
                for (let x = 0; x < matrixSize; x++) {
                    let colXor = 0;
                    for (let y = 0; y < matrixSize - 1; y++) colXor ^= matrix[y][x];
                    matrix[checksumRow][x] = colXor;
                }
            }

            // Render to canvas (scale to max 320px)
            const maxRender = 320;
            const moduleSize = Math.floor(maxRender / matrixSize);
            const renderSize = moduleSize * matrixSize;
            const canvas = document.createElement('canvas');
            canvas.width = renderSize;
            canvas.height = renderSize;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, renderSize, renderSize);
            ctx.fillStyle = '#000000';
            for (let y = 0; y < matrixSize; y++) {
                for (let x = 0; x < matrixSize; x++) {
                    if (matrix[y][x]) ctx.fillRect(x * moduleSize, y * moduleSize, moduleSize, moduleSize);
                }
            }

            qrImageData = canvas.toDataURL('image/png');

            const container = document.getElementById('qr-container');
            container.innerHTML = '';
            container.appendChild(canvas);
            const metaDiv = document.createElement('div');
            metaDiv.style.cssText = 'font-size:11px;color:#28a745;margin-top:8px;font-weight:bold;text-align:center;';
            metaDiv.textContent = '⚡ Pure Binary Full Record Matrix';
            container.appendChild(metaDiv);
            const infoDiv = document.createElement('div');
            infoDiv.style.cssText = 'font-size:9px;color:#555;margin-top:4px;line-height:1.3;text-align:center;';
            infoDiv.innerHTML = `Size: ${matrixSize}x${matrixSize} • Bytes: ${bytes.length} • Bits: ${bitCount}`;
            container.appendChild(infoDiv);

            console.log('✅ Pure binary matrix generated');
            console.log({ matrixSize, bytes: bytes.length, bits: bitCount });
        }
        
        // Hash string to number for binary encoding
        function hashString(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32-bit integer
            }
            return Math.abs(hash);
        }

            function generateFallbackQR(data = null) {
            console.log('Using fallback QR method');
            
            const qrData = data || JSON.stringify(studentData);
            
            // Create a simple QR-like pattern using SVG
            const svgQR = `
                <svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
                    <rect width="300" height="300" fill="white"/>
                    <!-- Finder patterns -->
                    <rect x="20" y="20" width="60" height="60" fill="black"/>
                    <rect x="30" y="30" width="40" height="40" fill="white"/>
                    <rect x="40" y="40" width="20" height="20" fill="black"/>
                    
                    <rect x="220" y="20" width="60" height="60" fill="black"/>
                    <rect x="230" y="30" width="40" height="40" fill="white"/>
                    <rect x="240" y="40" width="20" height="20" fill="black"/>
                    
                    <rect x="20" y="220" width="60" height="60" fill="black"/>
                    <rect x="30" y="230" width="40" height="40" fill="white"/>
                    <rect x="40" y="240" width="20" height="20" fill="black"/>
                    
                    <!-- Decorative pattern only (NOT SCANNABLE) -->
                    <rect x="100" y="100" width="10" height="10" fill="black"/>
                    <rect x="120" y="100" width="10" height="10" fill="black"/>
                    <rect x="140" y="100" width="10" height="10" fill="black"/>
                    <rect x="100" y="120" width="10" height="10" fill="black"/>
                    <rect x="140" y="120" width="10" height="10" fill="black"/>
                    <rect x="100" y="140" width="10" height="10" fill="black"/>
                    <rect x="120" y="140" width="10" height="10" fill="black"/>
                    <rect x="140" y="140" width="10" height="10" fill="black"/>
                    <text x="110" y="170" font-family="Arial" font-size="8" fill="black">FALLBACK</text>
                </svg>
            `;

            // Convert SVG to DataURL
            const svgBlob = new Blob([svgQR], { type: 'image/svg+xml' });
            const svgUrl = URL.createObjectURL(svgBlob);
            
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = 300;
                canvas.height = 300;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                
                qrImageData = canvas.toDataURL('image/png');
                
                const container = document.getElementById('qr-container');
                container.innerHTML = `<img src="${qrImageData}" alt="Fallback QR Code" style="max-width: 300px; height: auto;" />`;
                
                updateStatus('✅ Fallback QR generated!', 'success');
                document.getElementById('pngBtn').disabled = false;
                document.getElementById('pdfBtn').disabled = false;
                
                URL.revokeObjectURL(svgUrl);
            };
            img.src = svgUrl;
        }

        function downloadPNG() {
            if (!qrImageData) {
                alert('Please generate QR code first');
                return;
            }

            try {
                console.log('Starting PNG download...');
                
                const link = document.createElement('a');
                link.href = qrImageData;
                link.download = `${studentData.id}-QR-${Date.now()}.png`;
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                updateStatus('✅ PNG download completed!', 'success');
                console.log('PNG download successful');

            } catch (error) {
                console.error('PNG download failed:', error);
                updateStatus('❌ PNG download failed. Try alternative methods below.', 'error');
            }
        }

        function downloadPDF() {
            if (!qrImageData) {
                alert('Please generate QR code first');
                return;
            }

            try {
                console.log('Creating PDF...');
                
                if (!librariesLoaded.jspdf || typeof window.jsPDF === 'undefined') {
                    updateStatus('Loading PDF library...', 'info');
                    
                    return loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js')
                        .then(() => {
                            if (typeof window.jsPDF !== 'undefined') {
                                librariesLoaded.jspdf = true;
                                return downloadPDF();
                            } else {
                                throw new Error('jsPDF still not available after reload');
                            }
                        })
                        .catch(() => {
                            updateStatus('❌ PDF library failed. Using browser print instead.', 'error');
                            openPrintablePDF();
                        });
                }

                const { jsPDF } = window.jsPDF;
                const pdf = new jsPDF();

                // Header
                pdf.setFillColor(33, 140, 33);
                pdf.rect(0, 0, 210, 30, 'F');
                
                pdf.setTextColor(255, 255, 255);
                pdf.setFontSize(18);
                pdf.setFont('helvetica', 'bold');
                pdf.text('PALAWAN NATIONAL SCHOOL', 105, 20, { align: 'center' });

                // Student info
                pdf.setTextColor(0, 0, 0);
                pdf.setFontSize(14);
                pdf.text('Complete Student Database Record', 105, 45, { align: 'center' });

                let y = 65;
                
                // Complete database information
                const completeInfo = [
                    // Primary Information
                    ['Student ID:', studentData.student_id],
                    ['Full Name:', studentData.full_name],
                    ['LRN:', studentData.lrn],
                    ['Database ID:', studentData.database_id],
                    
                    // Personal Details
                    ['Email:', studentData.email],
                    ['Gender:', studentData.gender],
                    ['Contact:', studentData.contact_number || 'N/A'],
                    ['Address:', studentData.address || 'N/A'],
                    ['Birth Date:', studentData.birth_date || 'N/A'],
                    ['Parent/Guardian:', studentData.parent_guardian || 'N/A'],
                    ['Emergency Contact:', studentData.emergency_contact || 'N/A'],
                    
                    // Academic Information
                    ['Grade Level:', studentData.grade_level],
                    ['Strand:', studentData.strand],
                    ['Section:', studentData.section_block],
                    ['Enrollment Status:', studentData.enrollment_status],
                    ['School Year:', studentData.school_year],
                    
                    // System Information
                    ['Created:', studentData.created_at],
                    ['Last Updated:', studentData.updated_at],
                    ['QR Generated:', studentData.qr_generated_at]
                ];

                pdf.setFontSize(10);
                completeInfo.forEach(([label, value]) => {
                    if (y > 250) {
                        // Add new page if content is too long
                        pdf.addPage();
                        y = 20;
                    }
                    
                    pdf.setFont('helvetica', 'bold');
                    pdf.text(label, 20, y);
                    pdf.setFont('helvetica', 'normal');
                    
                    // Wrap long text
                    const valueStr = String(value || 'N/A');
                    if (valueStr.length > 40) {
                        const wrappedText = pdf.splitTextToSize(valueStr, 80);
                        pdf.text(wrappedText, 80, y);
                        y += (wrappedText.length * 4);
                    } else {
                        pdf.text(valueStr, 80, y);
                        y += 6;
                    }
                });

                // Add QR image
                pdf.addImage(qrImageData, 'PNG', 75, y + 10, 60, 60);

                // Footer
                pdf.setFontSize(10);
                pdf.text('Generated: ' + new Date().toLocaleString(), 20, y + 80);
                pdf.text('Palawan National School QR System', 20, y + 90);

                // Save
                const filename = `${studentData.id}-QR-Card-${Date.now()}.pdf`;
                pdf.save(filename);

                updateStatus('✅ PDF download completed!', 'success');
                console.log('PDF download successful');

            } catch (error) {
                console.error('PDF download failed:', error);
                updateStatus(`❌ PDF download failed: ${error.message}`, 'error');
            }
        }

        // Alternative method: Open QR in new window
        function openQRWindow() {
            if (!qrImageData) {
                alert('Please generate QR code first');
                return;
            }

            const popup = window.open('', '_blank', 'width=500,height=600');
            popup.document.write(`
                <html>
                <head>
                    <title>QR Code - ${studentData.id}</title>
                    <style>
                        body { 
                            text-align: center; 
                            padding: 20px; 
                            font-family: Arial, sans-serif;
                            background: #f8f9fa;
                        }
                        .qr-card {
                            background: white;
                            padding: 30px;
                            border-radius: 15px;
                            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                            display: inline-block;
                        }
                        img {
                            border: 3px solid #218c21;
                            border-radius: 10px;
                            margin: 20px 0;
                        }
                        .info {
                            background: #e8f5e8;
                            padding: 15px;
                            border-radius: 8px;
                            margin-top: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class="qr-card">
                        <h2>🎓 Palawan National School</h2>
                        <h3>Student QR Code</h3>
                        <img src="${qrImageData}" alt="QR Code" />
                        <div class="info">
                            <strong>Student ID:</strong> ${studentData.id}<br>
                            <strong>Name:</strong> ${studentData.name}<br>
                            <strong>Generated:</strong> ${new Date().toLocaleString()}
                        </div>
                        <p><em>Right-click the QR code to save as image</em></p>
                    </div>
                </body>
                </html>
            `);
            popup.document.close();
        }

        // Alternative method: Show QR as downloadable image
        function showQRAsImage() {
            if (!qrImageData) {
                alert('Please generate QR code first');
                return;
            }

            const link = document.createElement('a');
            link.href = qrImageData;
            link.target = '_blank';
            link.click();
        }

        // Fallback: Create printable PDF page
        function openPrintablePDF() {
            if (!qrImageData) {
                alert('Please generate QR code first');
                return;
            }

            const popup = window.open('', '_blank', 'width=800,height=600');
            popup.document.write(`
                <html>
                <head>
                    <title>Student QR Card - ${studentData.id}</title>
                    <style>
                        @media print {
                            body { margin: 0; }
                            .no-print { display: none; }
                        }
                        body { 
                            font-family: Arial, sans-serif;
                            padding: 20px;
                            background: white;
                        }
                        .card {
                            border: 2px solid #218c21;
                            border-radius: 10px;
                            padding: 30px;
                            text-align: center;
                            max-width: 500px;
                            margin: 0 auto;
                        }
                        .header {
                            background: #218c21;
                            color: white;
                            padding: 15px;
                            margin: -30px -30px 20px -30px;
                            border-radius: 8px 8px 0 0;
                        }
                        .qr-image {
                            border: 3px solid #218c21;
                            border-radius: 8px;
                            margin: 20px 0;
                        }
                        .info {
                            text-align: left;
                            background: #f8f9fa;
                            padding: 15px;
                            border-radius: 8px;
                            margin: 20px 0;
                        }
                        .print-btn {
                            background: #218c21;
                            color: white;
                            border: none;
                            padding: 12px 25px;
                            border-radius: 5px;
                            font-size: 16px;
                            cursor: pointer;
                            margin: 10px;
                        }
                    </style>
                </head>
                <body>
                    <div class="card">
                        <div class="header">
                            <h2>🎓 PALAWAN NATIONAL SCHOOL</h2>
                            <h3>Student QR Code Card</h3>
                        </div>
                        
                        <img src="${qrImageData}" alt="QR Code" class="qr-image" />
                        
                        <div class="info">
                            <p><strong>Student ID:</strong> ${studentData.id}</p>
                            <p><strong>Name:</strong> ${studentData.name}</p>
                            <p><strong>LRN:</strong> ${studentData.lrn}</p>
                            <p><strong>Email:</strong> ${studentData.email}</p>
                            <p><strong>Grade:</strong> ${studentData.grade}</p>
                            <p><strong>Strand:</strong> ${studentData.strand}</p>
                            <p><strong>Section:</strong> ${studentData.section}</p>
                            <p><strong>Generated:</strong> ${new Date().toLocaleString()}</p>
                        </div>
                        
                        <div class="no-print">
                            <button onclick="window.print()" class="print-btn">
                                🖨️ Print / Save as PDF
                            </button>
                            <button onclick="window.close()" class="print-btn" style="background: #6c757d;">
                                ❌ Close
                            </button>
                        </div>
                    </div>
                </body>
                </html>
            `);
            popup.document.close();
            
            setTimeout(() => {
                popup.focus();
                popup.print();
            }, 500);
        }

        // Initialize libraries when page loads
        window.addEventListener('load', function() {
            console.log('🚀 Student QR Card System initialized');
            console.log('Student Data:', studentData);
            loadLibraries();
            
            // Initialize binary mode
            initializeBinaryMode();
            
            // Auto-generate binary QR after page loads
            setTimeout(() => {
                updateStatus('⚡ Standard mode ready. Click "Generate QR" or switch to Binary.', 'info');
            }, 1000);
            
            // Auto-generate QR code once library likely loaded (poll up to 5s)
            (async function autoGen(){
                await ensureQRCodeLibrary();
                generateQR();
            })();
        });
    </script>
</body>
</html>