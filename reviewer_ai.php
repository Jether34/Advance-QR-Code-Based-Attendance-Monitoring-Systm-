<?php
// reviewer_ai.php - Comprehensive AI Study Assistant
// Features: Answer questions, create reviewers, general knowledge, context-aware responses
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/api_config.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $question = trim($input['question'] ?? '');
    $grade = $input['grade'] ?? '';
    $strand = $input['strand'] ?? '';
    $conversation_id = $input['conversation_id'] ?? null;
    if (!$question) throw new Exception('No question provided');

    // If continuing conversation, load previous context
    $previousContext = '';
    if ($conversation_id) {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT question, response FROM reviewer_conversations WHERE id = ?');
        $stmt->execute([$conversation_id]);
        $prev = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($prev) {
            $previousContext = "\n\n📚 PREVIOUS CONVERSATION:\nStudent: " . $prev['question'] . "\nYou: " . substr($prev['response'], 0, 500) . "...\n\n";
        }
    }

    // Detect intent: Is this a reviewer request or a general question?
    $q = strtolower($question);
    $isReviewerRequest = (
        strpos($q, 'reviewer') !== false || 
        strpos($q, 'review') !== false || 
        strpos($q, 'summarize') !== false ||
        strpos($q, 'create') !== false ||
        strpos($q, 'make') !== false ||
        strpos($q, 'generate') !== false
    );
    
    // Detect question types
    $isDirectQuestion = (
        strpos($q, 'what') !== false || 
        strpos($q, 'how') !== false || 
        strpos($q, 'why') !== false || 
        strpos($q, 'when') !== false || 
        strpos($q, 'who') !== false ||
        strpos($q, 'explain') !== false ||
        strpos($q, 'define') !== false ||
        strpos($q, '?') !== false
    );

    // Find all CSV lesson files in data/
    $dataDir = __DIR__ . '/data/';
    $csvFiles = glob($dataDir . '*.csv');
    $allLessons = [];
    foreach ($csvFiles as $file) {
        $lines = file($file);
        if (count($lines) < 2) continue;
        $headers = array_map('trim', explode(',', strtolower($lines[0])));
        for ($i = 1; $i < count($lines); $i++) {
            $values = str_getcsv($lines[$i]);
            $lesson = [];
            foreach ($headers as $idx => $h) {
                $lesson[$h] = $values[$idx] ?? '';
            }
            $allLessons[] = $lesson;
        }
    }

    // Smart search: Find relevant lessons from CSV data
    $relevantLessons = [];
    if (!empty($allLessons)) {
        // Enhanced fuzzy matching
        $searchWords = preg_split('/\s+/', strtolower($question));
        $searchWords = array_filter($searchWords, function($w) { return strlen($w) > 2; }); // Filter short words
        
        foreach ($allLessons as $lesson) {
            $score = 0;
            $topicLower = strtolower($lesson['topic'] ?? '');
            $contentLower = strtolower($lesson['content'] ?? '');
            $subjectLower = strtolower($lesson['subject'] ?? '');
            
            // Score matching
            foreach ($searchWords as $word) {
                if (strpos($topicLower, $word) !== false) $score += 10;
                if (strpos($contentLower, $word) !== false) $score += 5;
                if (strpos($subjectLower, $word) !== false) $score += 7;
            }
            
            // Exact phrase match bonus
            if (strpos($topicLower, strtolower($question)) !== false) $score += 50;
            if (strpos($contentLower, strtolower($question)) !== false) $score += 20;
            
            if ($score > 0) {
                $lesson['_score'] = $score;
                $relevantLessons[] = $lesson;
            }
        }
        
        // Sort by relevance score
        usort($relevantLessons, function($a, $b) {
            return ($b['_score'] ?? 0) - ($a['_score'] ?? 0);
        });
        
        // Take top 10 most relevant lessons
        $relevantLessons = array_slice($relevantLessons, 0, 10);
    }

    // Prepare context for AI based on available data
    $hasRelevantData = !empty($relevantLessons);
    $lessonContext = "";
    
    if ($hasRelevantData) {
        $lessonContext = "\n📖 RELEVANT LESSONS FROM YOUR SCHOOL MODULES:\n";
        foreach ($relevantLessons as $l) {
            $lessonContext .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $lessonContext .= "Topic: " . ($l['topic'] ?? 'N/A') . "\n";
            $lessonContext .= "Subject: " . ($l['subject'] ?? 'N/A') . "\n";
            $lessonContext .= "Difficulty: " . ($l['difficulty'] ?? 'N/A') . "\n";
            $lessonContext .= "Content: " . ($l['content'] ?? 'N/A') . "\n";
        }
        $lessonContext .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }

    // Build dynamic AI prompt based on context
    $systemPrompt = "You are Jether, a friendly and knowledgeable AI study buddy for Palawan National School students! 🎓

YOUR PERSONALITY:
- Warm, encouraging, and supportive like a helpful kuya/ate (older sibling)
- Use casual Filipino-English (Taglish) naturally: 'Uy!', 'Ayos!', 'Kaya mo yan!', 'Gets mo?'
- Keep it conversational and fun but educational
- Use emojis occasionally: 😊 💡 🎯 ✨ 📚 ⚡
- Relate concepts to real Filipino student life and experiences
- Always encourage and build confidence
- Answer ALL types of questions - from homework help to general knowledge

YOUR CAPABILITIES:
✅ Create comprehensive reviewers from school lessons
✅ Answer direct questions about any topic
✅ Explain concepts in simple, relatable terms
✅ Provide examples from real life
✅ Help with homework and assignments
✅ Discuss general knowledge topics
✅ Continue conversations naturally

RESPONSE GUIDELINES:
📝 Write in OUTLINED PARAGRAPH FORMAT with clear headings
📝 Use detailed paragraphs (30 - 50 sentences each)
📝 Include definitions, explanations, examples, and context
📝 NO practice questions, quizzes, or test items
📝 Focus on understanding, not memorization
📝 Be thorough but conversational\n\n";

    // Add conversation context
    if ($previousContext) {
        $systemPrompt .= $previousContext;
    }

    // Add student info
    $systemPrompt .= "STUDENT INFO:\n";
    $systemPrompt .= "Grade Level: " . ($grade ?: "Not specified") . "\n";
    $systemPrompt .= "Strand: " . ($strand ?: "Not specified") . "\n\n";

    // Add the student's question
    $systemPrompt .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $systemPrompt .= "STUDENT'S QUESTION:\n\"$question\"\n";
    $systemPrompt .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    // Add lesson data if available
    if ($hasRelevantData) {
        $systemPrompt .= $lessonContext;
        $systemPrompt .= "\n✨ INSTRUCTIONS:\n";
        $systemPrompt .= "The student asked: \"$question\"\n\n";
        
        if ($isReviewerRequest) {
            $systemPrompt .= "They want a REVIEWER. Create a comprehensive study guide using the lessons above.\n";
            $systemPrompt .= "Organize it with clear headings and detailed paragraph explanations.\n";
        } else if ($isDirectQuestion) {
            $systemPrompt .= "They asked a DIRECT QUESTION. Answer it clearly using the lesson data above.\n";
            $systemPrompt .= "If the lessons don't fully cover it, use your general knowledge to complete the answer.\n";
        } else {
            $systemPrompt .= "Respond naturally based on what they're asking for.\n";
            $systemPrompt .= "Use the lesson data as primary reference, but feel free to add general knowledge.\n";
        }
    } else {
        // No CSV data available - use general knowledge
        $systemPrompt .= "\n⚠️ NOTE: No specific lesson data found for this topic in the uploaded modules.\n";
        $systemPrompt .= "Use your GENERAL KNOWLEDGE to answer this question comprehensively.\n\n";
        $systemPrompt .= "✨ INSTRUCTIONS:\n";
        $systemPrompt .= "Answer \"$question\" using your training knowledge.\n";
        $systemPrompt .= "Be thorough, accurate, and helpful.\n";
        $systemPrompt .= "Explain concepts clearly with examples and context.\n";
        $systemPrompt .= "Relate to Filipino student experiences when possible.\n";
    }

    $systemPrompt .= "\n💬 Now respond in a friendly, helpful way. Make learning fun! Go:";

    // Call Ollama with the intelligent prompt
    $ollamaUrl = OLLAMA_API_URL;
    $data = [
        'model' => 'llama3.2',
        'prompt' => $systemPrompt,
        'stream' => false,
        'options' => [ 
            'temperature' => 0.7,     // Balanced creativity
            'top_p' => 0.9,           // Diverse responses
            'num_predict' => 5000,    // Extended for comprehensive answers
            'repeat_penalty' => 1.1   // Avoid repetition
        ]
    ];
    $ch = curl_init($ollamaUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90); // Increased timeout for longer responses
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Connection timeout
    
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Check for curl errors first
    if ($curlError) {
        throw new Exception('Ollama connection failed: ' . $curlError . '. Make sure Ollama is running at ' . $ollamaUrl);
    }
    
    if (!$response) {
        throw new Exception('No response from Ollama. Make sure Ollama is running and accessible at ' . $ollamaUrl);
    }
    
    // Ollama returns 200 on success, but also parse the response
    $result = json_decode($response, true);
    if (!$result || !isset($result['response'])) {
        throw new Exception('Invalid response from Ollama. HTTP Code: ' . $httpCode);
    }
    
    $aiText = $result['response'] ?? '';
    if (empty($aiText)) {
        throw new Exception('Ollama returned empty response. Try rephrasing your question.');
    }
    
    // Return response with metadata
    echo json_encode([
        'success' => true, 
        'response' => $aiText,
        'metadata' => [
            'has_csv_data' => $hasRelevantData,
            'lessons_found' => count($relevantLessons),
            'intent_type' => $isReviewerRequest ? 'reviewer' : ($isDirectQuestion ? 'question' : 'general'),
            'csv_files_scanned' => count($csvFiles),
            'total_lessons_available' => count($allLessons)
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
