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

// Validate link
if (strpos($link, 'pinterest.com') === false) {
    $response['message'] = 'Invalid Pinterest URL.';
    echo json_encode($response);
    exit;
}

// Path to links file
$linksFile = __DIR__ . '/links.txt';

// Read existing links
$existingLinks = [];
if (file_exists($linksFile)) {
    $fileContent = file_get_contents($linksFile);
    $existingLinks = explode("\n", $fileContent);
    $existingLinks = array_filter($existingLinks, function($existingLink) {
        return !empty(trim($existingLink));
    });
    $existingLinks = array_map('trim', $existingLinks);
}

// Check if link already exists
if (in_array($link, $existingLinks)) {
    $response['message'] = 'Link already exists.';
    $response['success'] = true; // Still consider it a success
    echo json_encode($response);
    exit;
}

// Add new link
$existingLinks[] = $link;

// Save links to file
$result = file_put_contents($linksFile, implode("\n", $existingLinks) . "\n");

if ($result !== false) {
    $response['success'] = true;
    $response['message'] = 'Link saved successfully.';
} else {
    $response['message'] = 'Failed to save link.';
}

// Return JSON response
echo json_encode($response);
