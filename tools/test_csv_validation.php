<?php
// tools/test_csv_validation.php - CLI smoke tests for CSV validation helper
if (php_sapi_name() !== 'cli') {
    echo "This script is CLI-only.\n";
    exit(1);
}
require_once __DIR__ . '/csv_validation_helper.php';

$tests = [];

// Valid CSV
$tests['valid'] = "topic,content,difficulty,subject\n" .
    "Photosynthesis,Photosynthesis is the process by which plants convert light into chemical energy. It involves chlorophyll and occurs in chloroplasts.,Easy,Biology\n" .
    "Cell Structure,Cells have membranes and organelles. The nucleus contains DNA.,Medium,Biology\n";

// Invalid header
$tests['bad_header'] = "title,body,level,subject\nTopic,Content,Easy,Biology\n";

// Malformed row
$tests['malformed_row'] = "topic,content,difficulty,subject\nOnlyTopic\n";

// Invalid difficulty
$tests['invalid_difficulty'] = "topic,content,difficulty,subject\n" .
    "Newton's Laws,Newton's laws describe motion and force.,Beginner,Physics\n";

foreach ($tests as $name => $csv) {
    echo "\n=== Test: $name ===\n";
    $ok = validateAndSanitizeCsv($csv, $clean, $count, $errors);
    if ($ok) {
        echo "PASS - lessons={$count}\n";
        echo "Sanitized CSV:\n" . $clean . "\n";
        if (!empty($errors)) {
            echo "Warnings:\n" . implode("\n", $errors) . "\n";
        }
    } else {
        echo "FAIL\nErrors:\n" . implode("\n", $errors) . "\n";
    }
}

echo "\nSmoke tests complete.\n";

?>