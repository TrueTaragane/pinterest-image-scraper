<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => true,
    'urls' => []
];

// Path to ignore file
$ignoreFile = __DIR__ . '/ignore.txt';

// Check if file exists
if (file_exists($ignoreFile)) {
    // Read file content
    $fileContent = file_get_contents($ignoreFile);
    
    // Split by new line
    $urls = explode("\n", $fileContent);
    
    // Filter empty lines
    $urls = array_filter($urls, function($url) {
        return !empty(trim($url));
    });
    
    // Trim each URL
    $urls = array_map('trim', $urls);
    
    // Set URLs in response
    $response['urls'] = array_values($urls);
} else {
    // Create empty file if it doesn't exist
    file_put_contents($ignoreFile, '');
}

// Return JSON response
echo json_encode($response);
