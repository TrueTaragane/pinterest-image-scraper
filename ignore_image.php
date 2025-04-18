<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method. Only POST requests are allowed.';
    echo json_encode($response);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required parameters
if (empty($data['image_url'])) {
    $response['message'] = 'Image URL is required.';
    echo json_encode($response);
    exit;
}

// Get image URL
$imageUrl = trim($data['image_url']);

// Path to ignore file
$ignoreFile = __DIR__ . '/ignore.txt';

// Read existing ignored URLs
$existingUrls = [];
if (file_exists($ignoreFile)) {
    $fileContent = file_get_contents($ignoreFile);
    $existingUrls = explode("\n", $fileContent);
    $existingUrls = array_filter($existingUrls, function($existingUrl) {
        return !empty(trim($existingUrl));
    });
    $existingUrls = array_map('trim', $existingUrls);
}

// Check if URL already exists
if (in_array($imageUrl, $existingUrls)) {
    $response['message'] = 'Image URL already ignored.';
    $response['success'] = true; // Still consider it a success
    echo json_encode($response);
    exit;
}

// Add new URL
$existingUrls[] = $imageUrl;

// Save URLs to file
$result = file_put_contents($ignoreFile, implode("\n", $existingUrls) . "\n");

if ($result !== false) {
    $response['success'] = true;
    $response['message'] = 'Image URL added to ignore list.';
} else {
    $response['message'] = 'Failed to add image URL to ignore list.';
}

// Return JSON response
echo json_encode($response);
