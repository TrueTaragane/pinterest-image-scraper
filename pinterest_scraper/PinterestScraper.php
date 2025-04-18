<?php
/**
 * Pinterest Scraper Class
 *
 * A PHP class to scrape images from Pinterest boards
 */
class PinterestScraper {
    private $url;
    private $numImages;
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

    /**
     * Constructor
     *
     * @param string $url Pinterest URL to scrape
     * @param int $numImages Number of images to scrape
     */
    public function __construct($url, $numImages = 10) {
        $this->url = $url;
        $this->numImages = $numImages;
    }

    /**
     * Get image URLs from Pinterest
     *
     * @return array Array of image URLs
     */
    public function getImageUrls() {
        $imageUrls = [];

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

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options with more browser-like behavior
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: max-age=0'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Execute cURL session
        $html = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            return ['error' => 'cURL Error: ' . curl_error($ch)];
        }

        // Get HTTP status code
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($statusCode != 200) {
            return ['error' => 'HTTP Error: ' . $statusCode];
        }

        // Close cURL session
        curl_close($ch);

        // Process HTML to extract images

        // Try multiple patterns to extract image URLs

        // Method 1: Look for originals in JSON data
        preg_match_all('/"orig":{"url":"(https:\/\/i\.pinimg\.com\/originals\/[^"]+)"/', $html, $matches1);
        // Process results from Method 1

        // Method 2: Look for 736x images in JSON data (high quality)
        preg_match_all('/"736x":{"url":"(https:\/\/i\.pinimg\.com\/736x\/[^"]+)"/', $html, $matches2);
        // Process results from Method 2

        // Method 3: Look for image URLs in standard img tags
        preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/', $html, $matches3);
        // Process results from Method 3

        // Method 4: Look for image URLs in data-src attributes (lazy loading)
        preg_match_all('/<img[^>]+data-src="([^"]+)"[^>]*>/', $html, $matches4);
        // Process results from Method 4

        // Method 5: Look for image URLs in srcset attributes
        preg_match_all('/srcset="([^"]+)"/', $html, $matches5);
        // Process results from Method 5

        // Method 6: Look for any image URL in the HTML
        preg_match_all('/https:\/\/[^\"\s]+\.(jpg|jpeg|png|gif)/', $html, $matches6);
        // Process results from Method 6

        // Combine all found URLs
        $allUrls = [];

        if (!empty($matches1[1])) {
            $allUrls = array_merge($allUrls, $matches1[1]);
        }

        if (!empty($matches2[1])) {
            $allUrls = array_merge($allUrls, $matches2[1]);
        }

        if (!empty($matches3[1])) {
            $allUrls = array_merge($allUrls, $matches3[1]);
        }

        if (!empty($matches4[1])) {
            $allUrls = array_merge($allUrls, $matches4[1]);
        }

        if (!empty($matches5[1])) {
            // Process srcset values which may contain multiple URLs
            foreach ($matches5[1] as $srcset) {
                $srcsetParts = explode(',', $srcset);
                foreach ($srcsetParts as $part) {
                    $urlPart = explode(' ', trim($part))[0];
                    if (!empty($urlPart)) {
                        $allUrls[] = $urlPart;
                    }
                }
            }
        }

        if (!empty($matches6[0])) {
            $allUrls = array_merge($allUrls, $matches6[0]);
        }

        // Filter for Pinterest image URLs and remove duplicates
        $filteredUrls = array_filter($allUrls, function($url) {
            return strpos($url, 'pinimg.com') !== false &&
                   strpos($url, 'x60.jpg') === false &&
                   strpos($url, 'x30.jpg') === false;
        });

        // Filter Pinterest image URLs

        // Clean URLs (remove escaped characters)
        $cleanUrls = array_map(function($url) {
            return str_replace('\\', '', $url);
        }, $filteredUrls);

        // Group images by their ID to find the largest version of each image
        $imageGroups = [];
        foreach ($cleanUrls as $url) {
            // Extract the image ID from the URL using a more flexible pattern
            // This pattern extracts just the filename part without extension
            if (preg_match('|/([0-9a-f]{2}/[0-9a-f]{2}/[0-9a-f]{2}/[0-9a-f]{32})|i', $url, $matches)) {
                $imageId = $matches[1];
                if (!isset($imageGroups[$imageId])) {
                    $imageGroups[$imageId] = [];
                }
                $imageGroups[$imageId][] = $url;
            }
            elseif (preg_match('|/([0-9a-f]{2}/[0-9a-f]{2}/[0-9a-f]{2}/[0-9a-f]{16})|i', $url, $matches)) {
                $imageId = $matches[1];
                if (!isset($imageGroups[$imageId])) {
                    $imageGroups[$imageId] = [];
                }
                $imageGroups[$imageId][] = $url;
            }
            elseif (preg_match('|/([0-9a-f]{8})|i', $url, $matches)) {
                $imageId = $matches[1];
                if (!isset($imageGroups[$imageId])) {
                    $imageGroups[$imageId] = [];
                }
                $imageGroups[$imageId][] = $url;
            }
            // Extract just the filename without path and extension
            elseif (preg_match('|/([^/]+)\.[^.]+$|', $url, $matches)) {
                $filename = $matches[1];
                // Only group if filename has a certain minimum length to avoid false grouping
                if (strlen($filename) > 8) {
                    if (!isset($imageGroups[$filename])) {
                        $imageGroups[$filename] = [];
                    }
                    $imageGroups[$filename][] = $url;
                } else {
                    // For short filenames, treat as unique
                    $imageGroups[md5($url)] = [$url];
                }
            } else {
                // If we can't extract an ID, treat the URL as unique
                $imageGroups[md5($url)] = [$url];
            }
        }

        // Select the largest version of each image
        $bestUrls = [];
        foreach ($imageGroups as $imageId => $urls) {
            // Sort URLs by size preference (originals > 736x > 474x > 236x)
            usort($urls, function($a, $b) {
                $sizeOrder = ['originals' => 4, '736x' => 3, '474x' => 2, '236x' => 1, '75x75' => 0];
                $sizeA = 0;
                $sizeB = 0;

                foreach ($sizeOrder as $size => $order) {
                    if (strpos($a, $size) !== false) $sizeA = $order;
                    if (strpos($b, $size) !== false) $sizeB = $order;
                }

                return $sizeB - $sizeA; // Descending order
            });

            // Add the largest version to the result
            $bestUrls[] = $urls[0];
        }

        // Select best quality images

        // Remove any remaining duplicates
        $imageUrls = array_values(array_unique($bestUrls));

        // Filter out ignored URLs
        if (!empty($ignoredUrls)) {
            $imageUrls = array_filter($imageUrls, function($url) use ($ignoredUrls) {
                return !in_array($url, $ignoredUrls);
            });
            $imageUrls = array_values($imageUrls); // Re-index array
        }

        // Prepare final image URLs

        // If still no images found, try a more generic approach
        if (empty($imageUrls)) {
            // Look for any image URL
            preg_match_all('/https:\/\/[^\"\s]+\.(jpg|jpeg|png|gif)/', $html, $matches);

            if (!empty($matches[0])) {
                $imageUrls = array_values(array_unique($matches[0]));
                $imageUrls = array_slice($imageUrls, 0, $this->numImages);

                // Use generic images as fallback
            }
        }

        // Always try the Russian Pinterest approach for more images
        $russianImages = $this->getRussianPinterestImages();
        if (!empty($russianImages)) {
            // Merge with existing images
            $imageUrls = array_merge($imageUrls, $russianImages);
            // Remove duplicates
            $imageUrls = array_values(array_unique($imageUrls));

            // Add Russian Pinterest images
        }

        // Try to extract additional images using the extract_more_images approach
        $additionalImages = $this->getAdditionalImages();
        if (!empty($additionalImages)) {
            // Merge with existing images
            $imageUrls = array_merge($imageUrls, $additionalImages);
            // Remove duplicates
            $imageUrls = array_values(array_unique($imageUrls));

            // Add additional images
        }

        // Return final image URLs

        return $imageUrls;
    }

    /**
     * Extract additional images from debug HTML files
     * @return array Array of additional image URLs
     */
    private function getAdditionalImages() {
        // Check if debug files exist
        $debugFile = __DIR__ . '/../pinterest_debug.html';
        $russianDebugFile = __DIR__ . '/../pinterest_debug_russian.html';

        // Include helper function if it exists
        $helperFile = __DIR__ . '/getAdditionalImages.php';
        if (!file_exists($helperFile)) {
            return [];
        }
        require_once $helperFile;

        $allImages = [];

        // Process main debug file
        if (file_exists($debugFile)) {
            $images = extractAdditionalImages($debugFile);
            if (!empty($images)) {
                $allImages = array_merge($allImages, $images);
            }
        }

        // Process Russian debug file
        if (file_exists($russianDebugFile)) {
            $images = extractAdditionalImages($russianDebugFile);
            if (!empty($images)) {
                $allImages = array_merge($allImages, $images);
            }
        }

        // Remove duplicates
        return array_values(array_unique($allImages));
    }

    /**
     * Special method to handle Russian Pinterest URLs
     *
     * @return array Array of image URLs
     */
    private function getRussianPinterestImages() {
        $imageUrls = [];

        // Process Russian Pinterest URL

        // Convert Russian URL to international format
        $url = $this->url;
        if (strpos($url, 'ru.pinterest.com') !== false) {
            $url = str_replace('ru.pinterest.com', 'www.pinterest.com', $url);
        } else if (strpos($url, 'pinterest.ru') !== false) {
            $url = str_replace('pinterest.ru', 'www.pinterest.com', $url);
        }

        // Use converted URL

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options with more browser-like behavior
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3', // Use Russian language
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: max-age=0'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Execute cURL session
        $html = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            return [];
        }

        // Close cURL session
        curl_close($ch);

        // Process Russian Pinterest HTML

        // Try to find image URLs in the HTML
        // Look for high-quality images
        preg_match_all('/"(https:\/\/i\.pinimg\.com\/[^\"]+\.jpg)"/', $html, $matches);

        if (!empty($matches[1])) {

            // Filter out small images
            $filteredUrls = array_filter($matches[1], function($url) {
                return strpos($url, 'x60.jpg') === false &&
                       strpos($url, 'x30.jpg') === false &&
                       strpos($url, 'avatars') === false;
            });

            // Filter small images

            // Group images by their ID to find the largest version of each image
            $imageGroups = [];
            foreach ($filteredUrls as $url) {
                // Extract the image ID from the URL using a more flexible pattern
                if (preg_match('|/([0-9a-f]{2}/[0-9a-f]{2}/[0-9a-f]{2}/[0-9a-f]{32})|i', $url, $matches2)) {
                    $imageId = $matches2[1];
                    if (!isset($imageGroups[$imageId])) {
                        $imageGroups[$imageId] = [];
                    }
                    $imageGroups[$imageId][] = $url;
                }
                elseif (preg_match('|/([0-9a-f]{2}/[0-9a-f]{2}/[0-9a-f]{2}/[0-9a-f]{16})|i', $url, $matches2)) {
                    $imageId = $matches2[1];
                    if (!isset($imageGroups[$imageId])) {
                        $imageGroups[$imageId] = [];
                    }
                    $imageGroups[$imageId][] = $url;
                }
                elseif (preg_match('|/([0-9a-f]{8})|i', $url, $matches2)) {
                    $imageId = $matches2[1];
                    if (!isset($imageGroups[$imageId])) {
                        $imageGroups[$imageId] = [];
                    }
                    $imageGroups[$imageId][] = $url;
                }
                // Extract just the filename without path and extension
                elseif (preg_match('|/([^/]+)\.[^.]+$|', $url, $matches2)) {
                    $filename = $matches2[1];
                    // Only group if filename has a certain minimum length to avoid false grouping
                    if (strlen($filename) > 8) {
                        if (!isset($imageGroups[$filename])) {
                            $imageGroups[$filename] = [];
                        }
                        $imageGroups[$filename][] = $url;
                    } else {
                        // For short filenames, treat as unique
                        $imageGroups[md5($url)] = [$url];
                    }
                } else {
                    // If we can't extract an ID, treat the URL as unique
                    $imageGroups[md5($url)] = [$url];
                }
            }

            // Select the largest version of each image
            $bestUrls = [];
            foreach ($imageGroups as $imageId => $urls) {
                // Sort URLs by size preference (originals > 736x > 474x > 236x)
                usort($urls, function($a, $b) {
                    $sizeOrder = ['originals' => 4, '736x' => 3, '474x' => 2, '236x' => 1, '75x75' => 0];
                    $sizeA = 0;
                    $sizeB = 0;

                    foreach ($sizeOrder as $size => $order) {
                        if (strpos($a, $size) !== false) $sizeA = $order;
                        if (strpos($b, $size) !== false) $sizeB = $order;
                    }

                    return $sizeB - $sizeA; // Descending order
                });

                // Add the largest version to the result
                $bestUrls[] = $urls[0];
            }

            $imageUrls = array_values(array_unique($bestUrls));
        }

        return $imageUrls;
    }
}
