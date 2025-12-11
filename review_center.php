<?php
// review_center.php - Student Review Center (AI-ready scaffold)
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}
$pdo = get_db();
$uid = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT full_name, grade_level, strand, section_block FROM students WHERE id = :id');
$stmt->execute([':id'=>$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$user){ echo 'Student not found'; exit; }

// Check if continuing a conversation
$currentConvoId = isset($_GET['convo_id']) ? (int)$_GET['convo_id'] : null;
$previousConvo = null;
if ($currentConvoId) {
    $stmt = $pdo->prepare('SELECT question, response FROM reviewer_conversations WHERE id = ? AND user_id = ?');
    $stmt->execute([$currentConvoId, $uid]);
    $previousConvo = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Review Center - PNS</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body{font-family:'Manrope','Inter','Segoe UI',system-ui,-apple-system,sans-serif;background:radial-gradient(circle at 18% 18%,rgba(34,211,238,0.12),transparent 38%),radial-gradient(circle at 78% -8%,rgba(34,197,94,0.1),transparent 42%),linear-gradient(140deg,#0c1426 0%,#102035 50%,#0c2841 100%);min-height:100vh;color:#0f172a;margin:0}
    .navbar{background:linear-gradient(120deg,#0ea5e9 0%,#0d95d7 42%,#0fb38f 100%);padding:0;box-shadow:0 14px 36px rgba(6,182,212,0.28);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;border-radius:0 0 16px 16px}
    .navbar-brand{padding:18px 32px;font-size:1.3em;font-weight:800;color:#fff;letter-spacing:0.02em}
    .navbar a{color:#fff;padding:18px 26px;text-decoration:none;font-weight:700;transition:all .25s ease;border-bottom:3px solid transparent;display:flex;align-items:center;gap:10px}
    .navbar a:hover{background:rgba(255,255,255,.12);border-bottom-color:#e0f2fe}
    .container{max-width:1100px;margin:28px auto;padding:0 24px}
    .panel{background:#ffffff;border-radius:20px;box-shadow:0 24px 70px rgba(8,47,73,0.18);padding:32px 36px;margin-bottom:28px;border:1px solid #e2e8f0}
    h1{color:#0f172a;margin:0 0 8px 0;font-size:2em;font-weight:800}
    h2{color:#0f172a;font-weight:800;font-size:1.45em}
    .sub{color:#475569;margin-bottom:16px;font-size:0.98em}
    textarea{width:100%;min-height:160px;padding:14px;border:1px solid #d7e0eb;border-radius:12px;font-size:14px;resize:vertical;font-family:inherit;background:#f7f9fc;transition:all .2s ease;box-sizing:border-box}
    textarea:focus{border-color:#0ea5e9;box-shadow:0 0 0 3px rgba(14,165,233,0.16);background:#fff;outline:none}
    .btn{padding:12px 18px;border:none;border-radius:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s ease}
    .btn-primary{background:linear-gradient(135deg,#0ea5e9 0%,#0284c7 50%,#0ea5e9 100%);color:#fff}
    .btn-primary:hover{transform:translateY(-2px);box-shadow:0 16px 32px rgba(14,165,233,0.28)}
    .btn-secondary{background:#fff;color:#0ea5e9;border:2px solid #0ea5e9}
    .btn-secondary:hover{background:#e0f2fe;transform:translateY(-2px)}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    .list{list-style:decimal;padding-left:20px}
    .badge{display:inline-block;background:#e0f2fe;color:#0c4a6e;border-radius:999px;padding:5px 12px;font-weight:700;font-size:12px;border:1px solid #bae6fd}
    #aiOutput{line-height:1.6}
    #aiOutput p{margin:12px 0}
    #aiOutput strong{color:#0ea5e9}
    #aiOutput ul,#aiOutput ol{margin:8px 0;padding-left:24px}
    #aiOutput li{margin:6px 0}
    .ai-loading{text-align:center;padding:24px;color:#0ea5e9;font-style:italic}
    #reviewerOutput{background:#f7fbff;border:2px solid #e0f2fe;border-radius:14px;padding:18px}
    #uploadStatus{background:#f7fbff;border:2px solid #e0f2fe;border-radius:14px}
    @media(max-width:900px){.row{grid-template-columns:1fr}}
    @media(max-width:768px){
      body{margin:0;padding:0}
      .navbar{flex-direction:column;align-items:stretch;position:fixed;top:0;left:0;right:0;z-index:1000;width:100%}
      .navbar-brand{padding:14px 20px;font-size:1.1em;text-align:center;border-bottom:1px solid rgba(255,255,255,.2)}
      .navbar>div{width:100%;display:flex;flex-direction:column}
      .navbar a{padding:12px 20px;font-size:0.9em;border-bottom:1px solid rgba(255,255,255,.1);justify-content:center;border-left:none;border-right:none}
      .container{margin-top:150px;padding:0 12px;max-width:100%}
      .panel{padding:24px 16px;border-radius:12px;margin-bottom:16px}
      h1{font-size:1.5em;margin:0 0 8px}
      h2{font-size:1.3em;margin:0 0 12px}
      .sub{font-size:0.9em;margin:0 0 16px}
      textarea{font-size:15px;min-height:120px;padding:12px;width:100%;box-sizing:border-box}
      .btn{padding:12px 16px;font-size:0.95em}
      .btn-primary{width:100%;margin-bottom:10px}
      .btn-secondary{width:100%}
      #reviewerOutput{margin-top:20px;min-height:80px}
      input[type="file"]{font-size:14px;width:100%}
      #uploadStatus{margin-top:16px;padding:12px}
      .badge{font-size:11px;padding:3px 8px}
      label{display:block;margin-bottom:8px}
    }
    @media(max-width:480px){
      .navbar-brand{font-size:1em;padding:12px 16px}
      .navbar a{font-size:0.85em;padding:10px 16px}
      .container{margin-top:130px;padding:0 10px}
      .panel{padding:20px 12px;margin-bottom:12px}
      h1{font-size:1.3em}
      h2{font-size:1.2em}
      textarea{font-size:14px;min-height:100px}
      .row{gap:12px}
    }
  </style>
</head>
<body>
  <div class="navbar">
    <div style="display:flex;gap:0.5rem;align-items:center">
      <a href="student_dashboard.php"> Dashboard</a>
      <a href="logout.php"> Logout</a>
      <a href="recent_conversation.php"> Recent Conversation</a>
    </div>
  </div>

  <div class="container">
    <div class="panel">
      <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
      <div class="sub">Grade <?php echo htmlspecialchars($user['grade_level']); ?> • <?php echo htmlspecialchars($user['strand']); ?> • Section <?php echo htmlspecialchars($user['section_block']); ?></div>
      <span class="badge">Beta</span>
    </div>

    <div class="panel">
      <h2 style="color:#0ea5e9">💬 Ask for Reviewer (by Topic)</h2>
      <p class="sub">Type a topic or question (e.g., "Create a reviewer for Photosynthesis" or "Give me a summary about World War II")</p>
      <?php if ($previousConvo): ?>
        <div style="background:#e0f2fe;padding:12px;border-radius:8px;margin-bottom:12px">
          <strong>Continuing conversation:</strong><br>
          <em><?php echo htmlspecialchars(substr($previousConvo['question'], 0, 100)) . (strlen($previousConvo['question']) > 100 ? '...' : ''); ?></em>
        </div>
      <?php endif; ?>
      <textarea id="reviewerQuestion" placeholder="Ask Jether AI for a reviewer about any topic..." style="min-height:120px;font-size:15px;margin-bottom:12px;width:100%;box-sizing:border-box"></textarea>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <button class="btn btn-primary" onclick="askReviewer()" style="flex:1;min-width:140px"> Send </button>
        <button class="btn btn-secondary" onclick="clearReviewer()" style="flex:1;min-width:100px"> Clear</button>
      </div>
      <div id="reviewerOutput" style="margin-top:18px;background:#f7fbff;border:2px solid #e0f2fe;border-radius:12px;padding:16px;min-height:60px">
        <?php if ($previousConvo): ?>
          <div style="color:#0ea5e9;font-weight:600;margin-bottom:12px">Previous conversation:</div>
          <div style="white-space:pre-wrap"><?php echo htmlspecialchars($previousConvo['response']); ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <h2 style="color:#0ea5e9">📄 Upload School Module (PDF)</h2>
      <p class="sub">Upload your school module PDF - AI will extract ALL lessons automatically</p>
      <div style="margin-bottom:12px">
        <label style="font-weight:600;color:#0f172a;display:block;margin-bottom:12px">PDF Module:</label>
        <div style="position:relative;overflow:hidden;display:inline-block;width:100%">
          <input type="file" id="pdfFile" accept=".pdf" style="position:absolute;left:-9999px">
          <label for="pdfFile" style="display:block;padding:14px 16px;background:#f7fbff;border:2px solid #e0f2fe;border-radius:8px;cursor:pointer;font-weight:600;color:#0f172a;text-align:center;transition:.25s">
            📁 Choose PDF File
          </label>
          <span id="fileName" style="display:block;margin-top:8px;color:#666;font-size:0.9em">No file selected</span>
        </div>
        <button class="btn btn-primary" onclick="uploadPDF()" style="font-size:16px;padding:12px 24px;margin-top:12px;width:100%;box-sizing:border-box">🤖 Convert PDF to Lessons</button>
      </div>
      <div id="uploadStatus" style="margin-top:16px;padding:12px;background:#f7fbff;border-radius:8px;border:2px solid #e0f2fe"></div>
    </div>


    <div class="panel">
      <h2 style="color:#0ea5e9">ℹ️ AI Study Assistant Features</h2>
      <p class="sub">Powered by Jether AI (Llama 3.2) - Your personal tutor created by Jether Garque for Palawan National School</p>
      <div class="row">
        <div>
          <h3 style="color:#0ea5e9">📚 What Jether Can Do:</h3>
          <ul>
            <li><strong>Convert PDF to CSV</strong> - AI extracts and organizes lessons from PDF files</li>
            <li><strong>Generate Practice Questions</strong> - Multiple choice, short answer, essay, and application problems</li>
            <li><strong>Create Summaries</strong> - Concise overviews of lessons with key points</li>
            <li><strong>Explain Concepts</strong> - Break down complex topics into simple explanations</li>
            <li><strong>Create Flashcards</strong> - Question/answer pairs for quick review</li>
          </ul>
        </div>
        <div>
          <h3 style="color:#0ea5e9">📄 How PDF Conversion Works:</h3>
          <ul>
            <li><strong>Complete Text Extraction</strong> - AI reads ALL text from your PDF module</li>
            <li><strong>Automatic Lesson Detection</strong> - Identifies chapters, topics, and units</li>
            <li><strong>Smart Organization</strong> - Each lesson saved separately with topic, content, difficulty, subject</li>
            <li><strong>Auto-Save</strong> - CSV file automatically saved to <code>data/</code> folder</li>
            <li><strong>Works with:</strong> Modules, textbooks, lecture notes, study guides</li>
          </ul>
          <p style="margin-top:12px"><strong>CSV Format Created:</strong></p>
          <code style="background:#e0f2fe;padding:8px;display:block;border-radius:4px">topic, content, difficulty, subject</code>
        </div>
      </div>
    </div>
  </div>

  <script>
    let lessonsData = [];
    const studentGrade = <?php echo json_encode($user['grade_level']); ?>;
    const studentStrand = <?php echo json_encode($user['strand']); ?>;

    // Store conversation history
    let conversationHistory = [];
    let currentConvoId = <?php echo $currentConvoId ? $currentConvoId : 'null'; ?>;

    async function askReviewer() {
      const question = document.getElementById('reviewerQuestion').value.trim();
      const output = document.getElementById('reviewerOutput');
      if (!question) {
        output.innerHTML = '<span style="color:#d32f2f">⚠️ Please enter a topic or question.</span>';
        return;
      }
      output.innerHTML = '<div class="ai-loading">🤖 Jether is searching all lessons and preparing your reviewer...</div>';
      try {
        const res = await fetch('reviewer_ai.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            question,
            grade: studentGrade,
            strand: studentStrand,
            user_id: <?php echo (int)$uid; ?>,
            conversation_id: currentConvoId
          })
        });
        const data = await res.json();
        if (data.success) {
          output.innerHTML = '<div style="color:#1e5128;font-weight:600;margin-bottom:12px">✅ Reviewer generated by Jether AI</div>' + formatResponse(data.response);
          // Save to database immediately
          const saveRes = await fetch('save_conversation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              question,
              response: data.response,
              user_id: <?php echo (int)$uid; ?>,
              conversation_id: currentConvoId
            })
          });
          const saveData = await saveRes.json();
          if (saveData.success && saveData.conversation_id) {
            currentConvoId = saveData.conversation_id;
          }
        } else {
          output.innerHTML = '❌ ' + (data.error || 'AI request failed');
        }
      } catch (err) {
        console.error('Error details:', err);
        output.innerHTML = '❌ ' + (err.message || 'Connection error. Make sure Ollama is running at http://192.168.1.12:11434');
      }
    }

    async function clearReviewer() {
      const output = document.getElementById('reviewerOutput');
      output.innerHTML = '';
      document.getElementById('reviewerQuestion').value = '';
      currentConvoId = null;
      window.location.href = 'review_center.php';
    }

    // Update filename display when file is selected
    document.getElementById('pdfFile').addEventListener('change', function(e) {
      const fileName = this.files[0] ? this.files[0].name : 'No file selected';
      document.getElementById('fileName').textContent = this.files[0] ? '✓ ' + fileName : 'No file selected';
      document.getElementById('fileName').style.color = this.files[0] ? '#218c21' : '#666';
    });

    async function uploadPDF() {
      const fileInput = document.getElementById('pdfFile');
      const status = document.getElementById('uploadStatus');
      if (!fileInput.files[0]) {
        status.innerHTML = '<strong style="color:#d32f2f">⚠️ Please select a PDF module first</strong>';
        return;
      }

      const fileName = fileInput.files[0].name;
      status.innerHTML = `<div style="color:#1e5128;font-weight:600">🤖 AI is extracting ALL text content from "${fileName}"...</div>` +
                        `<div style="color:#5a6c7d;margin-top:8px">📖 Reading module content...<br>🧠 Identifying lessons...<br>⏳ This may take 30-90 seconds</div>`;

      const formData = new FormData();
      formData.append('pdf_file', fileInput.files[0]);

      try {
        const res = await fetch('pdf_to_csv_converter.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          lessonsData = parseCSV(data.csv_content);
          status.innerHTML = `<div style="color:#1e5128;font-weight:700;font-size:18px">✅ Success!</div>` +
                            `<div style="margin-top:8px">📚 <strong>${data.lesson_count} lessons</strong> extracted from module</div>` +
                            `<div>📄 Processed <strong>${Math.round(data.extracted_text_length / 1024)}KB</strong> of text content</div>` +
                            `<div>💾 Saved to: <code style="background:#d8f3dc;padding:2px 6px;border-radius:4px">${data.csv_file}</code></div>` +
                            `<div style="margin-top:12px;color:#2d6a4f;font-style:italic">Ready! Use the AI tools below to generate questions, summaries, or flashcards.</div>`;
        } else {
          status.innerHTML = `<div style="color:#d32f2f;font-weight:600">❌ ${data.error || 'PDF conversion failed'}</div>` +
                            `<div style="margin-top:8px;color:#5a6c7d"><em>💡 Tip: Make sure the PDF contains readable text (not scanned images)</em></div>`;
        }
      } catch (err) {
        console.error(err);
        status.innerHTML = `<div style="color:#d32f2f;font-weight:600">❌ Connection error</div>` +
                          `<div style="margin-top:8px;color:#5a6c7d"><em>Make sure Ollama is running. The AI needs to be active to extract lessons.</em></div>`;
      }
    }

    function parseCSV(text) {
      const lines = text.split('\n').filter(l => l.trim());
      if (lines.length < 2) return [];
      const headers = lines[0].split(',').map(h => h.trim().toLowerCase());
      const data = [];
      for (let i = 1; i < lines.length; i++) {
        const values = lines[i].split(',');
        const obj = {};
        headers.forEach((h, idx) => obj[h] = values[idx] ? values[idx].trim() : '');
        data.push(obj);
      }
      return data;
    }

    function getLessonContent() {
      const notes = document.getElementById('notes').value.trim();
      if (notes) return notes;
      if (lessonsData.length > 0) {
        return lessonsData.map(l => `Topic: ${l.topic || l.title || 'Unknown'}\nContent: ${l.content || l.description || ''}`).join('\n\n');
      }
      return '';
    }

    async function callAI(task) {
      const content = getLessonContent();
      if (!content) {
        document.getElementById('aiOutput').innerHTML = '⚠️ Please upload a CSV file or paste notes first.';
        return;
      }
      document.getElementById('aiOutput').innerHTML = '<div class="ai-loading">🤖 Jether is analyzing your lessons and preparing your study materials...<br>This may take 5-15 seconds.</div>';
      try {
        const res = await fetch('review_ai.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ task, content, grade: studentGrade, strand: studentStrand })
        });
        const data = await res.json();
        if (data.success) {
          document.getElementById('aiOutput').innerHTML = '<div style="color:#1e5128;font-weight:600;margin-bottom:12px">✅ Generated by Jether AI</div>' + formatResponse(data.response);
        } else {
          document.getElementById('aiOutput').innerHTML = '❌ ' + (data.error || 'AI request failed');
        }
      } catch (err) {
        console.error(err);
        document.getElementById('aiOutput').innerHTML = '❌ Connection error. Make sure Ollama is running and try again.';
      }
    }

    function formatResponse(text) {
      return text
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>')
        .replace(/^(.+)$/m, '<p>$1</p>');
    }

    // ...existing code...
  </script>
</body>
</html>
