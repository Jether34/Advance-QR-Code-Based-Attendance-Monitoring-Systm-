<?php
// pdf_to_csv_converter.php - AI-powered PDF to CSV converter for lessons
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoloader for PDF parser
require_once __DIR__ . '/api_config.php';

use Smalot\PdfParser\Parser;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Handle file upload
if (!isset($_FILES['pdf_file'])) {
    echo json_encode(['error' => 'No PDF file uploaded']);
    exit;
}

$file = $_FILES['pdf_file'];

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Upload failed']);
    exit;
}

if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
    echo json_encode(['error' => 'File too large (max 10MB)']);
    exit;
}

$allowedTypes = ['application/pdf', 'application/x-pdf'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['error' => 'Invalid file type. Please upload a PDF.']);
    exit;
}

// Extract text from PDF using smalot/pdfparser library
function extractPDFText($filePath) {
    try {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        
        // Get text from all pages
        $text = $pdf->getText();
        
        // Clean up the extracted text
        $text = preg_replace('/\s+/', ' ', $text); // Normalize whitespace
        $text = str_replace(['\n', '\r', '\t'], ["\n", "\n", " "], $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text); // Limit consecutive newlines
        
        return trim($text);
    } catch (Exception $e) {
        // Log error and return empty
        error_log("PDF extraction error: " . $e->getMessage());
        return '';
    }
}

// Use AI to structure the extracted text into CSV format
function convertTextToCSV($text, $pdo) {
    $ollamaUrl = OLLAMA_API_URL;
    
    // Show first 300 chars for debugging
    $previewText = substr($text, 0, 300);
    
    $prompt = "You are analyzing a REAL school module PDF that has been uploaded by a student.

📄 ACTUAL TEXT EXTRACTED FROM THE UPLOADED PDF FILE:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
$text
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️ CRITICAL RULES - READ CAREFULLY:
1. The text shown above is the ACTUAL CONTENT from the student's PDF file
2. You MUST extract lessons ONLY from this real PDF content
3. DO NOT make up examples, DO NOT generate fake lessons, DO NOT copy from instructions
4. If the PDF is about Filipino - extract Filipino lessons
5. If the PDF is about Math - extract Math lessons  
6. If the PDF is about Science - extract Science lessons
7. USE ONLY THE ACTUAL TOPICS, DEFINITIONS, AND CONTENT FROM THE PDF ABOVE

YOUR TASK:
Carefully read the actual PDF text above and extract ALL individual lessons.

IDENTIFY LESSONS by looking for:
- Lesson numbers (Aralin 1, Lesson 1, Module 1, etc.)
- Chapter titles and headings
- Topic separators and sections
- Learning competencies or objectives
- Distinct topics or concepts

FOR EACH LESSON FOUND, EXTRACT:
- topic: The actual lesson title from the PDF (use exact wording when possible)
- content: Comprehensive summary with ALL key information from that specific lesson - include definitions, explanations, examples, procedures, important facts (write 4-8 complete sentences capturing everything important from the lesson)
- difficulty: Easy/Medium/Hard (based on Grade 11-12 complexity)
- subject: The actual subject (Filipino, English, Math, Science, etc.)

OUTPUT FORMAT - CSV with this exact header:
topic,content,difficulty,subject

Then one row per lesson extracted from the PDF.

CSV RULES:
- Wrap fields containing commas in double quotes
- Escape quotes by doubling them (\"\")
- Extract typically 5-20 lessons depending on PDF length
- Each lesson should have detailed content (4-8 sentences minimum)

🎯 REMEMBER: Extract lessons from the ACTUAL PDF TEXT shown at the top of this prompt. Do NOT invent content!";
    
    $data = [
        'model' => 'llama3.2',
        'prompt' => $prompt,
        'stream' => false,
        'options' => [
            'temperature' => 0.3, // Lower for more accurate extraction
            'num_predict' => 5000 // Extended to capture ALL text across lessons
        ]
    ];
    
    $ch = curl_init($ollamaUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return null;
    }
    
    $result = json_decode($response, true);
    return isset($result['response']) ? trim($result['response']) : null;
}

try {
    // Extract text from PDF
    $extractedText = extractPDFText($file['tmp_name']);
    
    if (empty($extractedText) || strlen($extractedText) < 50) {
        echo json_encode([
            'error' => 'Could not extract text from PDF. The file may be scanned/image-based or corrupted.',
            'extracted_length' => strlen($extractedText),
            'debug_preview' => substr($extractedText, 0, 200)
        ]);
        exit;
    }
    
    // Use AI to convert to CSV format
    $csvContent = convertTextToCSV($extractedText, get_db());
    
    if (empty($csvContent)) {
        echo json_encode([
            'error' => 'AI conversion failed. Make sure Ollama is running (http://localhost:11434).',
            'extracted_text_preview' => substr($extractedText, 0, 500)
        ]);
        exit;
    }
    
    // Clean up AI response (remove markdown code blocks if present)
    $csvContent = preg_replace('/```csv\n/', '', $csvContent);
    $csvContent = preg_replace('/```\n?$/', '', $csvContent);
    $csvContent = trim($csvContent);
    
    // Validate CSV has content
    $lines = explode("\n", $csvContent);
    $lessonCount = count($lines) - 1; // Subtract header row
    
    if ($lessonCount < 1) {
        echo json_encode(['error' => 'No lessons could be extracted from the PDF. The file may be scanned or contain unreadable text.']);
        exit;
    }
    
    // Save to data directory for better organization
    $dataDir = __DIR__ . '/data/';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Name CSV same as uploaded PDF (sanitized)
    $originalName = isset($_FILES['pdf_file']['name']) ? $_FILES['pdf_file']['name'] : ('module_lessons_' . time() . '.pdf');
    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
    $safeBase = preg_replace('/[^A-Za-z0-9_\- ]/', '', $baseName);
    $safeBase = trim($safeBase) !== '' ? $safeBase : ('module_lessons_' . time());
    $csvFileName = $safeBase . '.csv';
    $csvPath = $dataDir . $csvFileName;
    file_put_contents($csvPath, $csvContent);
    
    echo json_encode([
        'success' => true,
        'csv_content' => $csvContent,
        'csv_file' => 'data/' . $csvFileName,
        'lesson_count' => $lessonCount,
        'extracted_text_length' => strlen($extractedText),
        'message' => "Successfully extracted {$lessonCount} lessons from the module and saved to data folder!"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Conversion error: ' . $e->getMessage()
    ]);
}
