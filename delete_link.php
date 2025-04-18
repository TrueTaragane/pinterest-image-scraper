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
if (empty($data['link'])) {
    $response['message'] = 'Link is required.';
    echo json_encode($response);
    exit;
}

// Get link
$link = trim($data['link']);

// Path to links file
$linksFile = __DIR__ . '/links.txt';

// Check if file exists
if (!file_exists($linksFile)) {
    $response['message'] = 'Links file does not exist.';
    echo json_encode($response);
    exit;
}

// Read existing links
$fileContent = file_get_contents($linksFile);
$existingLinks = explode("\n", $fileContent);
$existingLinks = array_filter($existingLinks, function($existingLink) use ($link) {
    return !empty(trim($existingLink)) && trim($existingLink) !== $link;
});

// Save links to file
$result = file_put_contents($linksFile, implode("\n", $existingLinks) . "\n");

if ($result !== false) {
    $response['success'] = true;
    $response['message'] = 'Link deleted successfully.';
} else {
    $response['message'] = 'Failed to delete link.';
}

// Return JSON response
echo json_encode($response);
