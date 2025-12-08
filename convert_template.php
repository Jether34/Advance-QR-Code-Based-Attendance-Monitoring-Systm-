<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$src = 'Sf excel/SF2 Format.xls';
$dest = 'Sf excel/sf2_template_converted.xlsx';
$wb = IOFactory::load($src);
$writer = IOFactory::createWriter($wb, 'Xlsx');
$writer->save($dest);
echo "Converted to $dest\n";
