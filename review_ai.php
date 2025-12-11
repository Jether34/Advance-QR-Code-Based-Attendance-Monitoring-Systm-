<?php
// review_ai.php - AI Study Assistant Backend for Review Center
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';

// Verify student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$task = $input['task'] ?? '';
$content = trim($input['content'] ?? '');
$grade = $input['grade'] ?? '';
$strand = $input['strand'] ?? '';

if (empty($content)) {
    echo json_encode(['error' => 'No content provided']);
    exit;
}

// Call Ollama AI with lesson content
function callOllamaForStudy($task, $content, $grade, $strand) {
    $ollamaUrl = 'http://localhost:11434/api/generate';

    // Build task-specific prompts
    $prompts = [
        'generate_questions' => "You are Jether, an AI tutor for Grade $grade $strand students at Palawan National School.

LESSON CONTENT:
$content

TASK: Create 10 practice questions based on this content. Include:
- 3 multiple choice questions
- 3 short answer questions
- 2 essay questions
- 2 application/problem-solving questions

Format each question clearly with numbering. Make them appropriate for Grade $grade level.",

        'summarize' => "You are Jether, an AI tutor for Grade $grade $strand students at Palawan National School.

LESSON CONTENT:
$content

TASK: Create a comprehensive summary of this lesson. Include:
- Main topics covered
- Key concepts and definitions
- Important facts to remember
- Real-world applications

Keep it clear and concise for Grade $grade students.",

        'explain_concepts' => "You are Jether, an AI tutor for Grade $grade $strand students at Palawan National School.

LESSON CONTENT:
$content

TASK: Identify and explain the 5 most important concepts from this lesson. For each concept:
1. Define it
2. Explain why it's important
3. Give a real-world example
4. Provide a memory tip or mnemonic

Make explanations appropriate for Grade $grade $strand students.",

        'create_flashcards' => "You are Jether, an AI tutor for Grade $grade $strand students at Palawan National School.

LESSON CONTENT:
$content

TASK: Create 15 flashcards for studying this content. Format:

**Front:** [Question or term]
**Back:** [Answer or definition]

Include key terms, concepts, formulas, and important facts. Make them suitable for Grade $grade level."
    ];

    $prompt = $prompts[$task] ?? $prompts['summarize'];

    $data = [
        'model' => 'llama3.2',
        'prompt' => $prompt,
        'stream' => false,
        'options' => [
            'temperature' => 0.7,
            'num_predict' => 800
        ]
    ];

    $ch = curl_init($ollamaUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Longer timeout for study content

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'error' => 'Failed to connect to Ollama: ' . $curlError];
    }

    if ($httpCode !== 200) {
        return ['success' => false, 'error' => "Ollama error (HTTP {$httpCode}). Make sure Ollama is running."];
    }

    $result = json_decode($response, true);

    if (isset($result['response'])) {
        return ['success' => true, 'response' => trim($result['response'])];
    }

    return ['success' => false, 'error' => 'No response from Ollama'];
}

try {
    $result = callOllamaForStudy($task, $content, $grade, $strand);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'AI error: ' . $e->getMessage()
    ]);
}
