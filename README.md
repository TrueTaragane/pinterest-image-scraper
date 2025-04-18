# Pinterest Image Scraper

![Pinterest Image Scraper](https://i.imgur.com/JQFXQpL.png)

Мощный инструмент для скачивания изображений с досок Pinterest с удобным веб-интерфейсом. Приложение позволяет парсить изображения с досок Pinterest, группировать их по источникам, выбирать нужные изображения и скачивать их в различных форматах.

## 🌟 Возможности

- ✅ Парсинг изображений с досок Pinterest
- ✅ Группировка изображений по доскам с заголовками
- ✅ Выбор отдельных изображений с помощью чекбоксов
- ✅ Скачивание всех изображений из определенной доски
- ✅ Скачивание только выбранных изображений
- ✅ Удаление ненужных изображений из результатов
- ✅ Сохранение ссылок на доски Pinterest для повторного использования
- ✅ Скачивание всех досок одним архивом
- ✅ Поддержка кириллических имен папок с транслитерацией
- ✅ Просмотр изображений в галерее с увеличением (Fancybox)

## 📋 Требования

- PHP 7.0 или выше
- Расширение cURL для PHP
- Расширение ZipArchive для PHP
- Веб-сервер (Apache, Nginx и т.д.)
- Права на запись в директорию с файлами

## 🚀 Установка

1. Скачайте репозиторий как ZIP-архив или клонируйте его:  git clone https://github.com/TrueTaragane/pinterest-image-scraper.git
2. Загрузите файлы на ваш веб-сервер с поддержкой PHP
3. Убедитесь, что у PHP есть права на запись в директорию с файлами:

chmod -R 755 /path/to/pinterest-image-scraper
chmod -R 777 /path/to/pinterest-image-scraper/links.txt
chmod -R 777 /path/to/pinterest-image-scraper/ignore.txt


4. Откройте `pinterest_scraper.php` в браузере

## 📝 Использование

### Парсинг изображений с доски Pinterest

1. Введите URL доски Pinterest в поле ввода (например, `https://www.pinterest.com/username/boardname/`)
2. Нажмите кнопку "Искать"
3. Дождитесь завершения парсинга
4. Просмотрите найденные изображения, сгруппированные по доскам

### Работа с изображениями

- **Просмотр изображения**: Нажмите на изображение или на кнопку с иконкой глаза
- **Скачать отдельное изображение**: Нажмите на кнопку с иконкой скачивания рядом с изображением
- **Выбор изображений**: Используйте чекбоксы для выбора нужных изображений
- **Удаление изображения**: Нажмите на крестик в углу изображения, чтобы добавить его в список игнорируемых

### Скачивание изображений

- **Скачать всю папку**: Нажмите кнопку "Скачать всю папку" для скачивания всех изображений из доски
- **Скачать отмеченные**: Выберите нужные изображения и нажмите "Скачать отмеченные"
- **Скачать все папки**: Нажмите кнопку "Скачать все папки" для скачивания всех сохраненных досок

### Управление ссылками

- **Сохранить ссылку**: Введите URL доски Pinterest в поле "Добавить ссылку" и нажмите "Сохранить"
- **Парсить сохраненную ссылку**: Нажмите на кнопку с иконкой поиска рядом с сохраненной ссылкой
- **Удалить ссылку**: Нажмите на кнопку с иконкой корзины рядом с сохраненной ссылкой
- **Парсить все ссылки**: Нажмите кнопку "Парсить все ссылки" для обработки всех сохраненных ссылок

## 🔧 Структура проекта

- `pinterest_scraper.php` - Основной файл с веб-интерфейсом
- `pinterest_scraper/` - Директория с основными компонентами
  - `PinterestScraper.php` - Класс для парсинга изображений с Pinterest
  - `ImageDownloader.php` - Класс для скачивания изображений
  - `api.php` - API для взаимодействия с парсером
  - `download_zip.php` - Скрипт для скачивания изображений в ZIP-архиве
  - `getAdditionalImages.php` - Вспомогательный скрипт для извлечения дополнительных изображений
- `download_image.php` - Скрипт для скачивания отдельных изображений
- `ignore_image.php` - Скрипт для добавления изображений в список игнорируемых
- `load_ignored_urls.php` - Скрипт для загрузки списка игнорируемых URL
- `load_links.php` - Скрипт для загрузки списка сохраненных ссылок
- `save_link.php` - Скрипт для сохранения новых ссылок
- `delete_link.php` - Скрипт для удаления ссылок
- `download_all_folders.php` - Скрипт для скачивания всех папок
- `links.txt` - Файл для хранения сохраненных ссылок
- `ignore.txt` - Файл для хранения игнорируемых URL

## 📄 Лицензия

Этот проект распространяется под лицензией MIT. См. файл [LICENSE](LICENSE) для получения дополнительной информации.

## 🤝 Вклад в проект

Вклады приветствуются! Если у вас есть идеи по улучшению проекта, пожалуйста, создайте Issue или Pull Request.


Если у вас есть вопросы или предложения, пожалуйста, создайте Issue в этом репозитории.
