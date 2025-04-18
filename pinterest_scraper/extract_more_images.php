<?php
/**
 * Additional script to extract more images from Pinterest
 * This script can be used to extract images from the debug HTML files
 */

// Отключаем вывод ошибок в браузер
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

// Set headers for JSON response
header('Content-Type: application/json');

// Функция для безопасного вывода JSON
function output_json($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    // Получаем данные из запроса
    $data = json_decode(file_get_contents('php://input'), true);

    // Проверяем наличие URL Pinterest
    if (empty($data['pinterest_url'])) {
        output_json([
            'success' => false,
            'message' => 'URL Pinterest не указан.'
        ]);
    }

    // Сохраняем URL для использования в download_zip.php
    file_put_contents(__DIR__ . '/../last_pinterest_url.txt', $data['pinterest_url']);

    // Проверяем наличие файлов отладки
    $debugFile = __DIR__ . '/../pinterest_debug.html';
    $russianDebugFile = __DIR__ . '/../pinterest_debug_russian.html';

    if (!file_exists($debugFile) && !file_exists($russianDebugFile)) {
        output_json([
            'success' => false,
            'message' => 'Файлы отладки не найдены. Сначала выполните поиск изображений.'
        ]);
    }

    // Подключаем функцию для извлечения дополнительных изображений
    $helperFile = __DIR__ . '/getAdditionalImages.php';
    if (!file_exists($helperFile)) {
        output_json([
            'success' => false,
            'message' => 'Файл getAdditionalImages.php не найден.'
        ]);
    }
    require_once $helperFile;

    $allImages = [];

    // Обрабатываем основной файл отладки
    if (file_exists($debugFile)) {
        $images = extractAdditionalImages($debugFile);
        if (!empty($images)) {
            $allImages = array_merge($allImages, $images);
        }
    }

    // Обрабатываем файл отладки для русского Pinterest
    if (file_exists($russianDebugFile)) {
        $images = extractAdditionalImages($russianDebugFile);
        if (!empty($images)) {
            $allImages = array_merge($allImages, $images);
        }
    }

    // Удаляем дубликаты
    $uniqueUrls = array_values(array_unique($allImages));

    // Возвращаем результат
    if (count($uniqueUrls) > 0) {
        output_json([
            'success' => true,
            'message' => 'Найдено ' . count($uniqueUrls) . ' дополнительных изображений.',
            'data' => ['image_urls' => $uniqueUrls]
        ]);
    } else {
        output_json([
            'success' => false,
            'message' => 'Дополнительные изображения не найдены. Попробуйте выполнить поиск снова.'
        ]);
    }
} catch (Exception $e) {
    // Записываем ошибку в лог
    error_log('Extract More Images Error: ' . $e->getMessage());

    // Возвращаем ошибку в JSON
    output_json([
        'success' => false,
        'message' => 'Произошла ошибка: ' . $e->getMessage()
    ]);
}
