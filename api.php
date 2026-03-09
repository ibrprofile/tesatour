<?php
/**
 * TESA Tour API v1 Router
 * Точка входа для всех API запросов
 */

// Подключаем необходимые файлы
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Group.php';

// Подключаем API компоненты
require_once __DIR__ . '/api/v1/models/ApiKey.php';
require_once __DIR__ . '/api/v1/middleware/ApiAuthMiddleware.php';
require_once __DIR__ . '/api/v1/controllers/ApiBaseController.php';
require_once __DIR__ . '/api/v1/controllers/ApiKeysController.php';
require_once __DIR__ . '/api/v1/controllers/ApiGroupsController.php';
require_once __DIR__ . '/api/v1/controllers/ApiMembersController.php';
require_once __DIR__ . '/api/v1/controllers/ApiRoutesController.php';
require_once __DIR__ . '/api/v1/controllers/ApiDangerZonesController.php';
require_once __DIR__ . '/api/v1/controllers/ApiSosController.php';
require_once __DIR__ . '/api/v1/controllers/ApiChannelsController.php';
require_once __DIR__ . '/api/v1/controllers/ApiChatsController.php';

// Включаем CORS для локальной разработки
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Устанавливаем JSON заголовок
header('Content-Type: application/json; charset=utf-8');

// Получаем путь запроса
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/api/v1';

// Удаляем базовый путь и нормализуем
$path = str_replace($basePath, '', $path);
$path = trim($path, '/');

// Разбиваем путь
$segments = explode('/', $path);
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Маршруты API v1
 */
try {
    // API токены (управление ключами)
    if (count($segments) >= 1 && $segments[0] === 'keys') {
        $controller = new ApiKeysController();
        
        if (count($segments) === 1 && $method === 'GET') {
            $controller->list();
        } elseif (count($segments) === 1 && $method === 'POST') {
            $controller->create();
        } elseif (count($segments) === 2 && $method === 'GET') {
            $GLOBALS['routeParams'] = ['id' => $segments[1]];
            $controller->get();
        } elseif (count($segments) === 2 && $method === 'PUT') {
            $GLOBALS['routeParams'] = ['id' => $segments[1]];
            $controller->update();
        } elseif (count($segments) === 2 && $method === 'DELETE') {
            $GLOBALS['routeParams'] = ['id' => $segments[1]];
            $controller->delete();
        } elseif (count($segments) === 3 && $segments[2] === 'logs' && $method === 'GET') {
            $GLOBALS['routeParams'] = ['id' => $segments[1]];
            $controller->getLogs();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
        }
    }
    // Группы
    elseif (preg_match('/^groups\/(\d+)$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $groupId];
        
        $controller = new ApiGroupsController();
        
        if ($method === 'GET') {
            $controller->get();
        } elseif ($method === 'PUT') {
            $controller->update();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Статистика группы
    elseif (preg_match('/^groups\/(\d+)\/stats$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $groupId];
        
        $controller = new ApiGroupsController();
        if ($method === 'GET') {
            $controller->getStats();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Члены группы
    elseif (preg_match('/^groups\/(\d+)\/members$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $groupId];
        
        $controller = new ApiMembersController();
        if ($method === 'GET') {
            $controller->list();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Информация о члене группы
    elseif (preg_match('/^groups\/(\d+)\/members\/(\d+)$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $userId = (int)$matches[2];
        $GLOBALS['routeParams'] = ['id' => $groupId, 'userId' => $userId];
        
        $controller = new ApiMembersController();
        if ($method === 'GET') {
            $controller->get();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Роль члена группы
    elseif (preg_match('/^groups\/(\d+)\/members\/(\d+)\/role$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $userId = (int)$matches[2];
        $GLOBALS['routeParams'] = ['id' => $groupId, 'userId' => $userId];
        
        $controller = new ApiMembersController();
        if ($method === 'POST') {
            $controller->updateRole();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Удаление члена группы
    elseif (preg_match('/^groups\/(\d+)\/members\/(\d+)$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $userId = (int)$matches[2];
        $GLOBALS['routeParams'] = ['id' => $groupId, 'userId' => $userId];
        
        $controller = new ApiMembersController();
        if ($method === 'DELETE') {
            $controller->remove();
        }
    }
    // История местоположения члена
    elseif (preg_match('/^groups\/(\d+)\/members\/(\d+)\/location$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $userId = (int)$matches[2];
        $GLOBALS['routeParams'] = ['id' => $groupId, 'userId' => $userId];
        
        $controller = new ApiMembersController();
        if ($method === 'GET') {
            $controller->getLocationHistory();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Маршруты группы
    elseif (preg_match('/^groups\/(\d+)\/routes$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $groupId];
        
        $controller = new ApiRoutesController();
        if ($method === 'GET') {
            $controller->list();
        } elseif ($method === 'POST') {
            $controller->create();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Отдельный маршрут
    elseif (preg_match('/^routes\/(\d+)$/', $path, $matches)) {
        $routeId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $routeId];
        
        $controller = new ApiRoutesController();
        if ($method === 'GET') {
            $controller->get();
        } elseif ($method === 'PUT') {
            $controller->update();
        } elseif ($method === 'DELETE') {
            $controller->delete();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Точки маршрута
    elseif (preg_match('/^routes\/(\d+)\/points$/', $path, $matches)) {
        $routeId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $routeId];
        
        $controller = new ApiRoutesController();
        if ($method === 'POST') {
            $controller->addPoint();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Точка маршрута
    elseif (preg_match('/^routes\/points\/(\d+)$/', $path, $matches)) {
        $pointId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $pointId];
        
        $controller = new ApiRoutesController();
        if ($method === 'DELETE') {
            $controller->deletePoint();
        } elseif ($method === 'PUT') {
            $controller->markPointCompleted();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Опасные зоны группы
    elseif (preg_match('/^groups\/(\d+)\/danger-zones$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $groupId];
        
        $controller = new ApiDangerZonesController();
        if ($method === 'GET') {
            $controller->list();
        } elseif ($method === 'POST') {
            $controller->create();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Отдельная опасная зона
    elseif (preg_match('/^danger-zones\/(\d+)$/', $path, $matches)) {
        $zoneId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $zoneId];
        
        $controller = new ApiDangerZonesController();
        if ($method === 'GET') {
            $controller->get();
        } elseif ($method === 'PUT') {
            $controller->update();
        } elseif ($method === 'DELETE') {
            $controller->delete();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // SOS вызовы группы
    elseif (preg_match('/^groups\/(\d+)\/sos$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $groupId];
        
        $controller = new ApiSosController();
        if ($method === 'GET') {
            $controller->list();
        } elseif ($method === 'POST') {
            $controller->create();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Отдельный SOS вызов
    elseif (preg_match('/^sos\/(\d+)$/', $path, $matches)) {
        $sosId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $sosId];
        
        $controller = new ApiSosController();
        if ($method === 'GET') {
            $controller->get();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Разрешение SOS вызова
    elseif (preg_match('/^sos\/(\d+)\/resolve$/', $path, $matches)) {
        $sosId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $sosId];
        
        $controller = new ApiSosController();
        if ($method === 'POST') {
            $controller->resolve();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Каналы группы
    elseif (preg_match('/^groups\/(\d+)\/channels$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $groupId];
        
        $controller = new ApiChannelsController();
        if ($method === 'GET') {
            $controller->list();
        } elseif ($method === 'POST') {
            $controller->create();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Отдельный канал
    elseif (preg_match('/^channels\/(\d+)$/', $path, $matches)) {
        $channelId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $channelId];
        
        $controller = new ApiChannelsController();
        if ($method === 'GET') {
            $controller->get();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Сообщения канала
    elseif (preg_match('/^channels\/(\d+)\/messages$/', $path, $matches)) {
        $channelId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $channelId];
        
        $controller = new ApiChannelsController();
        if ($method === 'GET') {
            $controller->getMessages();
        } elseif ($method === 'POST') {
            $controller->sendMessage();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Редактирование/удаление сообщения канала
    elseif (preg_match('/^channels\/(\d+)\/messages\/(\d+)$/', $path, $matches)) {
        $channelId = (int)$matches[1];
        $messageId = (int)$matches[2];
        $GLOBALS['routeParams'] = ['id' => $channelId, 'messageId' => $messageId];
        
        $controller = new ApiChannelsController();
        if ($method === 'PUT') {
            $controller->editMessage();
        } elseif ($method === 'DELETE') {
            $controller->deleteMessage();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Групповой чат
    elseif (preg_match('/^groups\/(\d+)\/chat$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $GLOBALS['routeParams'] = ['id' => $groupId];
        
        $controller = new ApiChatsController();
        if ($method === 'GET') {
            $controller->getMessages();
        } elseif ($method === 'POST') {
            $controller->sendMessage();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Редактирование/удаление сообщения чата
    elseif (preg_match('/^groups\/(\d+)\/chat\/(\d+)$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $messageId = (int)$matches[2];
        $GLOBALS['routeParams'] = ['id' => $groupId, 'messageId' => $messageId];
        
        $controller = new ApiChatsController();
        if ($method === 'PUT') {
            $controller->editMessage();
        } elseif ($method === 'DELETE') {
            $controller->deleteMessage();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Реакции сообщение чата
    elseif (preg_match('/^groups\/(\d+)\/chat\/(\d+)\/reactions$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $messageId = (int)$matches[2];
        $GLOBALS['routeParams'] = ['id' => $groupId, 'messageId' => $messageId];
        
        $controller = new ApiChatsController();
        if ($method === 'POST') {
            $controller->addReaction();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // Закрепление сообщение чата
    elseif (preg_match('/^groups\/(\d+)\/chat\/(\d+)\/pin$/', $path, $matches)) {
        $groupId = (int)$matches[1];
        $messageId = (int)$matches[2];
        $GLOBALS['routeParams'] = ['id' => $groupId, 'messageId' => $messageId];
        
        $controller = new ApiChatsController();
        if ($method === 'PUT') {
            $controller->togglePin();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    }
    // API Status
    elseif ($path === '' || $path === 'status') {
        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'version' => 'v1',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    // 404
    else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
