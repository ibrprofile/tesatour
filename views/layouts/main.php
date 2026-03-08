<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#007AFF">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="TESA Tour">
    <meta name="description" content="Приложение для управления туристическими группами">
    <title><?= e($pageTitle ?? 'TESA Tour') ?> - TESA Tour</title>
    
    <!-- PWA -->
    <link rel="manifest" href="/public/manifest.json">
    <link rel="apple-touch-icon" href="/public/icons/icon-152x152.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/public/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/public/icons/icon-512x512.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Yandex Maps -->
    <script src="https://api-maps.yandex.ru/2.1/?apikey=<?= YANDEX_MAPS_API_KEY ?>&lang=ru_RU"></script>
    
    <!-- Styles -->
    <style><?php readfile(ROOT_PATH . '/public/css/app.css'); ?></style>
    
    <?php if (isset($extraHead)): ?>
        <?= $extraHead ?>
    <?php endif; ?>
</head>
<body>
    <?php if (Session::isLoggedIn()): ?>
        <?php View::partial('partials/header'); ?>
    <?php endif; ?>
    
    <main class="main-content">
        <?php View::partial('partials/toast'); ?>
        <?= $content ?>
    </main>
    
    <!-- Geolocation Permission Modal -->
    <div id="geoModal" class="modal" style="display: none;">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-icon">
                <i data-lucide="map-pin"></i>
            </div>
            <h3>Доступ к геолокации</h3>
            <p>Для работы приложения необходим доступ к вашему местоположению. Это позволит отслеживать участников группы и отправлять SOS-сигналы.</p>
            <button type="button" class="btn btn-primary btn-block" onclick="requestGeolocation()">
                Разрешить доступ
            </button>
        </div>
    </div>
    
    <!-- Scripts -->
    <script><?php readfile(ROOT_PATH . '/public/js/app.js'); ?></script>
    <script><?php readfile(ROOT_PATH . '/public/js/pwa.js'); ?></script>
    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
    
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
