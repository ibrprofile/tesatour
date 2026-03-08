<?php
/**
 * TESA Tour App - Главный конфигурационный файл
 */

// Режим отладки
define('DEBUG_MODE', true);

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'tesatour');
define('DB_USER', 'tesatour');
define('DB_PASS', 'tesatour#');
define('DB_CHARSET', 'utf8mb4');

// Настройки приложения
define('APP_NAME', 'TESA Tour');
define('APP_URL', 'https://tour.tesapp.ru');
define('APP_VERSION', '1.0.0');

// Yandex Maps API
define('YANDEX_MAPS_API_KEY', '84c7eaf1-e983-4a59-8257-90019c39df52');
define('MAP_DEFAULT_CENTER', json_encode([55.7558, 37.6173]));
define('MAP_DEFAULT_ZOOM', 12);

// Telegram Bot
define('TELEGRAM_BOT_TOKEN', '7622370308:AAFIWA2G57V7zJ1ZvF0OSf9lh15G_3CBdFQ');
define('TELEGRAM_BOT_USERNAME', 'tesatour_bot');

// Пути к директориям
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// URL путей
define('ASSETS_URL', '/public');
define('UPLOADS_URL', '/uploads');

// Настройки сессии
define('SESSION_NAME', 'tesa_tour_session');
define('SESSION_LIFETIME', 86400 * 7); // 7 дней

// Настройки загрузки файлов
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Настройки геолокации
define('GEO_UPDATE_INTERVAL', 30); // секунд между обновлениями

// Роли в группах
define('ROLE_OWNER', 'owner');
define('ROLE_ADMIN', 'admin');
define('ROLE_MEMBER', 'member');

// Статусы групп
define('GROUP_STATUS_ACTIVE', 'active');
define('GROUP_STATUS_CLOSED', 'closed');

// Статусы SOS вызовов
define('SOS_STATUS_ACTIVE', 'active');
define('SOS_STATUS_RESOLVED', 'resolved');

// Обработка ошибок
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Часовой пояс
date_default_timezone_set('Europe/Moscow');

// Автозагрузка классов
spl_autoload_register(function ($className) {
    $paths = [
        CORE_PATH . '/' . $className . '.php',
        CONTROLLERS_PATH . '/' . $className . '.php',
        MODELS_PATH . '/' . $className . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
