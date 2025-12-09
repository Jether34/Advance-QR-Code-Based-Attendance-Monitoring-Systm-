<?php
// CLI helper: check and apply the add_correction_columns.sql migration
// Usage: php tools/check_and_apply_corrections_migration.php [--apply]

require_once __DIR__ . '/../db.php';

$apply = in_array('--apply', $argv, true);

try {
    $pdo = get_db();
} catch (PDOException $e) {
    fwrite(STDERR, "Failed to connect to DB: " . $e->getMessage() . PHP_EOL);
    exit(2);
}

// Check existing columns
$stmt = $pdo->prepare("DESCRIBE attendance_records");
$stmt->execute();
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

$needed = ['correction_reason', 'corrected_by', 'corrected_at'];
$missing = array_values(array_filter($needed, function($c) use ($cols) { return !in_array($c, $cols); }));

if (empty($missing)) {
    echo "All correction columns already present: " . implode(', ', $needed) . PHP_EOL;
    exit(0);
}

echo "Missing columns detected: " . implode(', ', $missing) . PHP_EOL;

$sqlFile = __DIR__ . '/../add_correction_columns.sql';
if (!file_exists($sqlFile)) {
    fwrite(STDERR, "Migration SQL file not found: $sqlFile" . PHP_EOL);
    exit(3);
}

$sql = file_get_contents($sqlFile);
// Split by semicolon to execute statements separately
$parts = array_filter(array_map('trim', explode(';', $sql)));

echo "Migration file found: $sqlFile\n";
echo "Statements to run: " . count($parts) . PHP_EOL;

if (!$apply) {
    echo "Dry-run mode. To apply changes, re-run with --apply\n";
    exit(0);
}

try {
    // ALTER TABLE statements are not transactional on many MySQL setups.
    // Execute statements directly and report errors per statement.
    foreach ($parts as $p) {
        if ($p === '') continue;
        $pdo->exec($p);
    }
    echo "Migration applied successfully.\n";
    exit(0);
} catch (Exception $e) {
    fwrite(STDERR, "Failed to apply migration: " . $e->getMessage() . PHP_EOL);
    exit(4);
}

?>