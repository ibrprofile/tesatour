<?php
/**
 * TESA Tour App - Главный входной файл
 */

// Загрузка конфигурации
require_once __DIR__ . '/config/config.php';

// === ОБРАБОТКА API ЗАПРОСОВ ===
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($path, '/api/v1') === 0) {
    require_once __DIR__ . '/api.php';
    exit;
}

// Запуск сессии
Session::start();

// Инициализация роутера
$router = new Router();

// Middleware для проверки авторизации
$authMiddleware = function() {
    if (!Session::isLoggedIn()) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            Response::unauthorized();
        }
        Router::redirect('/login');
        return false;
    }
    return true;
};

// Middleware для гостей (не авторизованных)
$guestMiddleware = function() {
    if (Session::isLoggedIn()) {
        Router::redirect('/dashboard');
        return false;
    }
    return true;
};

// === ПУБЛИЧНЫЕ МАРШРУТЫ ===

// Главная страница
$router->get('/', [PageController::class, 'home']);

// Авторизация
$router->get('/login', [AuthController::class, 'loginForm'], [$guestMiddleware]);
$router->post('/login', [AuthController::class, 'login'], [$guestMiddleware]);

// Регистрация
$router->get('/register', [AuthController::class, 'registerForm'], [$guestMiddleware]);
$router->post('/register', [AuthController::class, 'register'], [$guestMiddleware]);

// Выход
$router->get('/logout', [AuthController::class, 'logout']);

// Приглашение в группу (публичное)
$router->get('/invite/{code}', [GroupController::class, 'invite']);
$router->post('/invite/{code}', [GroupController::class, 'joinByInvite']);

// SOS страница (публичная для просмотра)
$router->get('/sos/{id}', [SosController::class, 'view']);

// === ЗАЩИЩЕННЫЕ МАРШРУТЫ ===

// Дашборд
$router->get('/dashboard', [DashboardController::class, 'index'], [$authMiddleware]);

// Профиль
$router->get('/profile', [ProfileController::class, 'index'], [$authMiddleware]);
$router->post('/profile/update', [ProfileController::class, 'update'], [$authMiddleware]);
$router->post('/profile/avatar', [ProfileController::class, 'updateAvatar'], [$authMiddleware]);

// Настройки
$router->get('/settings', [SettingsController::class, 'index'], [$authMiddleware]);
$router->post('/settings/telegram', [SettingsController::class, 'linkTelegram'], [$authMiddleware]);
$router->post('/settings/telegram/unlink', [SettingsController::class, 'unlinkTelegram'], [$authMiddleware]);
$router->post('/settings/password', [SettingsController::class, 'changePassword'], [$authMiddleware]);
$router->post('/settings/upgrade', [SettingsController::class, 'upgradeToAgency'], [$authMiddleware]);

// Группы
$router->get('/groups', [GroupController::class, 'index'], [$authMiddleware]);
$router->get('/groups/create', [GroupController::class, 'createForm'], [$authMiddleware]);
$router->post('/groups/create', [GroupController::class, 'create'], [$authMiddleware]);
$router->get('/groups/{id}', [GroupController::class, 'show'], [$authMiddleware]);
$router->post('/groups/{id}/update', [GroupController::class, 'update'], [$authMiddleware]);
$router->post('/groups/{id}/close', [GroupController::class, 'close'], [$authMiddleware]);
$router->post('/groups/{id}/leave', [GroupController::class, 'leave'], [$authMiddleware]);

// Участники группы
$router->get('/groups/{id}/members', [MemberController::class, 'index'], [$authMiddleware]);
$router->post('/groups/{id}/members/{userId}/kick', [MemberController::class, 'kick'], [$authMiddleware]);
$router->post('/groups/{id}/members/{userId}/role', [MemberController::class, 'changeRole'], [$authMiddleware]);
$router->get('/groups/{id}/members/{userId}/location', [MemberController::class, 'location'], [$authMiddleware]);

// Приглашения и черный список
$router->get('/groups/{id}/invites', [GroupController::class, 'inviteLinks'], [$authMiddleware]);
$router->post('/groups/{id}/invites/toggle', [GroupController::class, 'toggleApproval'], [$authMiddleware]);
$router->post('/groups/{id}/invites/{requestId}/approve', [GroupController::class, 'approveRequest'], [$authMiddleware]);
$router->post('/groups/{id}/invites/{requestId}/reject', [GroupController::class, 'rejectRequest'], [$authMiddleware]);
$router->get('/groups/{id}/blacklist', [GroupController::class, 'blacklist'], [$authMiddleware]);
$router->post('/groups/{id}/blacklist/{userId}/add', [GroupController::class, 'addToBlacklist'], [$authMiddleware]);
$router->post('/groups/{id}/blacklist/{userId}/remove', [GroupController::class, 'removeFromBlacklist'], [$authMiddleware]);

// Настройки группы
$router->get('/groups/{id}/settings', [GroupController::class, 'settings'], [$authMiddleware]);
$router->post('/groups/{id}/settings/update', [GroupController::class, 'updateSettings'], [$authMiddleware]);

// SOS вызовы
$router->get('/groups/{id}/sos', [SosController::class, 'index'], [$authMiddleware]);
$router->post('/groups/{id}/sos/create', [SosController::class, 'create'], [$authMiddleware]);
$router->post('/sos/{id}/resolve', [SosController::class, 'resolve'], [$authMiddleware]);

// План-листы маршрутов
$router->get('/groups/{id}/routes', [RouteController::class, 'index'], [$authMiddleware]);
$router->get('/groups/{id}/routes/create', [RouteController::class, 'createForm'], [$authMiddleware]);
$router->post('/groups/{id}/routes/create', [RouteController::class, 'create'], [$authMiddleware]);
$router->get('/routes/{id}', [RouteController::class, 'show'], [$authMiddleware]);
$router->post('/routes/{id}/update', [RouteController::class, 'update'], [$authMiddleware]);
$router->post('/routes/{id}/delete', [RouteController::class, 'delete'], [$authMiddleware]);
$router->post('/routes/{id}/points', [RouteController::class, 'addPoint'], [$authMiddleware]);
$router->post('/routes/points/{id}/delete', [RouteController::class, 'deletePoint'], [$authMiddleware]);
$router->post('/routes/points/{id}/complete', [RouteController::class, 'markPointCompleted'], [$authMiddleware]);
$router->post('/routes/points/{id}/uncomplete', [RouteController::class, 'unmarkPointCompleted'], [$authMiddleware]);

// Каналы
$router->get('/groups/{id}/channels', [ChannelController::class, 'index'], [$authMiddleware]);
$router->post('/groups/{id}/channels/create', [ChannelController::class, 'create'], [$authMiddleware]);
$router->post('/channels/create/{id}', [ChannelController::class, 'create'], [$authMiddleware]);
$router->get('/channels/{id}', [ChannelController::class, 'show'], [$authMiddleware]);
$router->post('/channels/{id}/posts', [ChannelController::class, 'postMessage'], [$authMiddleware]);
$router->post('/channels/post/{id}', [ChannelController::class, 'postMessage'], [$authMiddleware]);
$router->post('/channels/{id}/delete', [ChannelController::class, 'deleteMessage'], [$authMiddleware]);
$router->post('/channels/edit/{id}', [ChannelController::class, 'editMessage'], [$authMiddleware]);
$router->post('/channels/delete/{id}', [ChannelController::class, 'deleteMessage'], [$authMiddleware]);
$router->post('/channels/pin/{id}', [ChannelController::class, 'pinMessage'], [$authMiddleware]);
$router->post('/channels/reaction/{id}', [ChannelController::class, 'addReaction'], [$authMiddleware]);

// Чаты
$router->get('/groups/{id}/chat', [ChatController::class, 'show'], [$authMiddleware]);
$router->post('/groups/{id}/chat/send', [ChatController::class, 'sendMessage'], [$authMiddleware]);
$router->post('/chat/messages/{id}/update', [ChatController::class, 'editMessage'], [$authMiddleware]);
$router->post('/chat/messages/{id}/delete', [ChatController::class, 'deleteMessage'], [$authMiddleware]);
$router->post('/chat/messages/{id}/react', [ChatController::class, 'addReaction'], [$authMiddleware]);
$router->post('/chat/messages/{id}/pin', [ChatController::class, 'pinMessage'], [$authMiddleware]);

// Опасные зоны
$router->get('/groups/{id}/danger-zones', [DangerZoneController::class, 'index'], [$authMiddleware]);
$router->post('/groups/{id}/danger-zones', [DangerZoneController::class, 'create'], [$authMiddleware]);
$router->post('/danger-zones/create/{id}', [DangerZoneController::class, 'create'], [$authMiddleware]);
$router->post('/danger-zones/update/{id}', [DangerZoneController::class, 'update'], [$authMiddleware]);
$router->post('/danger-zones/delete/{id}', [DangerZoneController::class, 'delete'], [$authMiddleware]);
$router->post('/danger-zones/{id}/update', [DangerZoneController::class, 'update'], [$authMiddleware]);
$router->post('/danger-zones/{id}/delete', [DangerZoneController::class, 'delete'], [$authMiddleware]);

// Подписка
$router->get('/subscription', [SubscriptionController::class, 'index'], [$authMiddleware]);
$router->post('/subscription/create', [SubscriptionController::class, 'create'], [$authMiddleware]);
$router->post('/subscription/cancel', [SubscriptionController::class, 'cancel'], [$authMiddleware]);
$router->get('/subscription/callback', [SubscriptionController::class, 'callback'], [$authMiddleware]);
$router->post('/subscription/webhook', [SubscriptionController::class, 'webhook']);

// Техподдержка
$router->get('/support', [SupportController::class, 'index'], [$authMiddleware]);
$router->post('/support/send', [SupportController::class, 'sendMessage'], [$authMiddleware]);
$router->get('/api/support/messages', [SupportController::class, 'getMessages'], [$authMiddleware]);

// Админка
$router->get('/admin', [AdminController::class, 'index'], [$authMiddleware]);
$router->get('/admin/users', [AdminController::class, 'users'], [$authMiddleware]);
$router->get('/admin/groups', [AdminController::class, 'groups'], [$authMiddleware]);
$router->get('/admin/notifications', [AdminController::class, 'notifications'], [$authMiddleware]);
$router->get('/admin/support', [AdminController::class, 'supportList'], [$authMiddleware]);
$router->get('/admin/support/{userId}', [AdminController::class, 'supportChat'], [$authMiddleware]);
$router->post('/admin/support/{userId}/send', [AdminController::class, 'sendSupportReply'], [$authMiddleware]);
$router->post('/admin/users/{id}/toggle-admin', [AdminController::class, 'toggleAdmin'], [$authMiddleware]);
$router->post('/admin/users/{id}/delete', [AdminController::class, 'deleteUser'], [$authMiddleware]);
$router->post('/admin/groups/{id}/delete', [AdminController::class, 'deleteGroup'], [$authMiddleware]);
$router->post('/admin/notifications/send', [AdminController::class, 'sendNotification'], [$authMiddleware]);
$router->post('/admin/toggle-mode', [AdminController::class, 'toggleAdminMode'], [$authMiddleware]);

// Юридические страницы
$router->get('/legal/terms', [PageController::class, 'terms']);
$router->get('/legal/privacy', [PageController::class, 'privacy']);
$router->get('/legal/offer', [PageController::class, 'offer']);

// === API МАРШРУТЫ ===

// Геолокация
$router->post('/api/location/update', [ApiController::class, 'updateLocation'], [$authMiddleware]);
$router->get('/api/location/history/{userId}', [ApiController::class, 'locationHistory'], [$authMiddleware]);

// SOS API
$router->get('/api/sos/active/{groupId}', [ApiController::class, 'activeSos'], [$authMiddleware]);

// Участники API
$router->get('/api/groups/{id}/members/locations', [ApiController::class, 'membersLocations'], [$authMiddleware]);

// Уведомления API
$router->get('/api/notifications/unread', [NotificationController::class, 'getUnread'], [$authMiddleware]);
$router->post('/api/notifications/mark-read', [NotificationController::class, 'markAsRead'], [$authMiddleware]);
$router->post('/api/notifications/subscribe', [NotificationController::class, 'subscribe'], [$authMiddleware]);
$router->post('/api/notifications/unsubscribe', [NotificationController::class, 'unsubscribe'], [$authMiddleware]);

// Запуск роутера
$router->dispatch();
