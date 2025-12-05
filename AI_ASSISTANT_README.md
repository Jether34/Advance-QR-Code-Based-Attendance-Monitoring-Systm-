# AI Assistant Integration Guide

## Overview
The developer dashboard now includes an AI Assistant with full access to system data and analytics.

## Current Implementation
- **Rule-based responses**: Intelligent pattern matching for common queries
- **Real-time data access**: Queries database for live stats
- **Context-aware**: Adapts responses based on question type
- **Secure**: Developer-only access

## Supported Queries
1. **Security Analysis**
   - "Give me a security analysis"
   - "Show failed login attempts"
   - "Any suspicious activity?"
   - "Check for threats"

2. **Attendance Insights**
   - "Show attendance trends"
   - "Which class has best attendance?"
   - "Attendance overview"

3. **Device/Platform Stats**
   - "What devices are being used?"
   - "Show platform breakdown"
   - "Mobile vs desktop usage"

4. **Performance Monitoring**
   - "Check system performance"
   - "Any slow operations?"
   - "Show latency metrics"

5. **System Overview**
   - "System status"
   - "Give me an overview"
   - "What's happening today?"

---

## Integrating External LLM (OpenAI/Azure OpenAI)

### Option 1: OpenAI API

1. **Install OpenAI PHP Client**
```bash
composer require openai-php/client
```

2. **Update `developer_ai_assistant.php`**

Replace the response generation section (line ~140) with:

```php
// Add at top after require statements
require 'vendor/autoload.php';

$client = OpenAI::client('YOUR_OPENAI_API_KEY');

// Replace generateIntelligentResponse() call with:
try {
    $result = $client->chat()->create([
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $question],
        ],
        'temperature' => 0.7,
        'max_tokens' => 500,
    ]);
    
    $response = $result->choices[0]->message->content;
    
} catch (Exception $e) {
    $response = "AI service unavailable. Error: " . $e->getMessage();
}
```

3. **Add API key to `.env`**
```
OPENAI_API_KEY=sk-...
```

---

### Option 2: Azure OpenAI

1. **Install Azure SDK**
```bash
composer require azure/azure-ai-openai
```

2. **Update `developer_ai_assistant.php`**

```php
use Azure\AI\OpenAI\OpenAIClient;
use Azure\Core\Credentials\AzureKeyCredential;

$endpoint = "https://YOUR_RESOURCE.openai.azure.com/";
$apiKey = "YOUR_AZURE_OPENAI_KEY";
$deployment = "YOUR_DEPLOYMENT_NAME"; // e.g., "gpt-4"

$client = new OpenAIClient($endpoint, new AzureKeyCredential($apiKey));

$result = $client->getChatCompletions($deployment, [
    [
        'role' => 'system',
        'content' => $systemPrompt
    ],
    [
        'role' => 'user',
        'content' => $question
    ]
], [
    'temperature' => 0.7,
    'max_tokens' => 500,
]);

$response = $result->choices[0]->message->content;
```

---

### Option 3: Local LLM (Ollama)

For offline/on-premises deployment:

1. **Install Ollama** (https://ollama.ai)
```bash
ollama pull llama2
```

2. **Update `developer_ai_assistant.php`**

```php
function callLocalLLM($systemPrompt, $question) {
    $ch = curl_init('http://localhost:11434/api/generate');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'llama2',
        'prompt' => $systemPrompt . "\n\nUser: " . $question . "\n\nAssistant:",
        'stream' => false,
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    return $result['response'] ?? 'No response from local LLM';
}

// Use it:
$response = callLocalLLM($systemPrompt, $question);
```

---

## Data Privacy & Security

### What AI Has Access To:
- ✅ Aggregate statistics (counts, averages)
- ✅ Event patterns and trends
- ✅ Performance metrics
- ✅ Device/platform data
- ❌ **NO** raw passwords or sensitive PII

### Security Best Practices:
1. **Never log API keys** in code or version control
2. **Use environment variables** for credentials
3. **Sanitize context data** before sending to external APIs
4. **Rate limit** AI requests to prevent abuse
5. **Audit AI queries** in system_events table

### Recommended: Add AI Query Logging

Add to `developer_ai_assistant.php` before response:

```php
log_event($pdo, 'ai_query', [
    'user_role' => 'developer',
    'user_id' => $_SESSION['developer_id'],
    'context_json' => [
        'question' => $question,
        'response_length' => strlen($response),
    ],
]);
```

---

## Cost Optimization

### OpenAI Pricing (as of 2025)
- GPT-4: ~$0.03 per 1K input tokens, ~$0.06 per 1K output tokens
- GPT-3.5-Turbo: ~$0.0015 per 1K input tokens, ~$0.002 per 1K output tokens

### Tips:
1. **Use GPT-3.5-Turbo** for most queries; reserve GPT-4 for complex analysis
2. **Cache common queries** for 5-10 minutes
3. **Limit context size** - only send relevant data
4. **Set max_tokens** to 500-1000 to control costs

### Example Caching:

```php
$cacheKey = 'ai_' . md5($question);
$cached = apcu_fetch($cacheKey);

if ($cached !== false) {
    echo json_encode(['success' => true, 'answer' => $cached, 'cached' => true]);
    exit;
}

// ... call LLM ...

apcu_store($cacheKey, $response, 300); // Cache 5 minutes
```

---

## Testing

Test the AI assistant with these queries:
1. "Give me a security analysis"
2. "Show attendance trends for the last week"
3. "What devices are students using?"
4. "Check system performance"
5. "Are there any security threats?"

The assistant should provide data-driven, actionable responses.

---

## Production Checklist

- [ ] Add API key to `.env` (never commit!)
- [ ] Enable rate limiting (max 30 queries/hour per developer)
- [ ] Log all AI queries to system_events
- [ ] Test with real data
- [ ] Monitor API costs
- [ ] Add fallback to rule-based responses if API fails
- [ ] Sanitize user input to prevent prompt injection
- [ ] Add "Export Conversation" button for developers

---

## Future Enhancements

1. **Multi-turn conversations**: Store chat history per session
2. **Data visualization**: AI generates chart.js configs
3. **Predictive analytics**: Forecast attendance trends
4. **Anomaly detection**: Auto-alert on unusual patterns
5. **Natural language SQL**: "Show me students absent more than 3 days"
6. **Voice input**: Speech-to-text for hands-free queries
7. **Scheduled reports**: AI-generated daily/weekly summaries
8. **Fine-tuning**: Train on school-specific patterns

---

## Support

For issues or questions, contact the development team or refer to:
- OpenAI Docs: https://platform.openai.com/docs
- Azure OpenAI: https://learn.microsoft.com/azure/ai-services/openai/
- Ollama: https://ollama.ai/docs
