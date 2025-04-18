<?php
// Set time limit to avoid timeout
set_time_limit(60);

// Check if URL parameter is provided
if (empty($_GET['url'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'URL parameter is required.';
    exit;
}

// Get URL and filename
$url = $_GET['url'];
$filename = !empty($_GET['filename']) ? $_GET['filename'] : basename($url);

// Make sure filename has an extension
if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $filename)) {
    // Extract extension from URL
    $extension = 'jpg'; // Default extension
    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url, $matches)) {
        $extension = strtolower($matches[1]);
    }
    $filename .= '.' . $extension;
}

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Invalid URL.';
    exit;
}

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
    'Accept-Language: en-US,en;q=0.5',
    'Referer: https://www.pinterest.com/'
]);

// Execute cURL session
$image = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Error downloading image: ' . curl_error($ch);
    curl_close($ch);
    exit;
}

// Get HTTP status code
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($statusCode != 200) {
    header('HTTP/1.1 ' . $statusCode);
    echo 'HTTP Error: ' . $statusCode;
    curl_close($ch);
    exit;
}

// Get content type
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

// Close cURL session
curl_close($ch);

// Make sure filename has the correct extension based on content type
if (strpos($contentType, 'image/jpeg') !== false && !preg_match('/\.(jpg|jpeg)$/i', $filename)) {
    $filename = preg_replace('/\.[^.]*$/', '', $filename) . '.jpg';
} elseif (strpos($contentType, 'image/png') !== false && !preg_match('/\.png$/i', $filename)) {
    $filename = preg_replace('/\.[^.]*$/', '', $filename) . '.png';
} elseif (strpos($contentType, 'image/gif') !== false && !preg_match('/\.gif$/i', $filename)) {
    $filename = preg_replace('/\.[^.]*$/', '', $filename) . '.gif';
} elseif (strpos($contentType, 'image/webp') !== false && !preg_match('/\.webp$/i', $filename)) {
    $filename = preg_replace('/\.[^.]*$/', '', $filename) . '.webp';
}

// Set headers for download
header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($image));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output image
echo $image;
