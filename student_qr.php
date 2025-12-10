<?php
// student_qr.php - Generate a REAL (standards-compliant) QR code that embeds ALL student database fields (except password).
// Access: Only logged-in students. Uses session user_id (no trusting of query string) to prevent forging another student's QR.
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: login.php');
    exit;
}

$pdo = get_db();
// Select * to avoid unknown-column errors across differing schema versions (later filter out password)
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
$stmt->execute([':id' => $_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$student){
  echo 'Student record not found';
  exit;
}
// Fetch adviser (class teacher) based on matching grade/strand/section
try {
  $advStmt = $pdo->prepare('SELECT full_name FROM teachers WHERE grade_level = :g AND strand = :s AND section_block = :sec LIMIT 1');
  $advStmt->execute([
    ':g' => $student['grade_level'] ?? null,
    ':s' => $student['strand'] ?? null,
    ':sec' => $student['section_block'] ?? null
  ]);
  $adviser = $advStmt->fetch(PDO::FETCH_ASSOC);
  $adviser_name = $adviser['full_name'] ?? 'N/A';
} catch(Exception $e){
  $adviser_name = 'N/A';
}
// Remove sensitive fields that should never leave server in plain form
foreach(['password'] as $sensitive){
  if(isset($student[$sensitive])) unset($student[$sensitive]);
}
// Guarantee stable keys even if some columns absent in older schema
foreach(['lrn','created_at','updated_at'] as $optional){
  if(!array_key_exists($optional,$student)) $student[$optional] = '';
}

// Build JSON payload with all student information
// Scanner will extract only student_id for validation
$generatedAt = gmdate('c');
$version = '1.2';

$qrData = [
    'student_id' => $student['student_id'] ?? $student['id'],
    'name' => $student['full_name'] ?? '',
    'lrn' => $student['lrn'] ?? '',
    'email' => $student['email'] ?? '',
    'grade' => $student['grade_level'] ?? '',
    'strand' => $student['strand'] ?? '',
    'section' => $student['section_block'] ?? '',
    'gender' => $student['gender'] ?? '',
    'adviser' => $adviser_name,
    'school' => 'Palawan National School',
    'type' => 'student_card',
    'version' => $version,
    'generated' => date('Y-m-d'),
    'generated_at' => $generatedAt
];

$packed = json_encode($qrData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>My QR Code - <?php echo htmlspecialchars($student['full_name']); ?></title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body { background:linear-gradient(135deg,#d6f5d6 0%,#eaffea 100%);min-height:100vh;padding:20px;font-family:'Segoe UI',Arial,sans-serif; }
        .qr-card { max-width:560px;margin:0 auto;background:#fff;border-radius:20px;box-shadow:0 10px 32px rgba(33,140,33,.2);padding:40px;text-align:center; }
        h1 { margin:0 0 10px;color:#218c21;font-size:2em; }
        .meta { color:#666;margin-bottom:25px;font-size:.95em; }
        #qrcode { margin:15px auto;display:inline-block;padding:14px;border:3px solid #218c21;border-radius:12px;background:#fff; }
        .status { margin-top:15px;padding:12px;border-radius:8px;font-weight:600;font-size:.95em; }
        .status.success { background:#d4edda;color:#155724; }
        .status.error { background:#f8d7da;color:#721c24; }
        .details { text-align:left;margin-top:30px;background:#f8f9fa;padding:18px 22px;border-radius:14px;border-left:6px solid #218c21;font-size:.95em;line-height:1.35; }
        .details h3 { margin:0 0 12px;color:#218c21; }
        .grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px; }
        .field { background:#fff;border:1px solid #e2f5e2;border-radius:8px;padding:8px 10px;font-size:.85em; }
        .field strong { display:block;color:#176617;font-weight:600;font-size:.75rem;letter-spacing:.5px;text-transform:uppercase;margin-bottom:2px; }
        .actions { margin-top:25px;display:flex;gap:12px;flex-wrap:wrap;justify-content:center; }
        .btn { padding:12px 22px;border:none;border-radius:10px;font-size:.95em;font-weight:600;cursor:pointer;transition:.25s;background:#218c21;color:#fff;text-decoration:none; }
        .btn.alt { background:#6c757d; }
        .btn:hover { background:#176617; }
        .footer-links { margin-top:35px;font-size:.9em; }
        .footer-links a { color:#218c21;text-decoration:none;margin:0 10px; }
        .footer-links a:hover { text-decoration:underline; }
        @media (max-width:768px){ 
            body { padding:12px; }
            .qr-card { padding:28px 20px;border-radius:12px; } 
            h1 { font-size:1.6em; }
            .meta { font-size:0.9em; }
            #qrcode { padding:10px;border-width:2px; }
            .grid { grid-template-columns:1fr;gap:8px; }
            .field { padding:10px 12px; }
            .actions { flex-direction:column;width:100%; }
            .btn { width:100%;padding:14px 20px;box-sizing:border-box; }
            .details { padding:16px 18px;font-size:0.9em; }
            .footer-links { margin-top:25px; }
            .footer-links a { display:block;margin:8px 0; }
        }
        @media (max-width:480px){
            h1 { font-size:1.4em; }
            #qrcode { transform:scale(0.9); }
        }
    </style>
</head>
<body>
<div class="qr-card">
    <h1>My Student QR</h1>
    <div class="meta">Choose QR code format and download options</div>
    
    <!-- QR Format Selection -->
    <div style="background: #e7f3ff; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #007bff;">
        <h3 style="margin: 0 0 12px; color: #007bff; font-size: 1.1em;">📋 QR Code Format</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
            <label style="display: flex; align-items: center; cursor: pointer; padding: 10px 15px; background: #fff; border: 2px solid #218c21; border-radius: 8px; font-weight: 600;">
                <input type="radio" name="qrFormat" value="full" checked style="margin-right: 8px; width: 18px; height: 18px;">
                <span>📦 Full Information (Recommended)</span>
            </label>
            <label style="display: flex; align-items: center; cursor: pointer; padding: 10px 15px; background: #fff; border: 2px solid #6c757d; border-radius: 8px;">
                <input type="radio" name="qrFormat" value="idonly" style="margin-right: 8px; width: 18px; height: 18px;">
                <span>🆔 Student ID Only</span>
            </label>
        </div>
        <div id="formatDescription" style="margin-top: 10px; font-size: 0.9em; color: #555; text-align: center;">
            Contains all your information: Name, Grade, Section, LRN, etc.
        </div>
    </div>
    
    <div id="qrcode" aria-label="Student QR Code"></div>
    <div id="status" class="status">Generating...</div>
    <div class="actions">
      <button class="btn" id="btnDownloadPdf">📄 Download PDF</button>
      <button class="btn" id="btnDownloadPng">🖼️ Download PNG</button>
      <button class="btn alt" id="btnShowRaw">📋 Show Raw Data</button>
    </div>
    <div class="details">
        <h3>Embedded Fields</h3>
        <div class="grid">
            <?php foreach($student as $k=>$v): ?>
            <div class="field"><strong><?php echo htmlspecialchars($k); ?></strong><?php echo htmlspecialchars((string)$v); ?>&nbsp;</div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="footer-links">
        <a href="student_dashboard.php">← Back to Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<script src="assets/qrcode.min.js"></script>
<!-- jsPDF will be loaded dynamically when needed (avoid race issues) -->
<script>
// Student data from PHP
const studentData = <?php echo json_encode($qrData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
const studentIdOnly = <?php echo json_encode($student['student_id'] ?? $student['id']); ?>;

const statusEl = document.getElementById('status');
function setStatus(msg, cls){ statusEl.textContent = msg; statusEl.className = 'status ' + (cls||''); }

let currentQrData = studentData;
let currentFormat = 'full';

// Update description when format changes
document.querySelectorAll('input[name="qrFormat"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
        currentFormat = e.target.value;
        const descEl = document.getElementById('formatDescription');
        
        if (currentFormat === 'full') {
            descEl.textContent = 'Contains all your information: Name, Grade, Section, LRN, etc.';
            descEl.style.color = '#555';
            currentQrData = JSON.stringify(studentData);
        } else {
            descEl.textContent = 'Contains only your Student ID for quick scanning';
            descEl.style.color = '#007bff';
            currentQrData = studentIdOnly;
        }
        
        // Regenerate QR with new format
        generateRealQR();
    });
});

function generateRealQR(){
  try {
    const container = document.getElementById('qrcode');
    container.innerHTML = '';
    if(typeof QRCode !== 'function') throw new Error('QRCode library missing');
    
    // Use currentQrData based on selected format
    const dataToEncode = currentFormat === 'full' ? JSON.stringify(studentData) : studentIdOnly;
    
    new QRCode(container, {
      text: dataToEncode,
      width: 320,
      height: 320,
      correctLevel: QRCode.CorrectLevel.M
    });
    
    const formatLabel = currentFormat === 'full' ? 'Full Info' : 'ID Only';
    setStatus(`✅ QR Ready (${formatLabel} - ${dataToEncode.length} chars)`,'success');
  } catch(e){
    console.error(e);
    setStatus('QR generation failed: '+e.message,'error');
  }
}

// Utility to load image and return DataURL (fallback placeholder if fails)
function fetchImageAsDataURL(src){
  return new Promise(resolve => {
    if(!src){ resolve(null); return; }
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
      const c = document.createElement('canvas');
      c.width = img.naturalWidth; c.height = img.naturalHeight;
      c.getContext('2d').drawImage(img,0,0);
      resolve(c.toDataURL('image/png'));
    };
    img.onerror = () => resolve(null);
    img.src = src;
  });
}

function generatePdf(){
  const canvas = document.querySelector('#qrcode canvas');
  if(!canvas){ alert('QR not ready yet'); return; }
  if(!window.jspdf){ alert('PDF library still loading...'); return; }
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({orientation:'portrait',unit:'pt',format:'A4'});
  const pageWidth = doc.internal.pageSize.getWidth();
  let y = 40;
  // Load logos (placeholders if not available)
  Promise.all([
    fetchImageAsDataURL('assets/pns_logo.png'),
    fetchImageAsDataURL('assets/system_logo.png')
  ]).then(([pnsLogo, sysLogo]) => {
    if(pnsLogo) doc.addImage(pnsLogo,'PNG',40,y,90,90); else { doc.setDrawColor(33,140,33); doc.rect(40,y,90,90); doc.setFontSize(10); doc.text('PNS LOGO',85,y+50,{align:'center'}); }
    if(sysLogo) doc.addImage(sysLogo,'PNG',pageWidth-130,y,90,90); else { doc.setDrawColor(33,140,33); doc.rect(pageWidth-130,y,90,90); doc.setFontSize(10); doc.text('SYSTEM LOGO',pageWidth-85,y+50,{align:'center'}); }
    doc.setFont('helvetica','bold');
    doc.setFontSize(22);
    doc.setTextColor(33,140,33);
    doc.text('Palawan National School', pageWidth/2, y+30, {align:'center'});
    doc.setFontSize(14);
    doc.setFont('helvetica','normal');
    doc.text('QR Identity Certificate', pageWidth/2, y+52, {align:'center'});
    y += 110;

    // Student & Adviser info
    doc.setFontSize(12);
    doc.setTextColor(0,0,0);
    doc.text('Student Name: <?php echo addslashes($student['full_name']); ?>', 40, y);
    doc.text('Student ID: <?php echo addslashes($student['student_id']); ?>', 40, y+18);
    doc.text('Adviser: <?php echo addslashes($adviser_name); ?>', 40, y+36);
    y += 52;

    // QR Image
    const qrData = canvas.toDataURL('image/png');
    const qrSize = 220;
    doc.addImage(qrData,'PNG',(pageWidth-qrSize)/2,y,qrSize,qrSize);
    y += qrSize + 30;

    // Terms & Conditions
    doc.setFont('helvetica','bold');
    doc.setFontSize(13);
    doc.text('Terms & Conditions',40,y);
    y += 16;
    doc.setFont('helvetica','normal');
    doc.setFontSize(10);
    const terms = [
      '1. This QR contains personal academic identifiers for official school use only.',
      '2. Do not share publicly on social media or with unverified parties.',
      '3. Lost or compromised QR must be reported to the school registrar immediately.',
      '4. Unauthorized duplication, alteration, or misuse is subject to disciplinary action.',
      '5. Validation of this QR must occur via the official attendance system.',
      '6. By using this QR, the student agrees to data processing for legitimate school interests.'
    ];
    terms.forEach(line => { doc.text(line, 48, y); y += 14; });

    y += 10;
    doc.setFontSize(9);
    doc.setTextColor(120);
    const formatInfo = currentFormat === 'full' ? 'Full Information' : 'ID Only';
    doc.text('Generated: '+ new Date().toISOString() +'  |  Format: ' + formatInfo, 40, y);
    doc.text('This PDF is valid only when viewed intact with QR.', 40, y+12);
    
    const pdfFilename = `student_qr_<?php echo $student['student_id']; ?>_${currentFormat}_${Date.now()}.pdf`;
    doc.save(pdfFilename);
  });
}

// Ensure jsPDF is present; load if missing then callback
function ensurePdfLib(cb){
  if(window.jspdf){ cb(); return; }
  // Prevent duplicate loads
  if(document.getElementById('jspdf-loader')){ setStatus('Waiting for PDF library...'); setTimeout(()=>ensurePdfLib(cb),300); return; }
  setStatus('Loading PDF library...');
  const s = document.createElement('script');
  s.id = 'jspdf-loader';
  s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
  s.onload = () => { setStatus('PDF library ready'); cb(); };
  s.onerror = () => { setStatus('Failed to load PDF library','error'); };
  document.head.appendChild(s);
}

document.addEventListener('DOMContentLoaded', () => {
  // Attempt QR generation; if library missing, load from CDN fallback.
  if(typeof QRCode !== 'function'){
    console.warn('Local QRCode library not found, loading CDN fallback...');
    setStatus('Loading QR library fallback...');
    const cdn = document.createElement('script');
    cdn.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    cdn.onload = () => { generateRealQR(); };
    cdn.onerror = () => { setStatus('Failed to load QRCode library','error'); };
    document.head.appendChild(cdn);
  } else {
    generateRealQR();
  }
  document.getElementById('btnDownloadPdf').addEventListener('click', () => ensurePdfLib(generatePdf));
  document.getElementById('btnDownloadPng').addEventListener('click', downloadPng);
  document.getElementById('btnShowRaw').addEventListener('click', () => {
    const dataToShow = currentFormat === 'full' ? JSON.stringify(studentData, null, 2) : studentIdOnly;
    alert('QR Code Data:\n\n' + dataToShow);
  });
});

function downloadPng() {
  const canvas = document.querySelector('#qrcode canvas');
  if (!canvas) {
    alert('QR code not ready yet. Please wait...');
    return;
  }
  
  try {
    const formatLabel = currentFormat === 'full' ? 'full' : 'id';
    const filename = `student_qr_<?php echo $student['student_id']; ?>_${formatLabel}_${new Date().getTime()}.png`;
    
    // Convert canvas to blob and download
    canvas.toBlob((blob) => {
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
      setStatus('✅ PNG downloaded successfully', 'success');
    }, 'image/png');
  } catch (error) {
    console.error('Download failed:', error);
    alert('Failed to download PNG: ' + error.message);
  }
}
</script>
</body>
</html>