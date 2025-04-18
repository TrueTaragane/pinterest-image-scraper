<?php
// Set headers for binary response
header('Content-Type: application/octet-stream');

// Function to transliterate Cyrillic to Latin
function transliterate($text) {
    $cyrillicToLatin = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
        'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
        'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
        'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
        'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
        'Я' => 'Ya'
    ];

    return strtr($text, $cyrillicToLatin);
}

// Headers will be set after determining the folder name

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method. Only POST requests are allowed.']);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// If form data is sent instead of JSON
if (empty($data)) {
    $data = $_POST;
}

// Validate required parameters
if (empty($data['image_urls'])) {
    echo json_encode(['error' => 'Image URLs are required.']);
    exit;
}

// Get parameters
$imageUrls = $data['image_urls'];

// Load ignored image URLs
$ignoredUrls = [];
$ignoreFile = __DIR__ . '/../ignore.txt';
if (file_exists($ignoreFile)) {
    $ignoreContent = file_get_contents($ignoreFile);
    $ignoredUrls = explode("\n", $ignoreContent);
    $ignoredUrls = array_filter($ignoredUrls, function($url) {
        return !empty(trim($url));
    });
    $ignoredUrls = array_map('trim', $ignoredUrls);
}

// Filter out ignored URLs
if (!empty($ignoredUrls)) {
    $imageUrls = array_filter($imageUrls, function($url) use ($ignoredUrls) {
        return !in_array($url, $ignoredUrls);
    });
    $imageUrls = array_values($imageUrls); // Re-index array
}

// Extract folder name from URL if not provided
if (empty($data['folder_name'])) {
    // Get Pinterest URL directly from the form data
    $pinterestUrl = isset($data['pinterest_url']) ? $data['pinterest_url'] : '';

    if (empty($pinterestUrl)) {
        // Try to get the URL from the saved file
        $lastUrlFile = __DIR__ . '/../last_pinterest_url.txt';
        if (file_exists($lastUrlFile)) {
            $pinterestUrl = trim(file_get_contents($lastUrlFile));
        }

        // Fallback to referer if URL not provided in form and not saved
        if (empty($pinterestUrl)) {
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

            // Extract Pinterest URL from referer query string
            if (preg_match('/pinterest_url=([^&]+)/', $referer, $matches)) {
                $pinterestUrl = urldecode($matches[1]);
            }
        }
    }

    if (!empty($pinterestUrl)) {
        // Parse URL to get the last part of the path
        $urlPath = parse_url($pinterestUrl, PHP_URL_PATH);
        $pathParts = explode('/', trim($urlPath, '/'));
        $lastPart = end($pathParts);

        // Decode URL-encoded characters
        $lastPart = urldecode($lastPart);

        // Transliterate folder name from Cyrillic to Latin
        $transliteratedName = transliterate($lastPart);

        // Use the transliterated folder name
        $folderName = !empty($transliteratedName) ? $transliteratedName : 'pinterest_images';
    } else {
        $folderName = 'pinterest_images';
    }
} else {
    $folderName = $data['folder_name'];
}

// Set the remaining headers with the determined folder name
header('Content-Disposition: attachment; filename="' . $folderName . '.zip"');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// Create temporary directory
$tempDir = sys_get_temp_dir() . '/' . uniqid('pinterest_');
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// Download images to temporary directory
foreach ($imageUrls as $index => $imageUrl) {
    $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (empty($extension) || strlen($extension) > 4) {
        $extension = 'jpg'; // Default extension
    }

    $fileName = $tempDir . '/' . $folderName . '_' . ($index + 1) . '.' . $extension;

    // Download image using cURL (more reliable)
    $ch = curl_init($imageUrl);
    $fp = fopen($fileName, 'wb');

    if ($fp) {
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
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

        curl_exec($ch);

        // Check if file was downloaded successfully
        if (curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            // If download failed, remove the file
            fclose($fp);
            if (file_exists($fileName)) {
                unlink($fileName);
            }
        } else {
            fclose($fp);

            // Check if file is too small (likely an error page)
            if (file_exists($fileName) && filesize($fileName) < 1000) { // Less than 1KB
                unlink($fileName);
            }
        }

        curl_close($ch);
    }
}

// Create ZIP archive
$zipFile = sys_get_temp_dir() . '/' . uniqid('pinterest_') . '.zip';
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($tempDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = basename($filePath);

            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();

    // Output ZIP file
    readfile($zipFile);

    // Clean up
    unlink($zipFile);

    // Remove temporary directory and files
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }

    rmdir($tempDir);
} else {
    echo json_encode(['error' => 'Could not create ZIP archive.']);
}
