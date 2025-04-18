<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Include required classes
require_once __DIR__ . '/PinterestScraper.php';
require_once __DIR__ . '/ImageDownloader.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method. Only POST requests are allowed.';
    echo json_encode($response);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// If form data is sent instead of JSON
if (empty($data)) {
    $data = $_POST;
}

// Validate required parameters
if (empty($data['pinterest_url'])) {
    $response['message'] = 'Pinterest URL is required.';
    echo json_encode($response);
    exit;
}

// Get parameters
$pinterestUrl = trim($data['pinterest_url']);
$numImages = 10000; // Always use maximum number of images
$downloadImages = isset($data['download_images']) ? filter_var($data['download_images'], FILTER_VALIDATE_BOOLEAN) : false;

// Extract folder name from URL if not provided
if (empty($data['folder_name'])) {
    // Parse URL to get the last part of the path
    $urlPath = parse_url($pinterestUrl, PHP_URL_PATH);
    $pathParts = explode('/', trim($urlPath, '/'));
    $lastPart = end($pathParts);

    // Use the exact folder name as it appears in the URL
    $folderName = !empty($lastPart) ? $lastPart : 'pinterest_images';
} else {
    $folderName = $data['folder_name'];
}

// Process the URL to ensure it's valid
if (strpos($pinterestUrl, 'http') !== 0) {
    $pinterestUrl = 'https://' . $pinterestUrl;
}

// Handle special characters in URL (like Cyrillic)
$parsedUrl = parse_url($pinterestUrl);
if (isset($parsedUrl['path'])) {
    $pathParts = explode('/', trim($parsedUrl['path'], '/'));

    // Encode path parts that might contain non-ASCII characters
    foreach ($pathParts as &$part) {
        // Only encode if it contains non-ASCII characters and isn't already encoded
        if (preg_match('/[^\x20-\x7E]/', $part) && urldecode($part) === $part) {
            $part = urlencode($part);
        }
    }

    // Rebuild the URL
    $path = '/' . implode('/', $pathParts) . '/';
    $pinterestUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $path;
}

// Save the Pinterest URL for later use
file_put_contents(__DIR__ . '/../last_pinterest_url.txt', $pinterestUrl);

// Always use maximum number of images (10000)

try {
    // Initialize scraping process

    // Initialize Pinterest scraper
    $scraper = new PinterestScraper($pinterestUrl, $numImages);

    // Get image URLs
    $imageUrls = $scraper->getImageUrls();

    // Check if any images were found
    if (empty($imageUrls) || (is_array($imageUrls) && isset($imageUrls['error']))) {
        if (is_array($imageUrls) && isset($imageUrls['error'])) {
            $response['message'] = 'Error: ' . $imageUrls['error'];
        } else {
            $response['message'] = 'No images found. Please check the Pinterest URL and try again.';
        }
        echo json_encode($response);
        exit;
    }

    // Set response data
    $response['success'] = true;
    $response['message'] = count($imageUrls) . ' images found.';
    $response['data']['image_urls'] = $imageUrls;

    // Download images if requested
    if ($downloadImages) {
        $outputDir = 'downloaded_images/' . $folderName;
        $downloader = new ImageDownloader($outputDir, $folderName);
        $downloadResults = $downloader->downloadImages($imageUrls);

        $response['data']['download_results'] = $downloadResults;
        $response['message'] .= ' ' . count(array_filter($downloadResults, function($result) {
            return $result['success'];
        })) . ' images downloaded successfully.';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    // Handle exception
}

// Return JSON response
echo json_encode($response);
