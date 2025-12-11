<?php
// tools/csv_validation_helper.php
// Helper to validate and sanitize CSV content produced by AI before saving

function validateAndSanitizeCsv($csvContent, &$cleanCsv, &$lessonCount, &$errors) {
    $errors = [];
    $lines = preg_split('/\r?\n/', trim($csvContent));
    if (count($lines) === 0) {
        $errors[] = 'Empty CSV content';
        return false;
    }

    $parsedHeader = str_getcsv(array_shift($lines));
    $expectedHeader = ['topic','content','difficulty','subject'];
    $normHeader = array_map('strtolower', array_map('trim', $parsedHeader));
    if ($normHeader !== $expectedHeader) {
        $errors[] = 'CSV header mismatch. Expected: ' . implode(',', $expectedHeader);
        return false;
    }

    $maxRows = 500;
    $rowCount = 0;
    $allowedDiff = ['Easy','Medium','Hard'];
    $outRows = [];

    foreach ($lines as $ln) {
        if (trim($ln) === '') continue;
        $row = str_getcsv($ln);
        if (count($row) < 4) {
            $errors[] = 'Malformed CSV row (fewer than 4 columns)';
            return false;
        }

        $topic = trim(strip_tags($row[0]));
        $content = trim(strip_tags($row[1]));
        $content = preg_replace('/\s+/', ' ', $content);
        $difficulty = trim($row[2]);
        $subject = trim(strip_tags($row[3]));

        // Normalize difficulty
        $diffNorm = ucfirst(strtolower($difficulty));
        if (!in_array($diffNorm, $allowedDiff, true)) {
            // Normalize invalid values to Medium and note warning
            $errors[] = "Invalid difficulty '{$difficulty}' detected — normalized to 'Medium' for topic: {$topic}";
            $diffNorm = 'Medium';
        }

        // Enforce sensible length limits
        $topic = mb_substr($topic, 0, 200);
        $content = mb_substr($content, 0, 2000);
        $subject = mb_substr($subject, 0, 100);

        if ($topic === '' || $content === '') {
            $errors[] = 'Empty topic or content in a row';
            return false;
        }

        $outRows[] = [$topic, $content, $diffNorm, $subject];
        $rowCount++;
        if ($rowCount > $maxRows) {
            $errors[] = 'Too many rows';
            return false;
        }
    }

    // Build a sanitized CSV using fputcsv to ensure proper escaping
    $fp = fopen('php://temp', 'r+');
    fputcsv($fp, $expectedHeader);
    foreach ($outRows as $r) {
        fputcsv($fp, $r);
    }
    rewind($fp);
    $cleanCsv = stream_get_contents($fp);
    fclose($fp);

    $lessonCount = $rowCount;
    return true;
}

?>
