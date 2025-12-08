<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$sheet = IOFactory::load('Sf excel/Sf2 excel format.xls')->getActiveSheet();
$maxRow = $sheet->getHighestRow();
$maxCol = $sheet->getHighestColumn();

echo "Dimensions: $maxRow rows x $maxCol columns\n\n";

// Read first 50 rows to understand structure
for ($row = 1; $row <= min(50, $maxRow); $row++) {
    for ($col = 'A'; $col <= $maxCol; $col++) {
        $cell = $sheet->getCell($col . $row);
        $value = $cell->getValue();
        echo $value . "\t";
    }
    echo "\n";
}

// Also check merged cells
echo "\n\nMerged Cells:\n";
foreach ($sheet->getMergeCells() as $mergeRange) {
    echo $mergeRange . "\n";
}
?>
