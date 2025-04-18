<?php
/**
 * Image Downloader Class
 * 
 * A PHP class to download images from URLs
 */
class ImageDownloader {
    private $outputDir;
    private $namePrefix;
    
    /**
     * Constructor
     * 
     * @param string $outputDir Directory to save images
     * @param string $namePrefix Prefix for image filenames
     */
    public function __construct($outputDir = 'downloaded_images', $namePrefix = 'pinterest_image') {
        $this->outputDir = $outputDir;
        $this->namePrefix = $namePrefix;
        
        // Create output directory if it doesn't exist
        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }
    }
    
    /**
     * Download a single image
     * 
     * @param string $imageUrl URL of the image to download
     * @param string $fileName Name to save the file as
     * @return array Status of the download operation
     */
    public function downloadImage($imageUrl, $fileName) {
        $filePath = $this->outputDir . '/' . $fileName;
        
        // Initialize cURL session
        $ch = curl_init($imageUrl);
        
        // Open file for writing
        $fp = fopen($filePath, 'wb');
        if (!$fp) {
            return [
                'success' => false,
                'message' => "Could not open file for writing: $filePath"
            ];
        }
        
        // Set cURL options with more browser-like behavior
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
        
        // Execute cURL session
        curl_exec($ch);
        
        // Check for errors
        if (curl_errno($ch)) {
            fclose($fp);
            return [
                'success' => false,
                'message' => 'cURL Error: ' . curl_error($ch)
            ];
        }
        
        // Get HTTP status code
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($statusCode != 200) {
            fclose($fp);
            return [
                'success' => false,
                'message' => "HTTP Error: $statusCode"
            ];
        }
        
        // Close cURL session and file
        curl_close($ch);
        fclose($fp);
        
        // Verify the file was downloaded successfully
        if (filesize($filePath) < 1000) { // Less than 1KB is probably an error
            unlink($filePath); // Delete the file
            return [
                'success' => false,
                'message' => "Downloaded file is too small, likely an error page"
            ];
        }
        
        return [
            'success' => true,
            'message' => "Image downloaded successfully",
            'path' => $filePath
        ];
    }
    
    /**
     * Download multiple images
     * 
     * @param array $imageUrls Array of image URLs to download
     * @return array Results of download operations
     */
    public function downloadImages($imageUrls) {
        $results = [];
        
        foreach ($imageUrls as $index => $imageUrl) {
            // Generate filename
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension) || strlen($extension) > 4) {
                $extension = 'jpg'; // Default extension
            }
            
            $fileName = $this->namePrefix . '_' . ($index + 1) . '.' . $extension;
            
            // Download image
            $result = $this->downloadImage($imageUrl, $fileName);
            $result['fileName'] = $fileName;
            $result['url'] = $imageUrl;
            
            $results[] = $result;
        }
        
        return $results;
    }
}
