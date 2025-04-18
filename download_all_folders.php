<?php
// Set time limit to 0 to avoid timeout
set_time_limit(0);

// Include required classes
require_once __DIR__ . '/pinterest_scraper/PinterestScraper.php';
require_once __DIR__ . '/pinterest_scraper/ImageDownloader.php';

// Path to links file
$linksFile = __DIR__ . '/links.txt';

// Path to ignore file
$ignoreFile = __DIR__ . '/ignore.txt';

// Check if links file exists
if (!file_exists($linksFile)) {
    header('HTTP/1.1 404 Not Found');
    echo 'Links file not found.';
    exit;
}

// Read links from file
$fileContent = file_get_contents($linksFile);
$links = explode("\n", $fileContent);
$links = array_filter($links, function($link) {
    return !empty(trim($link));
});
$links = array_map('trim', $links);

// Check if there are any links
if (empty($links)) {
    header('HTTP/1.1 404 Not Found');
    echo 'No links found.';
    exit;
}

// Read ignored image URLs
$ignoredUrls = [];
if (file_exists($ignoreFile)) {
    $ignoreContent = file_get_contents($ignoreFile);
    $ignoredUrls = explode("\n", $ignoreContent);
    $ignoredUrls = array_filter($ignoredUrls, function($url) {
        return !empty(trim($url));
    });
    $ignoredUrls = array_map('trim', $ignoredUrls);
}

// Create temporary directory for all folders
$tempDir = __DIR__ . '/temp_all_folders';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// Create ZIP archive
$zipFile = __DIR__ . '/all_pinterest_folders.zip';
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Failed to create ZIP archive.';
    exit;
}

// Process each link
foreach ($links as $link) {
    // Extract folder name from URL
    $urlPath = parse_url($link, PHP_URL_PATH);
    $pathParts = explode('/', trim($urlPath, '/'));
    $lastPart = end($pathParts);
    
    // Use the exact folder name as it appears in the URL
    $folderName = !empty($lastPart) ? $lastPart : 'pinterest_images';
    
    // Transliterate folder name if it contains non-ASCII characters
    if (preg_match('/[^\x20-\x7E]/', $folderName)) {
        $folderName = transliterate($folderName);
    }
    
    // Create folder in temporary directory
    $folderPath = $tempDir . '/' . $folderName;
    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0777, true);
    }
    
    // Initialize Pinterest scraper
    $scraper = new PinterestScraper($link, 10000);
    
    // Get image URLs
    $imageUrls = $scraper->getImageUrls();
    
    // Filter out ignored URLs
    if (!empty($ignoredUrls)) {
        $imageUrls = array_filter($imageUrls, function($url) use ($ignoredUrls) {
            return !in_array($url, $ignoredUrls);
        });
    }
    
    // Download images
    $downloader = new ImageDownloader($folderPath, $folderName);
    $downloader->downloadImages($imageUrls);
    
    // Add folder to ZIP archive
    addFolderToZip($zip, $folderPath, $folderName);
}

// Close ZIP archive
$zip->close();

// Send ZIP file to browser
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="all_pinterest_folders.zip"');
header('Content-Length: ' . filesize($zipFile));
readfile($zipFile);

// Delete temporary files
unlink($zipFile);
deleteDirectory($tempDir);

/**
 * Add folder to ZIP archive
 *
 * @param ZipArchive $zip ZIP archive
 * @param string $folder Folder path
 * @param string $zipFolder Folder name in ZIP
 */
function addFolderToZip($zip, $folder, $zipFolder) {
    if (!is_dir($folder)) return;
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folder),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = $zipFolder . '/' . substr($filePath, strlen($folder) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
}

/**
 * Delete directory recursively
 *
 * @param string $dir Directory path
 * @return bool
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) return false;
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    
    return rmdir($dir);
}

/**
 * Transliterate Cyrillic to Latin
 *
 * @param string $text Text to transliterate
 * @return string
 */
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
