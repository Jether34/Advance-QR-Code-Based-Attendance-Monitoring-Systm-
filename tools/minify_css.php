<?php
// tools/minify_css.php
// Simple CSS minifier: reads style.css and writes style.min.css
// Usage: php tools/minify_css.php

$cwd = __DIR__ . '/../';
$src = $cwd . 'style.css';
$dest = $cwd . 'style.min.css';

if (!file_exists($src)) {
    fwrite(STDERR, "Source CSS not found: $src\n");
    exit(1);
}

$css = file_get_contents($src);
if ($css === false) {
    fwrite(STDERR, "Failed to read $src\n");
    exit(2);
}

// Remove comments
$min = preg_replace('!/\*.*?\*/!s', '', $css);
// Remove newlines/tabs and collapse whitespace
$min = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $min);
$min = preg_replace('/\s+/', ' ', $min);
// Remove space around symbols
$min = preg_replace('/\s*([{};:,])\s*/', '$1', $min);
$min = trim($min);

if (file_put_contents($dest, $min) === false) {
    fwrite(STDERR, "Failed to write $dest\n");
    exit(3);
}

echo "Created $dest\n";
exit(0);
