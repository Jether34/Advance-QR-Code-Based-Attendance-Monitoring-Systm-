<?php
// Test PDF extraction with the new library
require_once __DIR__ . '/vendor/autoload.php';

use Smalot\PdfParser\Parser;

echo "=== PDF Extraction Test ===\n\n";

// Test with any available PDF
$testFiles = [
    'converted_docpose_sf2-january-2.pdf',
    'SF2 Monthly Attendance - 2025-11.pdf',
    'sf2.pdf'
];

foreach ($testFiles as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "Testing file: $file\n";
        echo str_repeat('-', 50) . "\n";
        
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            
            echo "Extracted length: " . strlen($text) . " characters\n";
            echo "First 500 characters:\n";
            echo substr($text, 0, 500) . "\n";
            echo str_repeat('=', 50) . "\n\n";
            
            break; // Just test one file
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n\n";
        }
    }
}

echo "\nIf you see extracted text above, the PDF parser is working correctly!\n";
echo "Now try uploading your Filipino or Pre-Calculus PDF through the student dashboard.\n";
