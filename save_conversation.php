<?php
// save_conversation.php - Save reviewer conversation to database
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $question = trim($input['question'] ?? '');
    $response = trim($input['response'] ?? '');
    $user_id = (int)($input['user_id'] ?? 0);
    $conversation_id = $input['conversation_id'] ?? null;
    if (!$question || !$response || !$user_id) throw new Exception('Missing data');

    $pdo = get_db();
    
    // If conversation_id exists, update the conversation
    if ($conversation_id) {
        $stmt = $pdo->prepare('UPDATE reviewer_conversations SET question = CONCAT(question, ?, "\n"), response = CONCAT(response, ?, "\n"), updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute(["\n" . $question, "\n" . $response, $conversation_id, $user_id]);
        echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);
    } else {
        // Create new conversation
        $stmt = $pdo->prepare('INSERT INTO reviewer_conversations (user_id, question, response, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
        $stmt->execute([$user_id, $question, $response]);
        $newId = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'conversation_id' => $newId]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
