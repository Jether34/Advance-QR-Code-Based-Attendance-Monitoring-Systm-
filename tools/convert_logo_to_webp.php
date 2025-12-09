<?php
// convert_logo_to_webp.php
// Convert uploads/System logo.jpg -> uploads/System logo.webp (quality 80)
// Usage: php tools/convert_logo_to_webp.php

$src = __DIR__ . '/../uploads/System logo.jpg';
$dest = __DIR__ . '/../uploads/System logo.webp';

if (!file_exists($src)) {
    fwrite(STDERR, "Source logo not found: $src\n");
    exit(1);
}

$info = @getimagesize($src);
if (!$info) {
    fwrite(STDERR, "Failed to read image info for $src\n");
    exit(2);
}

// create image resource from JPEG source
if ($info[2] === IMAGETYPE_JPEG) {
    $img = @imagecreatefromjpeg($src);
} elseif ($info[2] === IMAGETYPE_PNG) {
    $img = @imagecreatefrompng($src);
} else {
    fwrite(STDERR, "Unsupported source image type for $src\n");
    exit(3);
}

if (!$img) {
    fwrite(STDERR, "Failed to create image resource from $src\n");
    exit(4);
}

// try GD WebP first
if (function_exists('imagewebp')) {
    $ok = imagewebp($img, $dest, 80);
    if ($ok) {
        echo "Created $dest\n";
        imagedestroy($img);
        exit(0);
    } else {
        fwrite(STDERR, "imagewebp() failed to write $dest\n");
    }
}

// fallback to Imagick if available
if (class_exists('Imagick')) {
    try {
        $im = new Imagick($src);
        $im->setImageFormat('webp');
        $im->setImageCompressionQuality(80);
        $im->writeImage($dest);
        echo "Created $dest via Imagick\n";
        exit(0);
    } catch (Exception $e) {
        fwrite(STDERR, "Imagick conversion failed: " . $e->getMessage() . "\n");
    }
}

fwrite(STDERR, "No WebP encoder available (GD with WebP or Imagick required).\n");
imagedestroy($img);
exit(5);
