<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => true,
    'links' => []
];

// Path to links file
$linksFile = __DIR__ . '/links.txt';

// Check if file exists
if (file_exists($linksFile)) {
    // Read file content
    $fileContent = file_get_contents($linksFile);
    
    // Split by new line
    $links = explode("\n", $fileContent);
    
    // Filter empty lines
    $links = array_filter($links, function($link) {
        return !empty(trim($link));
    });
    
    // Trim each link
    $links = array_map('trim', $links);
    
    // Set links in response
    $response['links'] = array_values($links);
} else {
    // Create empty file if it doesn't exist
    file_put_contents($linksFile, '');
}

// Return JSON response
echo json_encode($response);
