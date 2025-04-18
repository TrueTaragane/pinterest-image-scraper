<?php
/**
 * Helper function to extract additional images from debug HTML files
 *
 * @param string $debugFile Path to debug HTML file
 * @return array Array of image URLs
 */
function extractAdditionalImages($debugFile) {
    // Check if file exists
    if (!file_exists($debugFile)) {
        return [];
    }

    // Read HTML content
    $html = file_get_contents($debugFile);
    if (empty($html)) {
        return [];
    }

    // Process HTML to extract additional images

    // Array to store all found image URLs
    $allUrls = [];

    // Method 1: Find original images in JSON data
    preg_match_all('/:\"(https:\/\/i\.pinimg\.com\/originals\/[^\"]+)\"/', $html, $matches1);
    if (!empty($matches1[1])) {
        foreach ($matches1[1] as $url) {
            $allUrls[] = $url;
        }
    }

    // Method 2: Find 736x images in JSON data
    preg_match_all('/:\"(https:\/\/i\.pinimg\.com\/736x\/[^\"]+)\"/', $html, $matches2);
    if (!empty($matches2[1])) {
        foreach ($matches2[1] as $url) {
            $allUrls[] = $url;
        }
    }

    // Method 3: Find 474x images in JSON data
    preg_match_all('/:\"(https:\/\/i\.pinimg\.com\/474x\/[^\"]+)\"/', $html, $matches3);
    if (!empty($matches3[1])) {
        foreach ($matches3[1] as $url) {
            $allUrls[] = $url;
        }
    }

    // Method 4: Find images in img tags
    preg_match_all('/<img[^>]+src=\"([^\"]+)\"[^>]*>/', $html, $matches4);
    if (!empty($matches4[1])) {
        foreach ($matches4[1] as $url) {
            $allUrls[] = $url;
        }
    }

    // Method 5: Find images in data-src attributes (lazy loading)
    preg_match_all('/<img[^>]+data-src=\"([^\"]+)\"[^>]*>/', $html, $matches5);
    if (!empty($matches5[1])) {
        foreach ($matches5[1] as $url) {
            $allUrls[] = $url;
        }
    }

    // Method 6: Find images in srcset attributes
    preg_match_all('/srcset=\"([^\"]+)\"/', $html, $matches6);
    if (!empty($matches6[1])) {
        foreach ($matches6[1] as $srcset) {
            $srcsetParts = explode(',', $srcset);
            foreach ($srcsetParts as $part) {
                $urlPart = explode(' ', trim($part))[0];
                if (!empty($urlPart)) {
                    $allUrls[] = $urlPart;
                }
            }
        }
    }

    // Method 7: Find image URLs in JSON data
    preg_match_all('/\"url\":\"(https:\/\/[^\"]+\.(jpg|jpeg|png|gif))\"/', $html, $matches7);
    if (!empty($matches7[1])) {
        foreach ($matches7[1] as $url) {
            $allUrls[] = $url;
        }
    }

    // Method 8: Find image URLs in styles
    preg_match_all('/background-image:\s*url\([\'"]?(https:\/\/[^\'"]+\.(jpg|jpeg|png|gif))[\'"]?\)/', $html, $matches8);
    if (!empty($matches8[1])) {
        foreach ($matches8[1] as $url) {
            $allUrls[] = $url;
        }
    }

    // Method 9: Find any image URLs in HTML
    preg_match_all('/https:\/\/[^\"\'\s]+\.(jpg|jpeg|png|gif)/', $html, $matches9);
    if (!empty($matches9[0])) {
        foreach ($matches9[0] as $url) {
            $allUrls[] = $url;
        }
    }

    // Filter Pinterest image URLs and remove duplicates
    $filteredUrls = array_filter($allUrls, function($url) {
        return strpos($url, 'pinimg.com') !== false &&
               strpos($url, 'x60.jpg') === false &&
               strpos($url, 'x30.jpg') === false &&
               strpos($url, 'avatars') === false;
    });

    // Group images by their ID to find the largest version of each image
    $imageGroups = [];
    foreach ($filteredUrls as $url) {
        // Extract the image ID from the URL using a more flexible pattern
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

    // Return the best quality images
    return $bestUrls;
}
