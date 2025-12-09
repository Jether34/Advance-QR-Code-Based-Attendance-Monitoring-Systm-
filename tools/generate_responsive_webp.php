<?php
// generate_responsive_webp.php
// Usage: php tools/generate_responsive_webp.php "uploads/System logo.jpg"
// Generates WebP variants at multiple widths beside the original file.

if ($argc < 2) {
    fwrite(STDERR, "Usage: php generate_responsive_webp.php <source-path>\n");
    exit(1);
}

$src = $argv[1];
if (!file_exists($src)) {
    fwrite(STDERR, "Source not found: $src\n");
    exit(2);
}

$sizes = [72, 150, 300, 512];
$info = @getimagesize($src);
if (!$info) {
    fwrite(STDERR, "Failed to read image info for $src\n");
    exit(3);
}

switch ($info[2]) {
    case IMAGETYPE_JPEG:
        $img = imagecreatefromjpeg($src);
        break;
    case IMAGETYPE_PNG:
        $img = imagecreatefrompng($src);
        break;
    case defined('IMAGETYPE_WEBP') ? IMAGETYPE_WEBP : -1:
        if (function_exists('imagecreatefromwebp')) {
            $img = imagecreatefromwebp($src);
        } else {
            fwrite(STDERR, "WebP source found but imagecreatefromwebp() not available.\n");
            exit(4);
        }
        break;
    default:
        fwrite(STDERR, "Unsupported image type for $src\n");
        exit(5);
}

$w = imagesx($img);
$h = imagesy($img);

$pathinfo = pathinfo($src);
$dir = $pathinfo['dirname'];
$basename = $pathinfo['filename'];

foreach ($sizes as $size) {
    $scale = min($size / $w, 1);
    $nw = (int)round($w * $scale);
    $nh = (int)round($h * $scale);
    $dst = imagecreatetruecolor($nw, $nh);
    // preserve alpha for PNGs
    imagesavealpha($dst, true);
    $trans_colour = imagecolorallocatealpha($dst, 255, 255, 255, 127);
    imagefill($dst, 0, 0, $trans_colour);
    imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

    $out = $dir . '/' . $basename . '-' . $nw . '.webp';
    if (function_exists('imagewebp')) {
        if (imagewebp($dst, $out, 80)) {
            echo "Created $out\n";
        } else {
            fwrite(STDERR, "Failed to write $out\n");
        }
    } elseif (class_exists('Imagick')) {
        try {
            $im = new Imagick($src);
            $im->resizeImage($nw, $nh, Imagick::FILTER_LANCZOS, 1, true);
            $im->setImageFormat('webp');
            $im->setImageCompressionQuality(80);
            $im->writeImage($out);
            echo "Created $out via Imagick\n";
        } catch (Exception $e) {
            fwrite(STDERR, "Imagick failed for $out: " . $e->getMessage() . "\n");
        }
    } else {
        fwrite(STDERR, "No WebP encoder available to write $out\n");
    }

    imagedestroy($dst);
}

imagedestroy($img);
echo "Responsive WebP generation complete.\n";
exit(0);
