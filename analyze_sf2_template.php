<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$path = 'Sf excel/sf2_template_converted.xlsx';
$sheet = IOFactory::load($path)->getActiveSheet();
$targets = ['No', 'NAME', 'Remarks', 'ABSENT', 'TARDY', 'GUIDELINES', 'Month'];

// dump first 200 non-empty cells to identify coordinates
echo "First 200 non-empty cells:\n";
$count = 0;
foreach ($sheet->getCellCollection() as $cell) {
    $val = $sheet->getCell($cell)->getValue();
    if ($val !== null && $val !== '') {
        echo $cell . ' = ' . (is_object($val) ? '[obj]' : $val) . "\n";
        $count++;
        if ($count >= 200) break;
    }
}

// show all formula cells to ensure we preserve them
echo "\nFormula cells:\n";
$formulas = [];
foreach ($sheet->getCellCollection() as $cellId) {
    $cell = $sheet->getCell($cellId);
    if ($cell->isFormula()) {
        $formulas[$cellId] = $cell->getValue();
    }
}
ksort($formulas);
if ($formulas) {
    foreach ($formulas as $k => $v) {
        echo $k . ' = ' . $v . "\n";
    }
} else {
    echo "(none)\n";
}

echo "\nSearch targets:\n";
foreach ($targets as $t) {
    $found = [];
    foreach ($sheet->getCellCollection() as $cell) {
        $val = (string)$sheet->getCell($cell)->getValue();
        if ($val !== '' && stripos($val, $t) !== false) {
            $found[] = $cell . ' = ' . $val;
        }
    }
    echo "--- $t ---\n";
    if ($found) {
        echo implode("\n", $found) . "\n";
    } else {
        echo "(not found)\n";
    }
}

// dump non-empty cells for first 70 rows
echo "\nSample rows with non-empty cells (1-70):\n";
for ($r=1; $r<=70; $r++) {
    $rowVals = [];
    foreach (range('A','BZ') as $col) {
        $cellId = $col.$r;
        $v = $sheet->getCell($cellId)->getValue();
        if ($v !== null && $v !== '') {
            $rowVals[] = $cellId.'='.$v;
        }
    }
    if ($rowVals) echo $r.': '.implode(' | ', $rowVals)."\n";
}

// show full row 15-20 across columns A to AZ
echo "\nRows 15-20 all columns:\n";
for ($r=15; $r<=20; $r++) {
    $cells = [];
    foreach (range('A','AZ') as $col) {
        $cellId = $col.$r;
        $v = $sheet->getCell($cellId)->getValue();
        $cells[] = $cellId.'='.(($v===null||$v==='')?'.':$v);
    }
    echo $r.': '.implode(' | ',$cells)."\n";
}
?>
