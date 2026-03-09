<?php
/**
 * API контроллер для работы с маршрутами
 */
class ApiRoutesController extends ApiBaseController
{
    private $routeModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->routeModel = new RoutePlan();
    }
    
    /**
     * GET /api/v1/groups/{id}/routes
     * Получение списка маршрутов группы
     */
    public function list()
    {
        if (!$this->requireScope('routes:read')) return;
        
        $limit = min((int)($_GET['limit'] ?? 50), 500);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $routes = $this->db->fetchAll(
            "SELECT rp.id, rp.title, rp.description, rp.is_active, rp.created_at, rp.updated_at,
                    u.first_name, u.last_name,
                    (SELECT COUNT(*) FROM route_points WHERE route_plan_id = rp.id) as points_count
             FROM route_plans rp
             JOIN users u ON rp.created_by = u.id
             WHERE rp.group_id = ?
             ORDER BY rp.created_at DESC
             LIMIT ? OFFSET ?",
            [$this->groupId, $limit, $offset]
        );
        
        // Форматируем
        foreach ($routes as &$route) {
            $route['is_active'] = (bool)$route['is_active'];
            $route['points_count'] = (int)$route['points_count'];
        }
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM route_plans WHERE group_id = ?",
            [$this->groupId]
        )['count'];
        
        $this->logSuccess('/api/v1/groups/{id}/routes', 'GET');
        $this->success([
            'routes' => $routes,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ], 'Список маршрутов');
    }
    
    /**
     * POST /api/v1/groups/{id}/routes
     * Создание нового маршрута
     */
    public function create()
    {
        if (!$this->requireScope('routes:write')) return;
        
        $data = $this->getPostData();
        
        if (!$this->validateRequired($data, ['title'])) {
            return;
        }
        
        $title = trim($data['title']);
        $description = trim($data['description'] ?? '');
        
        if (strlen($title) < 3 || strlen($title) > 255) {
            $this->error('Название маршрута должно быть от 3 до 255 символов', 400);
        }
        
        $userId = Session::getUserId();
        $routeId = $this->db->insert('route_plans', [
            'group_id' => $this->groupId,
            'created_by' => $userId,
            'title' => $title,
            'description' => $description,
            'is_active' => 1
        ]);
        
        if (!$routeId) {
            $this->logError('/api/v1/groups/{id}/routes', 'POST', 500, 'Ошибка при создании');
            $this->error('Ошибка при создании маршрута', 500);
        }
        
        $route = $this->getRouteById($routeId);
        
        $this->logSuccess('/api/v1/groups/{id}/routes', 'POST');
        $this->success($route, 'Маршрут успешно создан', 201);
    }
    
    /**
     * GET /api/v1/routes/{id}
     * Получение маршрута с его точками
     */
    public function get()
    {
        if (!$this->requireScope('routes:read')) return;
        
        $routeId = $this->getRouteParam('id');
        if (!$routeId) {
            $this->error('ID маршрута не указан', 400);
        }
        
        $route = $this->getRouteById($routeId);
        
        if (!$route) {
            $this->error('Маршрут не найден', 404);
        }
        
        if ($route['group_id'] != $this->groupId) {
            $this->error('Доступ запрещен', 403);
        }
        
        // Получаем точки маршрута
        $points = $this->db->fetchAll(
            "SELECT * FROM route_points WHERE route_plan_id = ? ORDER BY order_index",
            [$routeId]
        );
        
        // Форматируем точки
        foreach ($points as &$point) {
            $point['latitude'] = (float)$point['latitude'];
            $point['longitude'] = (float)$point['longitude'];
            $point['order_index'] = (int)$point['order_index'];
            $point['is_completed'] = (bool)$point['is_completed'];
        }
        
        $route['points'] = $points;
        
        $this->logSuccess('/api/v1/routes/{id}', 'GET');
        $this->success($route, 'Информация о маршруте');
    }
    
    /**
     * PUT /api/v1/routes/{id}
     * Обновление маршрута
     */
    public function update()
    {
        if (!$this->requireScope('routes:write')) return;
        
        $routeId = $this->getRouteParam('id');
        if (!$routeId) {
            $this->error('ID маршрута не указан', 400);
        }
        
        $route = $this->db->fetchOne(
            "SELECT * FROM route_plans WHERE id = ? AND group_id = ?",
            [$routeId, $this->groupId]
        );
        
        if (!$route) {
            $this->error('Маршрут не найден', 404);
        }
        
        $data = $this->getPostData();
        $updateData = [];
        
        if (isset($data['title']) && !empty($data['title'])) {
            $title = trim($data['title']);
            if (strlen($title) < 3 || strlen($title) > 255) {
                $this->error('Название должно быть от 3 до 255 символов', 400);
            }
            $updateData['title'] = $title;
        }
        
        if (isset($data['description'])) {
            $updateData['description'] = trim($data['description']);
        }
        
        if (isset($data['is_active'])) {
            $updateData['is_active'] = (bool)$data['is_active'] ? 1 : 0;
        }
        
        if (empty($updateData)) {
            $this->error('Нет данных для обновления', 400);
        }
        
        $this->db->update('route_plans', $updateData, 'id = ?', [$routeId]);
        
        $updatedRoute = $this->getRouteById($routeId);
        
        $this->logSuccess('/api/v1/routes/{id}', 'PUT');
        $this->success($updatedRoute, 'Маршрут успешно обновлен');
    }
    
    /**
     * DELETE /api/v1/routes/{id}
     * Удаление маршрута
     */
    public function delete()
    {
        if (!$this->requireScope('routes:write')) return;
        
        $routeId = $this->getRouteParam('id');
        if (!$routeId) {
            $this->error('ID маршрута не указан', 400);
        }
        
        $route = $this->db->fetchOne(
            "SELECT * FROM route_plans WHERE id = ? AND group_id = ?",
            [$routeId, $this->groupId]
        );
        
        if (!$route) {
            $this->error('Маршрут не найден', 404);
        }
        
        // Удаляем все точки маршрута (cascade)
        $this->db->delete('route_points', 'route_plan_id = ?', [$routeId]);
        
        // Удаляем сам маршрут
        $this->db->delete('route_plans', 'id = ?', [$routeId]);
        
        $this->logSuccess('/api/v1/routes/{id}', 'DELETE');
        $this->success(null, 'Маршрут успешно удален');
    }
    
    /**
     * POST /api/v1/routes/{id}/points
     * Добавление точки к маршруту
     */
    public function addPoint()
    {
        if (!$this->requireScope('routes:write')) return;
        
        $routeId = $this->getRouteParam('id');
        if (!$routeId) {
            $this->error('ID маршрута не указан', 400);
        }
        
        $route = $this->db->fetchOne(
            "SELECT * FROM route_plans WHERE id = ? AND group_id = ?",
            [$routeId, $this->groupId]
        );
        
        if (!$route) {
            $this->error('Маршрут не найден', 404);
        }
        
        $data = $this->getPostData();
        
        if (!$this->validateRequired($data, ['title', 'latitude', 'longitude'])) {
            return;
        }
        
        $title = trim($data['title']);
        $latitude = (float)$data['latitude'];
        $longitude = (float)$data['longitude'];
        $description = trim($data['description'] ?? '');
        $orderIndex = (int)($data['order_index'] ?? 0);
        
        // Валидируем координаты
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $this->error('Неверные координаты', 400);
        }
        
        if (strlen($title) < 2 || strlen($title) > 255) {
            $this->error('Название точки должно быть от 2 до 255 символов', 400);
        }
        
        $pointId = $this->db->insert('route_points', [
            'route_plan_id' => $routeId,
            'title' => $title,
            'description' => $description,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'order_index' => $orderIndex
        ]);
        
        if (!$pointId) {
            $this->logError('/api/v1/routes/{id}/points', 'POST', 500, 'Ошибка при создании');
            $this->error('Ошибка при добавлении точки', 500);
        }
        
        $point = $this->db->fetchOne(
            "SELECT * FROM route_points WHERE id = ?",
            [$pointId]
        );
        
        // Форматируем
        $point['latitude'] = (float)$point['latitude'];
        $point['longitude'] = (float)$point['longitude'];
        $point['is_completed'] = (bool)$point['is_completed'];
        
        $this->logSuccess('/api/v1/routes/{id}/points', 'POST');
        $this->success($point, 'Точка маршрута успешно добавлена', 201);
    }
    
    /**
     * DELETE /api/v1/routes/points/{id}
     * Удаление точки маршрута
     */
    public function deletePoint()
    {
        if (!$this->requireScope('routes:write')) return;
        
        $pointId = $this->getRouteParam('id');
        if (!$pointId) {
            $this->error('ID точки не указан', 400);
        }
        
        $point = $this->db->fetchOne(
            "SELECT rp.*, rpm.group_id FROM route_points rp
             JOIN route_plans rpm ON rp.route_plan_id = rpm.id
             WHERE rp.id = ?",
            [$pointId]
        );
        
        if (!$point) {
            $this->error('Точка маршрута не найдена', 404);
        }
        
        if ($point['group_id'] != $this->groupId) {
            $this->error('Доступ запрещен', 403);
        }
        
        $this->db->delete('route_points', 'id = ?', [$pointId]);
        
        $this->logSuccess('/api/v1/routes/points/{id}', 'DELETE');
        $this->success(null, 'Точка маршрута успешно удалена');
    }
    
    /**
     * PUT /api/v1/routes/points/{id}
     * Отметить точку как выполненную
     */
    public function markPointCompleted()
    {
        if (!$this->requireScope('routes:write')) return;
        
        $pointId = $this->getRouteParam('id');
        if (!$pointId) {
            $this->error('ID точки не указан', 400);
        }
        
        $point = $this->db->fetchOne(
            "SELECT rp.*, rpm.group_id FROM route_points rp
             JOIN route_plans rpm ON rp.route_plan_id = rpm.id
             WHERE rp.id = ?",
            [$pointId]
        );
        
        if (!$point) {
            $this->error('Точка маршрута не найдена', 404);
        }
        
        if ($point['group_id'] != $this->groupId) {
            $this->error('Доступ запрещен', 403);
        }
        
        $data = $this->getPostData();
        $isCompleted = isset($data['is_completed']) ? (bool)$data['is_completed'] : true;
        
        $this->db->update('route_points',
            [
                'is_completed' => $isCompleted ? 1 : 0,
                'completed_at' => $isCompleted ? date('Y-m-d H:i:s') : null
            ],
            'id = ?',
            [$pointId]
        );
        
        $updated = $this->db->fetchOne("SELECT * FROM route_points WHERE id = ?", [$pointId]);
        $updated['latitude'] = (float)$updated['latitude'];
        $updated['longitude'] = (float)$updated['longitude'];
        $updated['is_completed'] = (bool)$updated['is_completed'];
        
        $this->logSuccess('/api/v1/routes/points/{id}', 'PUT');
        $this->success($updated, 'Статус точки обновлен');
    }
    
    /**
     * Получение маршрута по ID с форматированием
     * @param int $routeId
     * @return array|null
     */
    private function getRouteById($routeId)
    {
        $route = $this->db->fetchOne(
            "SELECT rp.*, u.first_name, u.last_name,
                    (SELECT COUNT(*) FROM route_points WHERE route_plan_id = rp.id) as points_count
             FROM route_plans rp
             JOIN users u ON rp.created_by = u.id
             WHERE rp.id = ?",
            [$routeId]
        );
        
        if (!$route) {
            return null;
        }
        
        $route['is_active'] = (bool)$route['is_active'];
        $route['points_count'] = (int)$route['points_count'];
        
        return $route;
    }
}
