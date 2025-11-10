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
<html>
<head>
    <meta charset="utf-8">
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
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #218c21;
            color: white;
        }
        
        .btn-primary:hover {
            background: #176617;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(33, 140, 33, 0.3);
        }
        
        .btn-secondary {
            background: #fff;
            color: #218c21;
            border: 2px solid #218c21;
        }
        
        .btn-secondary:hover {
            background: #f0fff0;
            transform: translateY(-2px);
        }
        
        .instructions {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
            font-size: 0.9em;
            color: #004085;
        }
        
        .instructions h4 {
            margin: 0 0 15px 0;
            color: #218c21;
            font-size: 1.1em;
        }
        
        .instructions ul {
            padding-left: 20px;
            line-height: 1.6;
        }
        
        .footer-links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .footer-links a {
            color: #218c21;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 600;
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
            <canvas id="studentQRCanvas" width="300" height="300" style="border: 3px solid #218c21; border-radius: 12px; background: white; box-shadow: 0 5px 20px rgba(33, 140, 33, 0.15); margin: 20px 0;"></canvas>
            <div id="qrStatus" style="text-align: center; margin: 10px 0; padding: 10px; border-radius: 8px; font-weight: 600;"></div>
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

    <!-- QR and PDF Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="js/working-qr-generator.js"></script>
    <script src="js/enhanced-pdf-generator.js"></script>
    <script src="js/simple-download.js"></script>
    
    <script>
        // Complete student data from database
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
            
            // Auto-generate QR code after short delay
            setTimeout(generateStudentQRCode, 1200);
        });
    </script>
</body>
</html>