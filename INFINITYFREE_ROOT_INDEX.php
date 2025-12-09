<?php
/**
 * InfinityFree Root Redirect
 * Upload this file as index.php to /htdocs/ (not inside puta folder)
 * It will redirect visitors from the root to the /puta/ subfolder
 */

// Redirect to the puta folder where the actual app is
header('Location: /puta/');
exit;
