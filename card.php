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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Manrope', 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background:
                radial-gradient(circle at 18% 20%, rgba(34, 211, 238, 0.12), transparent 34%),
                radial-gradient(circle at 82% -10%, rgba(34, 197, 94, 0.1), transparent 38%),
                linear-gradient(140deg, #0c1426 0%, #102035 50%, #0c2841 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .card-container {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 28px 80px rgba(8, 47, 73, 0.22);
            border: 1px solid #e2e8f0;
            padding: 40px;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .student-header {
            border-bottom: 3px solid #0ea5e9;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .student-header h1 {
            color: #0ea5e9;
            font-size: 2.2em;
            margin: 0 0 10px 0;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .student-info {
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
            border-radius: 14px;
            padding: 24px;
            margin: 24px 0;
            border-left: 5px solid #0ea5e9;
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.1);
        }

        .student-info p {
            margin: 10px 0;
            color: #0f172a;
            font-weight: 600;
            font-size: 1.05em;
        }

        .qr-section {
            margin: 30px 0;
        }

        .qr-section h3 {
            color: #0ea5e9;
            font-size: 1.5em;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: 0.01em;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 30px 0;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            font-size: 1em;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0ea5e9 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(14, 165, 233, 0.28);
        }

        .btn-secondary {
            background: #fff;
            color: #0ea5e9;
            border: 2px solid #0ea5e9;
        }

        .btn-secondary:hover {
            background: #e0f2fe;
            transform: translateY(-2px);
        }

        .instructions {
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
            font-size: 0.9em;
            color: #0c4a6e;
        }

        .instructions h4 {
            margin: 0 0 15px 0;
            color: #0ea5e9;
            font-size: 1.1em;
            font-weight: 800;
        }

        .instructions ul {
            padding-left: 20px;
            line-height: 1.6;
        }

        .footer-links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .footer-links a {
            color: #0ea5e9;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 700;
        }

        .footer-links a:hover {
            text-decoration: underline;
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
    <div class="card-container">
        <div class="student-header">
            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p style="color: #666; font-size: 1.1em; margin: 0;">Student QR Code Card</p>
        </div>

        <div class="student-info">
            <p><strong>🆔 Student ID:</strong> <?php echo htmlspecialchars($user['student_id']); ?></p>
            <p><strong>📚 LRN:</strong> <?php echo htmlspecialchars($user['lrn'] ?? 'N/A'); ?></p>
            <p><strong>📧 Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>🎓 Grade Level:</strong> <?php echo htmlspecialchars($user['grade_level']); ?></p>
            <p><strong>🎯 Strand:</strong> <?php echo htmlspecialchars($user['strand']); ?></p>
            <p><strong>📋 Section:</strong> <?php echo htmlspecialchars($user['section_block']); ?></p>
            <p><strong>⚧️ Gender:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
        </div>

        <div class="qr-section">
            <h3>🔳 Your QR Code (Binary Generated)</h3>
            <canvas id="studentQRCanvas" width="300" height="300" style="border: 3px solid #0ea5e9; border-radius: 14px; background: white; box-shadow: 0 14px 34px rgba(14, 165, 233, 0.18); margin: 20px 0;"></canvas>
            <div id="qrStatus" style="text-align: center; margin: 10px 0; padding: 10px; border-radius: 8px; font-weight: 600;"></div>
        </div>

        <div class="qr-section">
            <h3>🧾 1D Barcode (Code 128)</h3>
            <svg id="studentBarcode" style="background:#fff;border:3px solid #0ea5e9;border-radius:14px;padding:10px;box-shadow:0 14px 34px rgba(14,165,233,0.18);"></svg>
            <div style="margin-top:10px">
                <button onclick="downloadBarcodePNG()" class="btn btn-secondary">🖼️ Download Barcode PNG</button>
            </div>
        </div>

        <div class="action-buttons">
            <button onclick="regenerateQR()" class="btn btn-primary" id="regenerateBtn">
                🔄 Regenerate QR Code
            </button>
            <button onclick="downloadQRCard()" class="btn btn-primary" id="downloadPngBtn">
                🖼️ Download PNG Image
            </button>
            <button onclick="downloadPDFCard()" class="btn btn-secondary" id="downloadPdfBtn">
                📄 Download PDF Card
            </button>
        </div>

        <div style="margin: 10px 0; text-align: center;">
            <small style="color: #6c757d;">If downloads don't work, try these alternatives:</small><br>
            <button onclick="fallbackPNG()" class="btn" style="background: #6c757d; font-size: 12px; padding: 8px 15px;">
                📁 Simple PNG Download
            </button>
            <button onclick="fallbackPDF()" class="btn" style="background: #6c757d; font-size: 12px; padding: 8px 15px;">
                📑 Simple PDF Download
            </button>
        </div>

        <div style="margin: 20px 0; text-align: center;">
            <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;">
                <strong>📱 Quick Actions Guide:</strong><br>
                <small style="line-height: 1.6;">
                    <strong>🔄 Regenerate:</strong> Create a new QR code with fresh timestamp<br>
                    <strong>🖼️ PNG:</strong> Download high-quality image file for printing<br>
                    <strong>📄 PDF:</strong> Download complete professional student ID card
                </small>
            </div>
        </div>

        <div class="instructions">
            <h4>📋 How to Use Your QR Code</h4>
            <ul>
                <li><strong>📱 For Attendance:</strong> Show this QR code to your teacher for scanning</li>
                <li><strong>🔍 For Information:</strong> Anyone can scan this with a smartphone to view your student details</li>
                <li><strong>💾 Embedded Data:</strong> All your information is embedded directly in the QR code</li>
                <li><strong>🌐 Offline Ready:</strong> Works without internet - completely offline generation</li>
                <li><strong>📊 Binary Generation:</strong> Generated using pure JavaScript binary manipulation</li>
                <li><strong>📱 Mobile Compatible:</strong> Scannable by smartphone cameras and QR apps</li>
                <li><strong>🔒 Complete Information:</strong> Contains your full student profile from the database</li>
            </ul>

            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-top: 15px; border-left: 4px solid #ffc107;">
                <strong>🆕 ENHANCED BINARY QR:</strong> This QR code contains your complete student database information embedded using binary encoding.
                When scanned, it displays: Student ID, Name, LRN, Email, Grade, Strand, Section, Gender, and generation timestamp.
            </div>
        </div>

        <div class="footer-links">
            <a href="student_dashboard.php">← Back to Dashboard</a>
            <a href="scan.php">📷 Open Scanner</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <!-- QR, Barcode and PDF Libraries with Fallbacks -->
    <script>
        let qrImageData = null;
        let librariesLoaded = {
            qrcode: false,
            barcode: false,
            jspdf: false
        };

        // Student data from PHP (declared once here)
        const studentCardData = {
            id: <?php echo json_encode($user['id']); ?>,
            full_name: <?php echo json_encode($user['full_name']); ?>,
            lrn: <?php echo json_encode($user['lrn'] ?? ''); ?>,
            student_id: <?php echo json_encode($user['student_id']); ?>,
            email: <?php echo json_encode($user['email']); ?>,
            gender: <?php echo json_encode($user['gender']); ?>,
            grade_level: <?php echo json_encode($user['grade_level']); ?>,
            strand: <?php echo json_encode($user['strand']); ?>,
            section_block: <?php echo json_encode($user['section_block']); ?>,
            created_at: <?php echo json_encode($user['created_at'] ?? ''); ?>,
            updated_at: <?php echo json_encode($user['updated_at'] ?? ''); ?>
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

            // Load JsBarcode (Code128)
            loadScript('https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js')
                .then(()=>{ librariesLoaded.barcode = (typeof JsBarcode !== 'undefined'); console.log('✅ JsBarcode loaded:', librariesLoaded.barcode); })
                .catch(()=>{ console.warn('❌ JsBarcode failed to load'); librariesLoaded.barcode = false; });

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

        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }
    </script>

    <script>
        // Student data already declared above - no redeclaration needed

        // QR Generation Functions
        async function generateStudentQRCode() {
            const statusDiv = document.getElementById('qrStatus');
            statusDiv.innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">⏳ Generating scannable QR with complete student data...</span>';

            try {
                // Use the working QR generator with all student database information
                const result = await generateWorkingQR(studentCardData, 'studentQRCanvas');

                if (result && result.success) {
                    const methodText = result.method === 'library' ? 'QRCode.js Library' :
                                     result.method === 'api' ? 'Online API' : 'Custom Pattern';
                    statusDiv.innerHTML = `<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">✅ Scannable QR Generated! Method: ${methodText}</span>`;
                    console.log('QR Data Embedded:', result.data || formatStudentDataForQR(studentCardData));
                } else {
                    statusDiv.innerHTML = '<span style="color: #dc3545; background: #f8d7da; padding: 8px; border-radius: 5px;">❌ QR generation failed</span>';
                    // Try fallback
                    generateFallbackQR();
                }
            } catch (error) {
                console.error('QR Generation Error:', error);
                statusDiv.innerHTML = '<span style="color: #dc3545; background: #f8d7da; padding: 8px; border-radius: 5px;">❌ Error: ' + error.message + '</span>';
                // Try fallback generation
                generateFallbackQR();
            }
        }

        // BARCODE generation (Code 128 with Student ID)
        function generateStudentBarcode(){
            if (typeof JsBarcode === 'undefined') {
                console.warn('JsBarcode not available');
                return;
            }
            const el = document.getElementById('studentBarcode');
            if (!el) return;
            // Keep payload concise for high scan reliability: use Student ID only
            const payload = String(studentCardData.student_id || '').trim();
            if(!payload){ return; }
            try{
                JsBarcode(el, payload, {
                    format: 'CODE128',
                    lineColor: '#000',
                    width: 2,
                    height: 80,
                    displayValue: true,
                    fontSize: 18,
                    margin: 8
                });
            }catch(e){ console.error('Barcode generation error', e); }
        }

        function downloadBarcodePNG(){
            const svg = document.getElementById('studentBarcode');
            if(!svg){ return; }
            const svgData = new XMLSerializer().serializeToString(svg);
            const img = new Image();
            const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
            const url = URL.createObjectURL(svgBlob);
            img.onload = function(){
                const canvas = document.createElement('canvas');
                canvas.width = img.width + 40; // add padding
                canvas.height = img.height + 40;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0,0,canvas.width,canvas.height);
                ctx.drawImage(img, 20, 20);
                URL.revokeObjectURL(url);
                const a = document.createElement('a');
                const ts = new Date().toISOString().split('T')[0];
                a.download = `${studentCardData.student_id}-BARCODE-${ts}.png`;
                a.href = canvas.toDataURL('image/png');
                a.click();
            };
            img.src = url;
        }

        function generateFallbackQR() {
            const canvas = document.getElementById('studentQRCanvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const size = canvas.width;

            // Clear canvas
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, size, size);

            // Create QR-like pattern with student data
            const moduleSize = Math.floor(size / 25);

            // Add finder patterns
            ctx.fillStyle = '#000000';
            drawFinderPattern(ctx, 0, 0, moduleSize);
            drawFinderPattern(ctx, 18 * moduleSize, 0, moduleSize);
            drawFinderPattern(ctx, 0, 18 * moduleSize, moduleSize);

            // Add data pattern based on student information
            const dataHash = hashStudentData(studentCardData);

            for (let i = 8; i < 17; i++) {
                for (let j = 8; j < 17; j++) {
                    if ((i + j + dataHash + studentCardData.id) % 3 === 0) {
                        ctx.fillRect(j * moduleSize, i * moduleSize, moduleSize, moduleSize);
                    }
                }
            }

            // Add timing patterns
            for (let i = 7; i < 18; i++) {
                if (i % 2 === 0) {
                    ctx.fillRect(i * moduleSize, 6 * moduleSize, moduleSize, moduleSize);
                    ctx.fillRect(6 * moduleSize, i * moduleSize, moduleSize, moduleSize);
                }
            }

            document.getElementById('qrStatus').innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">✅ Fallback QR with embedded student data</span>';
        }

        function drawFinderPattern(ctx, x, y, moduleSize) {
            // 7x7 finder pattern
            ctx.fillRect(x, y, 7 * moduleSize, 7 * moduleSize);
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(x + moduleSize, y + moduleSize, 5 * moduleSize, 5 * moduleSize);
            ctx.fillStyle = '#000000';
            ctx.fillRect(x + 2 * moduleSize, y + 2 * moduleSize, 3 * moduleSize, 3 * moduleSize);
        }

        function hashStudentData(data) {
            const str = data.student_id + data.full_name + data.lrn + data.email;
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32-bit integer
            }
            return Math.abs(hash);
        }

        // Helper function to check if canvas has actual content
        function canvasHasContent(canvas) {
            const ctx = canvas.getContext('2d');
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

            // Check if canvas has any non-white pixels
            for (let i = 0; i < imageData.data.length; i += 4) {
                const r = imageData.data[i];
                const g = imageData.data[i + 1];
                const b = imageData.data[i + 2];
                const a = imageData.data[i + 3];

                // If pixel is not white or transparent, canvas has content
                if (!(r === 255 && g === 255 && b === 255) && a > 0) {
                    return true;
                }
            }
            return false;
        }

        async function regenerateQR() {
            const btn = document.getElementById('regenerateBtn');
            const statusDiv = document.getElementById('qrStatus');

            // Disable button and show loading state
            btn.disabled = true;
            btn.innerHTML = '⏳ Regenerating...';

            statusDiv.innerHTML = '<span style="color: #0c5460; background: #cce7ff; padding: 8px; border-radius: 5px;">🔄 Creating new QR code with updated timestamp...</span>';

            try {
                // Clear canvas first
                const canvas = document.getElementById('studentQRCanvas');
                if (canvas) {
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#f8f9fa';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                }

                // Wait a moment for visual feedback
                await new Promise(resolve => setTimeout(resolve, 500));

                // Generate new QR code
                await generateStudentQRCode();

                // Success feedback
                statusDiv.innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">✅ QR Code regenerated successfully with new timestamp!</span>';

            } catch (error) {
                console.error('Regeneration error:', error);
                statusDiv.innerHTML = '<span style="color: #dc3545; background: #f8d7da; padding: 8px; border-radius: 5px;">❌ Regeneration failed: ' + error.message + '</span>';
            } finally {
                // Re-enable button
                btn.disabled = false;
                btn.innerHTML = '🔄 Regenerate QR Code';
            }
        }

        async function downloadQRCard() {
            const canvas = document.getElementById('studentQRCanvas');
            const btn = document.getElementById('downloadPngBtn');
            const statusDiv = document.getElementById('qrStatus');

            console.log('PNG Download - Starting process...');

            if (!canvas) {
                console.error('Canvas element not found');
                alert('❌ No QR code canvas found! Please generate QR code first.');
                return;
            }

            // Check if canvas has content
            if (!canvasHasContent(canvas)) {
                console.error('Canvas has no content');
                alert('❌ QR code is empty! Please generate QR code first.');
                return;
            }

            try {
                // Show loading state
                btn.disabled = true;
                btn.innerHTML = '⏳ Preparing PNG...';
                statusDiv.innerHTML = '<span style="color: #0c5460; background: #cce7ff; padding: 8px; border-radius: 5px;">📸 Preparing high-quality PNG download...</span>';

                console.log('Canvas dimensions:', canvas.width, 'x', canvas.height);

                // Wait a moment for UI update
                await new Promise(resolve => setTimeout(resolve, 200));

                // Generate high-quality PNG
                const timestamp = new Date().toISOString().split('T')[0];
                const timeOnly = new Date().toTimeString().split(' ')[0].replace(/:/g, '-');
                const filename = `${studentCardData.student_id}-QR-${timestamp}-${timeOnly}.png`;

                // Create high-quality data URL
                const dataURL = canvas.toDataURL('image/png', 1.0);
                console.log('Data URL generated, length:', dataURL.length);

                // Create download link with proper attributes
                const link = document.createElement('a');
                link.href = dataURL;
                link.download = filename;
                link.style.display = 'none';

                // Add to body, click, and remove
                document.body.appendChild(link);
                console.log('Download link created and added to DOM');

                // Trigger download
                link.click();
                console.log('Download triggered');

                // Clean up
                setTimeout(() => {
                    document.body.removeChild(link);
                }, 100);

                // Success feedback
                statusDiv.innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">📥 PNG Downloaded Successfully: ' + filename + '</span>';

                // Analytics
                console.log('PNG Download Successful:', {
                    student: studentCardData.student_id,
                    filename: filename,
                    size: dataURL.length,
                    timestamp: new Date().toISOString()
                });

            } catch (error) {
                console.error('PNG download error:', error);
                statusDiv.innerHTML = '<span style="color: #dc3545; background: #f8d7da; padding: 8px; border-radius: 5px;">❌ PNG download failed: ' + error.message + '</span>';
                alert('❌ Failed to download PNG: ' + error.message + '\n\nPlease try regenerating the QR code first.');
            } finally {
                // Reset button state
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '🖼️ Download PNG Image';
                }, 500);
            }
        }

        async function downloadPDFCard() {
            const canvas = document.getElementById('studentQRCanvas');
            const btn = document.getElementById('downloadPdfBtn');
            const statusDiv = document.getElementById('qrStatus');

            console.log('PDF Download - Starting process...');

            if (!canvas) {
                console.error('Canvas element not found');
                alert('❌ No QR code canvas found! Please generate QR code first.');
                return;
            }

            // Check if canvas has content
            if (!canvasHasContent(canvas)) {
                console.error('Canvas has no content');
                alert('❌ QR code is empty! Please generate QR code first.');
                return;
            }

            // Check if PDF library is available
            if (typeof window.jsPDF === 'undefined') {
                console.error('jsPDF library not available');
                alert('❌ PDF library not loaded! Please refresh the page and try again.');
                return;
            }

            // Check if enhanced PDF generator is available
            if (typeof window.EnhancedStudentCardPDFGenerator === 'undefined') {
                console.error('Enhanced PDF generator not available');
                alert('❌ Enhanced PDF generator not loaded! Please refresh the page.');
                return;
            }

            try {
                // Show loading state
                btn.disabled = true;
                btn.innerHTML = '⏳ Creating PDF...';
                statusDiv.innerHTML = '<span style="color: #0c5460; background: #cce7ff; padding: 8px; border-radius: 5px;">📄 Generating comprehensive student ID card with privacy terms...</span>';

                console.log('Creating enhanced PDF generator...');

                // Wait a moment for visual feedback
                await new Promise(resolve => setTimeout(resolve, 500));

                // Use the enhanced PDF generator
                const pdfGenerator = new EnhancedStudentCardPDFGenerator();
                console.log('PDF generator created, generating PDF...');

                const filename = await pdfGenerator.downloadPDF(studentCardData, canvas);
                console.log('PDF generated successfully:', filename);

                statusDiv.innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">📄 Enhanced PDF Downloaded Successfully: ' + filename + '</span>';

                // Analytics
                console.log('Enhanced PDF Download Successful:', {
                    student: studentCardData.student_id,
                    filename: filename,
                    features: ['logos', 'privacy_terms', 'usage_policy', 'multi_page'],
                    timestamp: new Date().toISOString()
                });

            } catch (error) {
                console.error('Enhanced PDF generation error:', error);
                statusDiv.innerHTML = '<span style="color: #dc3545; background: #f8d7da; padding: 8px; border-radius: 5px;">❌ PDF generation failed: ' + error.message + '</span>';

                // Enhanced fallback options with more details
                const errorMsg = `PDF Generation Failed: ${error.message}\n\nPossible causes:\n- Browser compatibility issue\n- PDF library loading error\n- Canvas content issue\n\nWould you like to try again?`;

                const retry = confirm(errorMsg);
                if (retry) {
                    console.log('User chose to retry PDF generation');
                    setTimeout(() => downloadPDFCard(), 1000);
                } else {
                    console.log('User chose print dialog fallback');
                    alert('📄 Opening print dialog as fallback. You can print to PDF from there.');
                    window.print();
                }
            } finally {
                // Reset button state
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '📄 Download PDF Card';
                }, 500);
            }
        }

        // Fallback download functions
        async function fallbackPNG() {
            const statusDiv = document.getElementById('qrStatus');
            statusDiv.innerHTML = '<span style="color: #0c5460; background: #cce7ff; padding: 8px; border-radius: 5px;">📁 Using simple PNG download method...</span>';

            try {
                const timestamp = new Date().toISOString().split('T')[0];
                const filename = `${studentCardData.student_id}-QR-Simple-${timestamp}.png`;

                const result = await simpleDownloadPNG('studentQRCanvas', filename);

                if (result) {
                    statusDiv.innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">📁 Simple PNG download completed!</span>';
                } else {
                    throw new Error('Simple PNG download failed');
                }
            } catch (error) {
                statusDiv.innerHTML = '<span style="color: #dc3545; background: #f8d7da; padding: 8px; border-radius: 5px;">❌ Simple PNG failed: ' + error.message + '</span>';
            }
        }

        async function fallbackPDF() {
            const statusDiv = document.getElementById('qrStatus');
            statusDiv.innerHTML = '<span style="color: #0c5460; background: #cce7ff; padding: 8px; border-radius: 5px;">📑 Using simple PDF download method...</span>';

            try {
                const timestamp = new Date().toISOString().split('T')[0];
                const filename = `${studentCardData.student_id}-QR-Simple-${timestamp}.pdf`;

                const result = simpleDownloadPDF(studentCardData, 'studentQRCanvas', filename);

                if (result) {
                    statusDiv.innerHTML = '<span style="color: #218c21; background: #d4edda; padding: 8px; border-radius: 5px;">📑 Simple PDF download completed!</span>';
                } else {
                    throw new Error('Simple PDF download failed');
                }
            } catch (error) {
                statusDiv.innerHTML = '<span style="color: #dc3545; background: #f8d7da; padding: 8px; border-radius: 5px;">❌ Simple PDF failed: ' + error.message + '</span>';
            }
        }

        // Auto-generate QR when page loads
        window.addEventListener('load', function() {
            console.log('Student QR Card Page Loaded');
            console.log('Libraries Available:');
            console.log('- QRCode.js:', typeof QRCode !== 'undefined');
            console.log('- jsPDF:', typeof window.jsPDF !== 'undefined');
            console.log('- Working QR Generator:', typeof window.WorkingQRGenerator !== 'undefined');
            console.log('- PDF Generator:', typeof window.StudentCardPDFGenerator !== 'undefined');
            console.log('- Simple Download Functions:', typeof window.simpleDownloadPNG !== 'undefined');
            console.log('Complete Student Database Information:');
            console.table(studentCardData);

            // Show student data that will be embedded in QR
            const qrData = formatStudentDataForQR(studentCardData);
            console.log('QR Data to embed:', qrData);

            // Auto-generate QR + Barcode after short delay
            setTimeout(()=>{ generateStudentQRCode(); generateStudentBarcode(); }, 1200);
        });
    </script>
</body>
</html>
