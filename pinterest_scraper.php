<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Pinterest Image Scraper</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Fancybox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css"/>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .pinterest-scraper {
            padding: 50px 0;
        }
        .image-preview {
            display: flex;
            flex-direction: column;
            gap: 30px;
            margin-top: 30px;
        }
        .image-item {
            position: relative;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            margin-bottom: 15px;
        }
        .image-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .image-item a {
            display: block;
            cursor: zoom-in;
        }
        .image-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
            transition: filter 0.3s;
        }
        .image-item:hover img {
            filter: brightness(1.1);
        }
        /* Checkbox styles */
        .image-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 10;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        .image-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .image-item:hover .image-checkbox {
            opacity: 1;
        }
        /* Remove button styles */
        .image-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            opacity: 0;
            transition: opacity 0.3s;
            background-color: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .image-item:hover .image-remove {
            opacity: 1;
        }
        .image-remove:hover {
            background-color: rgba(220, 53, 69, 1);
            transform: scale(1.1);
        }
        /* Selected image style */
        .image-item.selected {
            border: 3px solid #0d6efd;
            overflow: visible;
        }
        .image-item.selected::before {
            content: '✓';
            position: absolute;
            top: -10px;
            left: -10px;
            background-color: #0d6efd;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 11;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .image-actions {
            position: absolute;
            bottom: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 10; /* Ensure buttons are above the image */
        }
        .image-item:hover .image-actions {
            opacity: 1;
        }
        .image-actions button {
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .image-actions button:hover {
            background-color: #fff;
            transform: scale(1.1);
        }
        .image-actions button.btn-primary {
            background-color: rgba(13, 110, 253, 0.9);
            color: white;
        }
        .image-actions button.btn-primary:hover {
            background-color: rgba(13, 110, 253, 1);
        }
        .image-actions button.btn-info {
            background-color: rgba(13, 202, 240, 0.9);
            color: white;
        }
        .image-actions button.btn-info:hover {
            background-color: rgba(13, 202, 240, 1);
        }

        /* Source block styles */
        .source-block {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
        }

        .source-header {
            background-color: #6c5ce7;
            color: white;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .source-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .source-url {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            margin-top: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 500px;
        }

        .source-stats {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 14px;
        }

        .stats-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .source-actions {
            display: flex;
            gap: 10px;
        }

        .source-actions button {
            white-space: nowrap;
            font-size: 12px;
            padding: 5px 10px;
        }

        .source-content {
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-top: none;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .source-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .progress-container {
            margin: 30px 0;
            display: none;
        }
        .bulk-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .bulk-actions button {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        .bulk-actions button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-select-all {
            background-color: #6c757d;
            color: white;
        }
        .btn-select-all:hover {
            background-color: #5a6268;
        }
        .btn-download-selected {
            background-color: #0d6efd;
            color: white;
        }
        .btn-download-selected:hover {
            background-color: #0b5ed7;
        }
        .btn-download-selected:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .result-container {
            margin-top: 30px;
            display: none;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1>Pinterest Image Scraper</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pinterest-scraper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <p class="text-center mb-4">Добавьте ссылки на доски Pinterest и нажмите "Парсить все ссылки", чтобы извлечь и скачать изображения</p>

                    <!-- Project Links Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0 d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-link me-2"></i> Ссылки проектов</span>
                                <button class="btn btn-light btn-sm" type="button" id="parse-all-btn">
                                    <i class="fas fa-search me-2"></i> Парсить все ссылки
                                </button>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="links-container" class="mb-3">
                                <!-- Links will be loaded here -->
                            </div>
                            <div class="input-group mb-3">
                                <input type="url" class="form-control" id="new-link" placeholder="https://www.pinterest.com/username/boardname/">
                                <button class="btn btn-success" type="button" id="save-link-btn">
                                    <i class="fas fa-save me-2"></i> Сохранить
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Form -->
                    <form id="pinterest-form" onsubmit="return false;" style="display: none;">
                        <div class="row g-3 mb-4">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="pinterest-url">URL доски Pinterest</label>
                                <input class="form-control" id="pinterest-url" type="url" name="pinterest_url"
                                    placeholder="https://www.pinterest.com/username/boardname/" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="folder-name">Имя папки для сохранения <small>(по умолчанию - последняя часть URL)</small></label>
                                <input class="form-control" id="folder-name" type="text" name="folder_name"
                                    placeholder="Автоматически из URL" value="">
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" id="download-images" type="checkbox" name="download_images">
                                    <label class="form-check-label" for="download-images">Скачать изображения автоматически</label>
                                </div>
                            </div>
                            <div class="col-md-12 text-center">
                                <button class="btn btn-primary" type="button" id="search-btn">
                                    <i class="fas fa-search me-2"></i> Найти все изображения
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Progress -->
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                        </div>
                        <p class="text-center mt-2" id="progress-text">Загрузка изображений...</p>
                    </div>

                    <!-- Results -->
                    <div class="result-container">
                        <div class="alert" role="alert" id="result-message"></div>

                        <div class="text-center mb-4">
                            <h4 id="images-count">Найдено изображений: 0</h4>
                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <button class="btn btn-success download-all-btn" id="download-all-btn">
                                    <i class="fas fa-download me-2"></i> Скачать все изображения
                                </button>
                                <button class="btn btn-info" id="download-all-folders-btn">
                                    <i class="fas fa-folder-download me-2"></i> Скачать все папки
                                </button>
                            </div>
                            <div class="bulk-actions justify-content-center">
                                <button class="btn-select-all" id="select-all-btn">
                                    <i class="fas fa-check-square me-2"></i> Выделить все
                                </button>
                                <button class="btn-download-selected" id="download-selected-btn" disabled>
                                    <i class="fas fa-download me-2"></i> Скачать выбранные (<span id="selected-count">0</span>)
                                </button>
                            </div>
                            <div class="alert alert-info" id="log-info" style="display: none;">
                                <p>Все найденные изображения записаны в лог-файлы:</p>
                                <ul class="text-start">
                                    <li><code>all_images_log.txt</code> - все найденные изображения</li>
                                    <li><code>russian_pinterest_log.txt</code> - изображения с русского Pinterest</li>
                                    <li><code>additional_images_log.txt</code> - дополнительные изображения</li>
                                </ul>
                            </div>
                        </div>

                        <div class="image-preview" id="image-preview"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Fancybox JS -->
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load links from links.txt
            loadLinks();
            const form = document.getElementById('pinterest-form');
            const progressContainer = document.querySelector('.progress-container');
            const progressBar = document.querySelector('.progress-bar');
            const progressText = document.getElementById('progress-text');
            const resultContainer = document.querySelector('.result-container');
            const resultMessage = document.getElementById('result-message');
            const imagesCount = document.getElementById('images-count');
            const imagePreview = document.getElementById('image-preview');
            const downloadAllBtn = document.getElementById('download-all-btn');

            // Search button click
            const searchBtn = document.getElementById('search-btn');
            searchBtn.addEventListener('click', function() {
                // Get the Pinterest URL
                const pinterestUrlInput = document.getElementById('pinterest-url');
                const pinterestUrl = pinterestUrlInput.value;

                // Validate URL
                if (!pinterestUrl) {
                    alert('Пожалуйста, введите URL доски Pinterest');
                    return;
                }

                // Show progress
                progressContainer.style.display = 'block';
                resultContainer.style.display = 'none';
                progressBar.style.width = '0%';
                progressBar.setAttribute('aria-valuenow', 0);
                progressText.textContent = 'Загрузка изображений...';

                // Create data object with Pinterest URL
                const data = {
                    pinterest_url: pinterestUrl,
                    download_images: false // We don't download images directly
                };

                // Simulate progress (since we can't track actual progress of the scraping)
                let progress = 0;
                const progressInterval = setInterval(function() {
                    progress += 5;
                    if (progress > 90) {
                        clearInterval(progressInterval);
                    }
                    progressBar.style.width = progress + '%';
                    progressBar.setAttribute('aria-valuenow', progress);
                }, 300);

                // Send AJAX request
                fetch('pinterest_scraper/api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    // Clear progress interval
                    clearInterval(progressInterval);

                    // Complete progress
                    progressBar.style.width = '100%';
                    progressBar.setAttribute('aria-valuenow', 100);
                    progressText.textContent = 'Загрузка завершена';

                    // Show results
                    resultContainer.style.display = 'block';

                    // Set message
                    resultMessage.textContent = data.message;
                    resultMessage.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger');

                    // Clear previous results
                    imagePreview.innerHTML = '';

                    if (data.success) {
                        // Update images count
                        const imageUrls = data.data.image_urls;
                        imagesCount.textContent = 'Найдено изображений: ' + imageUrls.length;

                        // Show log info
                        logInfo.style.display = 'block';

                        // Extract folder name from URL
                        try {
                            const urlObj = new URL(pinterestUrl);
                            const urlPath = urlObj.pathname;
                            const pathParts = urlPath.split('/');
                            let lastPart = pathParts[pathParts.length - 2] || ''; // Get the last non-empty part

                            // Decode URL-encoded characters
                            try {
                                lastPart = decodeURIComponent(lastPart);
                            } catch (e) {
                                console.error('Error decoding URL part:', e);
                            }

                            // Update folder name input
                            if (lastPart) {
                                document.getElementById('folder-name').value = transliterate(lastPart);
                            }
                        } catch (e) {
                            console.error('Error parsing URL:', e);
                        }

                        // Now automatically try to find more images
                        fetch('pinterest_scraper/extract_more_images.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ pinterest_url: pinterestUrl })
                        })
                        .then(function(response) { return response.json(); })
                        .then(function(moreData) {
                            if (moreData.success) {
                                // Add more images to the array
                                const moreUrls = moreData.data.image_urls;
                                imageUrls.push(...moreUrls);

                                // Update count
                                imagesCount.textContent = 'Найдено изображений: ' + imageUrls.length;

                                // Update message
                                resultMessage.textContent = 'Найдено ' + imageUrls.length + ' изображений (включая дополнительные).';
                            }

                            // Display all image previews
                            imagePreview.innerHTML = ''; // Clear previous results
                            imageUrls.forEach(function(url, index) {
                                const imageItem = document.createElement('div');
                                imageItem.className = 'image-item';
                                imageItem.dataset.url = url;
                                imageItem.dataset.index = index;

                                // Create a link for Fancybox
                                // Extract folder name from URL for display
                                let folderName = 'Pinterest Board';
                                try {
                                    const urlObj = new URL(pinterestUrl);
                                    const urlPath = urlObj.pathname;
                                    const pathParts = urlPath.split('/');
                                    let lastPart = pathParts[pathParts.length - 2] || '';

                                    // Decode URL-encoded characters
                                    try {
                                        lastPart = decodeURIComponent(lastPart);
                                    } catch (e) {
                                        console.error('Error decoding URL part:', e);
                                    }

                                    if (lastPart) {
                                        folderName = lastPart;
                                    }
                                } catch (e) {
                                    console.error('Error parsing URL:', e);
                                }

                                const link = document.createElement('a');
                                link.href = url;
                                link.dataset.fancybox = 'gallery';
                                link.dataset.caption = folderName + ' - Image ' + (index + 1);

                                const img = document.createElement('img');
                                img.src = url;
                                img.alt = 'Pinterest Image ' + (index + 1);
                                img.loading = 'lazy';

                                // Add the image to the link
                                link.appendChild(img);

                                // Add checkbox for selection
                                const checkbox = document.createElement('div');
                                checkbox.className = 'image-checkbox';
                                const checkboxInput = document.createElement('input');
                                checkboxInput.type = 'checkbox';
                                checkboxInput.addEventListener('change', function() {
                                    if (this.checked) {
                                        imageItem.classList.add('selected');
                                    } else {
                                        imageItem.classList.remove('selected');
                                    }
                                    updateSelectedCount();
                                });
                                checkbox.appendChild(checkboxInput);
                                imageItem.appendChild(checkbox);

                                // Add remove button
                                const removeBtn = document.createElement('button');
                                removeBtn.className = 'image-remove';
                                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                                removeBtn.title = 'Удалить';
                                removeBtn.addEventListener('click', function(e) {
                                    e.stopPropagation();

                                    // Add image URL to ignore list
                                    const imageUrl = imageItem.dataset.url;
                                    fetch('ignore_image.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({ image_url: imageUrl })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            // Remove image from display
                                            imageItem.remove();
                                            updateImagesCount();
                                            updateSelectedCount();
                                        } else {
                                            alert('Ошибка: ' + data.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error ignoring image:', error);
                                        alert('Ошибка при добавлении изображения в список игнорируемых');
                                    });
                                });
                                imageItem.appendChild(removeBtn);

                                // Add action buttons
                                const actions = document.createElement('div');
                                actions.className = 'image-actions';

                                const downloadBtn = document.createElement('button');
                                downloadBtn.className = 'btn-primary';
                                downloadBtn.innerHTML = '<i class="fas fa-download"></i>';
                                downloadBtn.title = 'Скачать';
                                downloadBtn.addEventListener('click', function(e) {
                                    e.stopPropagation(); // Prevent triggering the Fancybox
                                    downloadImage(url, 'pinterest_image_' + (index + 1));
                                });

                                const viewBtn = document.createElement('button');
                                viewBtn.className = 'btn-info';
                                viewBtn.innerHTML = '<i class="fas fa-eye"></i>';
                                viewBtn.title = 'Просмотр';
                                viewBtn.addEventListener('click', function(e) {
                                    e.stopPropagation(); // Prevent triggering the Fancybox
                                    // Use Fancybox to show the image
                                    Fancybox.show([{ src: url, caption: 'Pinterest Image ' + (index + 1) }]);
                                });

                                actions.appendChild(viewBtn);
                                actions.appendChild(downloadBtn);
                                imageItem.appendChild(link);
                                imageItem.appendChild(actions);
                                imagePreview.appendChild(imageItem);
                            });
                        });
                    }
                })
                .catch(error => {
                    // Clear progress interval
                    clearInterval(progressInterval);

                    // Complete progress
                    progressBar.style.width = '100%';
                    progressBar.setAttribute('aria-valuenow', 100);
                    progressText.textContent = 'Ошибка загрузки';

                    // Show error message
                    resultContainer.style.display = 'block';
                    resultMessage.textContent = 'Произошла ошибка: ' + error.message;
                    resultMessage.className = 'alert alert-danger';
                });
            });

            // Download all images button
            downloadAllBtn.addEventListener('click', function() {
                const imageItems = imagePreview.querySelectorAll('.image-item');
                if (imageItems.length === 0) return;

                // Get all image URLs
                const imageUrls = [];
                imageItems.forEach(item => {
                    imageUrls.push(item.dataset.url);
                });

                // Get folder name - use empty string to let the server determine the folder name from URL
                const folderName = document.getElementById('folder-name').value || '';

                // Create form for ZIP download
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'pinterest_scraper/download_zip.php';

                // Add image URLs as hidden inputs
                imageUrls.forEach((url, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'image_urls[]';
                    input.value = url;
                    form.appendChild(input);
                });

                // Add folder name
                const folderInput = document.createElement('input');
                folderInput.type = 'hidden';
                folderInput.name = 'folder_name';
                folderInput.value = folderName;
                form.appendChild(folderInput);

                // Add Pinterest URL
                const urlInput = document.createElement('input');
                urlInput.type = 'hidden';
                urlInput.name = 'pinterest_url';
                urlInput.value = document.getElementById('pinterest-url').value;
                form.appendChild(urlInput);

                // Submit form
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            });

            // Log info element
            const logInfo = document.getElementById('log-info');

            // Select all button
            const selectAllBtn = document.getElementById('select-all-btn');
            const downloadSelectedBtn = document.getElementById('download-selected-btn');
            const selectedCount = document.getElementById('selected-count');

            selectAllBtn.addEventListener('click', function() {
                const checkboxes = imagePreview.querySelectorAll('input[type="checkbox"]');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);

                checkboxes.forEach(checkbox => {
                    checkbox.checked = !allChecked;
                    const imageItem = checkbox.closest('.image-item');
                    if (!allChecked) {
                        imageItem.classList.add('selected');
                    } else {
                        imageItem.classList.remove('selected');
                    }
                });

                updateSelectedCount();

                // Update button text
                if (!allChecked) {
                    selectAllBtn.innerHTML = '<i class="fas fa-square me-2"></i> Снять выделение';
                } else {
                    selectAllBtn.innerHTML = '<i class="fas fa-check-square me-2"></i> Выделить все';
                }
            });

            // Download selected button
            downloadSelectedBtn.addEventListener('click', function() {
                const selectedItems = imagePreview.querySelectorAll('.image-item.selected');
                if (selectedItems.length === 0) return;

                // Get selected image URLs
                const imageUrls = [];
                selectedItems.forEach(item => {
                    imageUrls.push(item.dataset.url);
                });

                // Get folder name - use empty string to let the server determine the folder name from URL
                const folderName = document.getElementById('folder-name').value || '';

                // Create form for ZIP download
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'pinterest_scraper/download_zip.php';

                // Add image URLs as hidden inputs
                imageUrls.forEach((url, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'image_urls[]';
                    input.value = url;
                    form.appendChild(input);
                });

                // Add folder name
                const folderInput = document.createElement('input');
                folderInput.type = 'hidden';
                folderInput.name = 'folder_name';
                folderInput.value = folderName;
                form.appendChild(folderInput);

                // Add Pinterest URL
                const urlInput = document.createElement('input');
                urlInput.type = 'hidden';
                urlInput.name = 'pinterest_url';
                urlInput.value = document.getElementById('pinterest-url').value;
                form.appendChild(urlInput);

                // Submit form
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            });

            // Function to update selected count
            function updateSelectedCount() {
                const selectedItems = imagePreview.querySelectorAll('.image-item.selected');
                selectedCount.textContent = selectedItems.length;

                // Enable/disable download selected button
                if (selectedItems.length > 0) {
                    downloadSelectedBtn.disabled = false;
                } else {
                    downloadSelectedBtn.disabled = true;
                }
            }

            // Function to update images count
            function updateImagesCount() {
                const totalImages = imagePreview.querySelectorAll('.image-item').length;
                imagesCount.textContent = 'Найдено изображений: ' + totalImages;
            }

            // Function to transliterate Cyrillic to Latin
            function transliterate(text) {
                const cyrillicToLatin = {
                    'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'zh',
                    'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o',
                    'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts',
                    'ч': 'ch', 'ш': 'sh', 'щ': 'sch', 'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu',
                    'я': 'ya',
                    'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo', 'Ж': 'Zh',
                    'З': 'Z', 'И': 'I', 'Й': 'Y', 'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N', 'О': 'O',
                    'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'Ts',
                    'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sch', 'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu',
                    'Я': 'Ya'
                };

                return text.split('').map(char => {
                    return cyrillicToLatin[char] || char;
                }).join('');
            }

            // Function to download an image
            function downloadImage(url, filename) {
                // Create a proxy URL to avoid CORS issues
                const proxyUrl = 'download_image.php?url=' + encodeURIComponent(url) + '&filename=' + encodeURIComponent(filename);

                // Create a link and trigger download
                const a = document.createElement('a');
                a.href = proxyUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }

            // Function to download a folder
            function downloadFolder(source, folderName, urls) {
                // Create form for ZIP download
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'pinterest_scraper/download_zip.php';

                // Add image URLs as hidden inputs
                urls.forEach((url, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'image_urls[]';
                    input.value = url;
                    form.appendChild(input);
                });

                // Add folder name
                const folderInput = document.createElement('input');
                folderInput.type = 'hidden';
                folderInput.name = 'folder_name';
                folderInput.value = folderName;
                form.appendChild(folderInput);

                // Add Pinterest URL
                const urlInput = document.createElement('input');
                urlInput.type = 'hidden';
                urlInput.name = 'pinterest_url';
                urlInput.value = source;
                form.appendChild(urlInput);

                // Submit form
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }

            // Save link button click
            const saveLinkBtn = document.getElementById('save-link-btn');
            saveLinkBtn.addEventListener('click', function() {
                const newLinkInput = document.getElementById('new-link');
                const newLink = newLinkInput.value.trim();

                if (!newLink) {
                    alert('Пожалуйста, введите URL доски Pinterest');
                    return;
                }

                // Validate URL
                if (!newLink.includes('pinterest.com')) {
                    alert('Пожалуйста, введите корректный URL доски Pinterest');
                    return;
                }

                // Save link
                saveLink(newLink);
            });

            // Download all folders button click
            const downloadAllFoldersBtn = document.getElementById('download-all-folders-btn');
            downloadAllFoldersBtn.addEventListener('click', function() {
                downloadAllFolders();
            });

            // Parse all links button click
            const parseAllBtn = document.getElementById('parse-all-btn');
            parseAllBtn.addEventListener('click', function() {
                parseAllLinks();
            });

            // Function to parse all links
            function parseAllLinks() {
                // First load ignored URLs
                fetch('load_ignored_urls.php')
                    .then(response => response.json())
                    .then(ignoredData => {
                        const ignoredUrls = ignoredData.urls || [];

                        // Then load links
                        return fetch('load_links.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.links && data.links.length > 0) {
                                    // Clear previous results
                                    imagePreview.innerHTML = '';
                                    resultContainer.style.display = 'none';

                                    // Show progress
                                    progressContainer.style.display = 'block';
                                    progressBar.style.width = '0%';
                                    progressBar.setAttribute('aria-valuenow', 0);
                                    progressText.textContent = 'Загрузка изображений...';

                                    // Parse each link sequentially
                                    parseNextLink(data.links, 0, [], ignoredUrls);
                                } else {
                                    alert('Нет сохраненных ссылок. Добавьте ссылки для парсинга.');
                                }
                            });
                    })
                    .catch(error => {
                        console.error('Error loading data:', error);
                        alert('Ошибка загрузки данных');
                    });
            }

            // Function to parse links sequentially
            function parseNextLink(links, index, allImageUrls, ignoredUrls) {
                if (index >= links.length) {
                    // All links parsed, display results
                    displayResults(allImageUrls, ignoredUrls);
                    return;
                }

                const link = links[index];

                // Update progress
                const progress = Math.round((index / links.length) * 100);
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
                progressText.textContent = 'Парсинг ссылки ' + (index + 1) + ' из ' + links.length;

                // Create data object with Pinterest URL
                const data = {
                    pinterest_url: link,
                    download_images: false
                };

                // Send AJAX request
                fetch('pinterest_scraper/api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Get image URLs
                        const imageUrls = data.data.image_urls;

                        // Add source link to each image URL
                        const imageUrlsWithSource = imageUrls.map(url => {
                            return {
                                url: url,
                                source: link
                            };
                        });

                        // Add to all image URLs
                        allImageUrls = allImageUrls.concat(imageUrlsWithSource);
                    }

                    // Parse next link
                    parseNextLink(links, index + 1, allImageUrls, ignoredUrls);
                })
                .catch(error => {
                    console.error('Error parsing link:', error);
                    // Continue with next link even if there's an error
                    parseNextLink(links, index + 1, allImageUrls, ignoredUrls);
                });
            }

            // Function to display results
            function displayResults(imageUrlsWithSource, ignoredUrls) {
                // Filter out ignored URLs
                if (ignoredUrls && ignoredUrls.length > 0) {
                    imageUrlsWithSource = imageUrlsWithSource.filter(item => {
                        return !ignoredUrls.includes(item.url);
                    });
                }
                // Complete progress
                progressBar.style.width = '100%';
                progressBar.setAttribute('aria-valuenow', 100);
                progressText.textContent = 'Загрузка завершена';

                // Show results
                resultContainer.style.display = 'block';

                // Set message
                resultMessage.textContent = 'Найдено ' + imageUrlsWithSource.length + ' изображений.';
                resultMessage.className = 'alert alert-success';

                // Update images count
                imagesCount.textContent = 'Найдено изображений: ' + imageUrlsWithSource.length;

                // Show log info
                document.getElementById('log-info').style.display = 'block';

                // Group images by source
                const groupedImages = {};

                imageUrlsWithSource.forEach(item => {
                    const source = item.source;
                    if (!groupedImages[source]) {
                        groupedImages[source] = [];
                    }
                    groupedImages[source].push(item.url);
                });

                // Display grouped image previews
                imagePreview.innerHTML = ''; // Clear previous results

                // Process each source group
                Object.keys(groupedImages).forEach(source => {
                    const urls = groupedImages[source];

                    // Extract folder name from source URL for display
                    let folderName = 'Pinterest Board';
                    try {
                        const urlObj = new URL(source);
                        const urlPath = urlObj.pathname;
                        const pathParts = urlPath.split('/');
                        let lastPart = pathParts[pathParts.length - 2] || '';

                        // Decode URL-encoded characters
                        try {
                            lastPart = decodeURIComponent(lastPart);
                        } catch (e) {
                            console.error('Error decoding URL part:', e);
                        }

                        if (lastPart) {
                            folderName = lastPart;
                        }
                    } catch (e) {
                        console.error('Error parsing URL:', e);
                    }

                    // Create source block
                    const sourceBlock = document.createElement('div');
                    sourceBlock.className = 'source-block';

                    // Create source header
                    const sourceHeader = document.createElement('div');
                    sourceHeader.className = 'source-header';

                    // Create source title and URL
                    const sourceTitleContainer = document.createElement('div');

                    const sourceTitle = document.createElement('h3');
                    sourceTitle.className = 'source-title';
                    sourceTitle.textContent = folderName;
                    sourceTitleContainer.appendChild(sourceTitle);

                    const sourceUrl = document.createElement('a');
                    sourceUrl.className = 'source-url';
                    sourceUrl.href = source;
                    sourceUrl.target = '_blank';
                    sourceUrl.textContent = source;
                    sourceTitleContainer.appendChild(sourceUrl);

                    sourceHeader.appendChild(sourceTitleContainer);

                    // Create source stats and actions
                    const sourceStats = document.createElement('div');
                    sourceStats.className = 'source-stats';

                    // Stats info
                    const statsInfo = document.createElement('div');
                    statsInfo.className = 'stats-info';
                    statsInfo.innerHTML = `<span>${urls.length} изображений</span> <span class="selected-count">0/${urls.length} выбрано</span>`;
                    sourceStats.appendChild(statsInfo);

                    // Source actions
                    const sourceActions = document.createElement('div');
                    sourceActions.className = 'source-actions';

                    // Download folder button
                    const downloadFolderBtn = document.createElement('button');
                    downloadFolderBtn.className = 'btn btn-sm btn-success';
                    downloadFolderBtn.innerHTML = '<i class="fas fa-download me-1"></i> Скачать всю папку';
                    downloadFolderBtn.addEventListener('click', function() {
                        downloadFolder(source, folderName, urls);
                    });
                    sourceActions.appendChild(downloadFolderBtn);

                    // Download selected button
                    const downloadSelectedBtn = document.createElement('button');
                    downloadSelectedBtn.className = 'btn btn-sm btn-primary download-selected-btn';
                    downloadSelectedBtn.innerHTML = '<i class="fas fa-download me-1"></i> Скачать отмеченные';
                    downloadSelectedBtn.disabled = true;
                    downloadSelectedBtn.addEventListener('click', function() {
                        const selectedItems = sourceBlock.querySelectorAll('.image-item.selected');
                        if (selectedItems.length === 0) return;

                        const selectedUrls = [];
                        selectedItems.forEach(item => {
                            selectedUrls.push(item.dataset.url);
                        });

                        downloadFolder(source, folderName, selectedUrls);
                    });
                    sourceActions.appendChild(downloadSelectedBtn);

                    sourceStats.appendChild(sourceActions);

                    sourceHeader.appendChild(sourceStats);

                    sourceBlock.appendChild(sourceHeader);

                    // Create source content
                    const sourceContent = document.createElement('div');
                    sourceContent.className = 'source-content';

                    // Create source images container
                    const sourceImages = document.createElement('div');
                    sourceImages.className = 'source-images';

                    // Add images to source images container
                    urls.forEach((url, index) => {
                        const imageItem = document.createElement('div');
                        imageItem.className = 'image-item';
                        imageItem.dataset.url = url;
                        imageItem.dataset.index = index;
                        imageItem.dataset.source = source;

                        const link = document.createElement('a');
                        link.href = url;
                        link.dataset.fancybox = 'gallery-' + folderName.replace(/[^a-z0-9]/gi, '-').toLowerCase();
                        link.dataset.caption = folderName + ' - Image ' + (index + 1);

                        const img = document.createElement('img');
                        img.src = url;
                        img.alt = folderName + ' - Image ' + (index + 1);
                        img.loading = 'lazy';

                        // Add the image to the link
                        link.appendChild(img);

                        // Add checkbox for selection
                        const checkbox = document.createElement('div');
                        checkbox.className = 'image-checkbox';
                        const checkboxInput = document.createElement('input');
                        checkboxInput.type = 'checkbox';
                        checkboxInput.addEventListener('change', function() {
                            if (this.checked) {
                                imageItem.classList.add('selected');
                            } else {
                                imageItem.classList.remove('selected');
                            }
                            updateSelectedCount();
                            updateSourceSelectedCount(sourceBlock);
                        });
                        checkbox.appendChild(checkboxInput);
                        imageItem.appendChild(checkbox);

                        // Add remove button
                        const removeBtn = document.createElement('button');
                        removeBtn.className = 'image-remove';
                        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                        removeBtn.title = 'Удалить';
                        removeBtn.addEventListener('click', function(e) {
                            e.stopPropagation();

                            // Add image URL to ignore list
                            const imageUrl = imageItem.dataset.url;
                            fetch('ignore_image.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ image_url: imageUrl })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Remove image from display
                                    imageItem.remove();
                                    updateImagesCount();
                                    updateSelectedCount();
                                    updateSourceSelectedCount(sourceBlock);

                                    // Enable/disable download selected button
                                    const downloadSelectedBtn = sourceBlock.querySelector('.download-selected-btn');
                                    const selectedItems = sourceBlock.querySelectorAll('.image-item.selected');
                                    if (selectedItems.length > 0) {
                                        downloadSelectedBtn.disabled = false;
                                    } else {
                                        downloadSelectedBtn.disabled = true;
                                    }

                                    // Update source stats
                                    const remainingImages = sourceImages.querySelectorAll('.image-item').length;
                                    if (remainingImages === 0) {
                                        // If no images left, remove the entire source block
                                        sourceBlock.remove();
                                    } else {
                                        // Update the image count in the source stats
                                        const statsElement = sourceStats.querySelector('span:first-child');
                                        statsElement.textContent = `${remainingImages} изображений`;
                                    }
                                } else {
                                    alert('Ошибка: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error ignoring image:', error);
                                alert('Ошибка при добавлении изображения в список игнорируемых');
                            });
                        });
                        imageItem.appendChild(removeBtn);

                        // Add action buttons
                        const actions = document.createElement('div');
                        actions.className = 'image-actions';

                        const downloadBtn = document.createElement('button');
                        downloadBtn.className = 'btn-primary';
                        downloadBtn.innerHTML = '<i class="fas fa-download"></i>';
                        downloadBtn.title = 'Скачать';
                        downloadBtn.addEventListener('click', function(e) {
                            e.stopPropagation(); // Prevent triggering the Fancybox

                            // Extract extension from URL
                            let extension = 'jpg'; // Default extension
                            const urlParts = url.split('.');
                            if (urlParts.length > 1) {
                                const possibleExt = urlParts[urlParts.length - 1].toLowerCase();
                                // Check if it's a valid image extension
                                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(possibleExt)) {
                                    extension = possibleExt;
                                }
                            }

                            downloadImage(url, folderName + '_image_' + (index + 1) + '.' + extension);
                        });

                        const viewBtn = document.createElement('button');
                        viewBtn.className = 'btn-info';
                        viewBtn.innerHTML = '<i class="fas fa-eye"></i>';
                        viewBtn.title = 'Просмотр';
                        viewBtn.addEventListener('click', function(e) {
                            e.stopPropagation(); // Prevent triggering the Fancybox
                            // Use Fancybox to show the image
                            Fancybox.show([{ src: url, caption: folderName + ' - Image ' + (index + 1) }]);
                        });

                        actions.appendChild(viewBtn);
                        actions.appendChild(downloadBtn);
                        imageItem.appendChild(link);
                        imageItem.appendChild(actions);
                        sourceImages.appendChild(imageItem);
                    });

                    sourceContent.appendChild(sourceImages);
                    sourceBlock.appendChild(sourceContent);
                    imagePreview.appendChild(sourceBlock);
                });
            }

            // Function to update selected count for a source block
            function updateSourceSelectedCount(sourceBlock) {
                const totalImages = sourceBlock.querySelectorAll('.image-item').length;
                const selectedImages = sourceBlock.querySelectorAll('.image-item.selected').length;
                const selectedCountElement = sourceBlock.querySelector('.selected-count');
                if (selectedCountElement) {
                    selectedCountElement.textContent = `${selectedImages}/${totalImages} выбрано`;
                }
            }

            // Initialize Fancybox
            Fancybox.bind('[data-fancybox="gallery"]', {
                // Custom options
                Toolbar: {
                    display: [
                        { id: 'prev', position: 'center' },
                        { id: 'counter', position: 'center' },
                        { id: 'next', position: 'center' },
                        'zoom',
                        'slideshow',
                        'fullscreen',
                        'download',
                        'close',
                    ],
                },
                Image: {
                    zoom: true,
                },
                Thumbs: {
                    autoStart: false,
                },
            });

            // Function to load links from links.txt
            function loadLinks() {
                fetch('load_links.php')
                    .then(response => response.json())
                    .then(data => {
                        const linksContainer = document.getElementById('links-container');
                        linksContainer.innerHTML = '';

                        if (data.links && data.links.length > 0) {
                            const linksList = document.createElement('div');
                            linksList.className = 'list-group';

                            data.links.forEach(link => {
                                const linkItem = document.createElement('div');
                                linkItem.className = 'list-group-item d-flex justify-content-between align-items-center';

                                // Extract folder name from URL
                                let folderName = 'Pinterest Board';
                                try {
                                    const urlObj = new URL(link);
                                    const urlPath = urlObj.pathname;
                                    const pathParts = urlPath.split('/');
                                    let lastPart = pathParts[pathParts.length - 2] || '';

                                    // Decode URL-encoded characters
                                    try {
                                        lastPart = decodeURIComponent(lastPart);
                                    } catch (e) {
                                        console.error('Error decoding URL part:', e);
                                    }

                                    if (lastPart) {
                                        folderName = lastPart;
                                    }
                                } catch (e) {
                                    console.error('Error parsing URL:', e);
                                }

                                const linkText = document.createElement('div');
                                linkText.innerHTML = `<strong>${folderName}</strong><br><small>${link}</small>`;
                                linkItem.appendChild(linkText);

                                const buttonsContainer = document.createElement('div');
                                buttonsContainer.className = 'd-flex gap-2';

                                const parseBtn = document.createElement('button');
                                parseBtn.className = 'btn btn-sm btn-primary';
                                parseBtn.innerHTML = '<i class="fas fa-search"></i>';
                                parseBtn.title = 'Парсить';
                                parseBtn.addEventListener('click', function() {
                                    document.getElementById('pinterest-url').value = link;
                                    document.getElementById('search-btn').click();
                                });

                                const deleteBtn = document.createElement('button');
                                deleteBtn.className = 'btn btn-sm btn-danger';
                                deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                                deleteBtn.title = 'Удалить';
                                deleteBtn.addEventListener('click', function() {
                                    if (confirm('Вы уверены, что хотите удалить эту ссылку?')) {
                                        deleteLink(link);
                                    }
                                });

                                buttonsContainer.appendChild(parseBtn);
                                buttonsContainer.appendChild(deleteBtn);
                                linkItem.appendChild(buttonsContainer);

                                linksList.appendChild(linkItem);
                            });

                            linksContainer.appendChild(linksList);
                        } else {
                            linksContainer.innerHTML = '<div class="alert alert-info">Нет сохраненных ссылок</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading links:', error);
                        document.getElementById('links-container').innerHTML =
                            '<div class="alert alert-danger">Ошибка загрузки ссылок</div>';
                    });
            }

            // Function to save a link to links.txt
            function saveLink(link) {
                fetch('save_link.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ link: link })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear input field
                        document.getElementById('new-link').value = '';
                        // Reload links
                        loadLinks();
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error saving link:', error);
                    alert('Ошибка сохранения ссылки');
                });
            }

            // Function to delete a link from links.txt
            function deleteLink(link) {
                fetch('delete_link.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ link: link })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload links
                        loadLinks();
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error deleting link:', error);
                    alert('Ошибка удаления ссылки');
                });
            }

            // Function to download all folders
            function downloadAllFolders() {
                fetch('download_all_folders.php')
                .then(response => {
                    if (response.ok) {
                        return response.blob();
                    }
                    throw new Error('Network response was not ok.');
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'all_pinterest_folders.zip';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    console.error('Error downloading all folders:', error);
                    alert('Ошибка скачивания всех папок');
                });
            }
        });
    </script>
</body>
</html>
