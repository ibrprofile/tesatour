<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Страница не найдена - TESA Tour</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
</head>
<body class="guest-body">
    <main class="guest-main">
        <div class="empty-state" style="min-height: 100vh; justify-content: center;">
            <div class="empty-state-icon" style="width: 100px; height: 100px;">
                <i data-lucide="map-pin-off" width="48" height="48"></i>
            </div>
            <h1 class="empty-state-title">Страница не найдена</h1>
            <p class="empty-state-text">Похоже, вы заблудились. Эта страница не существует или была удалена.</p>
            <a href="/" class="btn btn-primary">
                <i data-lucide="home"></i>
                На главную
            </a>
        </div>
    </main>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
