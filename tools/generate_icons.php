<?php
// generate_icons.php - create PNG app icons from uploads/System logo.jpg
// Usage: php tools/generate_icons.php

$src = __DIR__ . '/../uploads/System logo.jpg';
$outDir = __DIR__ . '/../webapp/icons';
$sizes = [
    192 => 'icon-192.png',
    512 => 'icon-512.png',
    180 => 'apple-touch-180.png',
    152 => 'apple-touch-152.png',
];

if (!file_exists($src)) {
    fwrite(STDERR, "Source logo not found: $src\n");
    exit(1);
}

if (!is_dir($outDir)) mkdir($outDir, 0755, true);

$info = @getimagesize($src);
if (!$info) {
    fwrite(STDERR, "Failed to read image info for $src\n");
    exit(2);
}

switch ($info[2]) {
    case IMAGETYPE_JPEG:
        $img = imagecreatefromjpeg($src);
        break;
    case IMAGETYPE_PNG:
        $img = imagecreatefrompng($src);
        break;
    default:
        fwrite(STDERR, "Unsupported image type for $src\n");
        exit(3);
}

$w = imagesx($img);
$h = imagesy($img);

foreach ($sizes as $size => $filename) {
    $dstPath = $outDir . '/' . $filename;
    $dst = imagecreatetruecolor($size, $size);
    // preserve transparency
    imagesavealpha($dst, true);
    $trans_colour = imagecolorallocatealpha($dst, 255, 255, 255, 127);
    imagefill($dst, 0, 0, $trans_colour);

    // compute scaled size
    $scale = min($size / $w, $size / $h);
    $nw = (int)round($w * $scale);
    $nh = (int)round($h * $scale);
    $dx = (int)(($size - $nw) / 2);
    $dy = (int)(($size - $nh) / 2);

    imagecopyresampled($dst, $img, $dx, $dy, 0, 0, $nw, $nh, $w, $h);
    if (!imagepng($dst, $dstPath)) {
        fwrite(STDERR, "Failed to write $dstPath\n");
    } else {
        echo "Created $dstPath\n";
    }
    imagedestroy($dst);
}

imagedestroy($img);
echo "Icon generation complete.\n";
?>
