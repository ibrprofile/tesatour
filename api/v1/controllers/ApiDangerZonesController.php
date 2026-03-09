<?php
/**
 * API контроллер для работы с опасными зонами
 */
class ApiDangerZonesController extends ApiBaseController
{
    /**
     * GET /api/v1/groups/{id}/danger-zones
     * Получение списка опасных зон
     */
    public function list()
    {
        if (!$this->requireScope('danger_zones:read')) return;
        
        $limit = min((int)($_GET['limit'] ?? 50), 500);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $zones = $this->db->fetchAll(
            "SELECT dz.*, u.first_name, u.last_name
             FROM danger_zones dz
             JOIN users u ON dz.created_by = u.id
             WHERE dz.group_id = ?
             ORDER BY dz.created_at DESC
             LIMIT ? OFFSET ?",
            [$this->groupId, $limit, $offset]
        );
        
        // Форматируем
        foreach ($zones as &$zone) {
            $zone['latitude'] = (float)$zone['latitude'];
            $zone['longitude'] = (float)$zone['longitude'];
        }
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM danger_zones WHERE group_id = ?",
            [$this->groupId]
        )['count'];
        
        $this->logSuccess('/api/v1/groups/{id}/danger-zones', 'GET');
        $this->success([
            'zones' => $zones,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ], 'Список опасных зон');
    }
    
    /**
     * POST /api/v1/groups/{id}/danger-zones
     * Создание новой опасной зоны
     */
    public function create()
    {
        if (!$this->requireScope('danger_zones:write')) return;
        
        $data = $this->getPostData();
        
        if (!$this->validateRequired($data, ['title', 'latitude', 'longitude'])) {
            return;
        }
        
        $title = trim($data['title']);
        $latitude = (float)$data['latitude'];
        $longitude = (float)$data['longitude'];
        $description = trim($data['description'] ?? '');
        
        // Валидируем данные
        if (strlen($title) < 2 || strlen($title) > 255) {
            $this->error('Название зоны должно быть от 2 до 255 символов', 400);
        }
        
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $this->error('Неверные координаты', 400);
        }
        
        $userId = Session::getUserId();
        $zoneId = $this->db->insert('danger_zones', [
            'group_id' => $this->groupId,
            'created_by' => $userId,
            'title' => $title,
            'description' => $description,
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);
        
        if (!$zoneId) {
            $this->logError('/api/v1/groups/{id}/danger-zones', 'POST', 500, 'Ошибка при создании');
            $this->error('Ошибка при создании опасной зоны', 500);
        }
        
        $zone = $this->getZone($zoneId);
        
        $this->logSuccess('/api/v1/groups/{id}/danger-zones', 'POST');
        $this->success($zone, 'Опасная зона успешно создана', 201);
    }
    
    /**
     * GET /api/v1/danger-zones/{id}
     * Получение информации об опасной зоне
     */
    public function get()
    {
        if (!$this->requireScope('danger_zones:read')) return;
        
        $zoneId = $this->getRouteParam('id');
        if (!$zoneId) {
            $this->error('ID зоны не указан', 400);
        }
        
        $zone = $this->getZone($zoneId);
        
        if (!$zone) {
            $this->error('Опасная зона не найдена', 404);
        }
        
        if ($zone['group_id'] != $this->groupId) {
            $this->error('Доступ запрещен', 403);
        }
        
        $this->logSuccess('/api/v1/danger-zones/{id}', 'GET');
        $this->success($zone, 'Информация об опасной зоне');
    }
    
    /**
     * PUT /api/v1/danger-zones/{id}
     * Обновление опасной зоны
     */
    public function update()
    {
        if (!$this->requireScope('danger_zones:write')) return;
        
        $zoneId = $this->getRouteParam('id');
        if (!$zoneId) {
            $this->error('ID зоны не указан', 400);
        }
        
        $zone = $this->db->fetchOne(
            "SELECT * FROM danger_zones WHERE id = ? AND group_id = ?",
            [$zoneId, $this->groupId]
        );
        
        if (!$zone) {
            $this->error('Опасная зона не найдена', 404);
        }
        
        $data = $this->getPostData();
        $updateData = [];
        
        if (isset($data['title']) && !empty($data['title'])) {
            $title = trim($data['title']);
            if (strlen($title) < 2 || strlen($title) > 255) {
                $this->error('Название должно быть от 2 до 255 символов', 400);
            }
            $updateData['title'] = $title;
        }
        
        if (isset($data['description'])) {
            $updateData['description'] = trim($data['description']);
        }
        
        if (isset($data['latitude'])) {
            $lat = (float)$data['latitude'];
            if ($lat < -90 || $lat > 90) {
                $this->error('Неверная широта', 400);
            }
            $updateData['latitude'] = $lat;
        }
        
        if (isset($data['longitude'])) {
            $lng = (float)$data['longitude'];
            if ($lng < -180 || $lng > 180) {
                $this->error('Неверная долгота', 400);
            }
            $updateData['longitude'] = $lng;
        }
        
        if (empty($updateData)) {
            $this->error('Нет данных для обновления', 400);
        }
        
        $this->db->update('danger_zones', $updateData, 'id = ?', [$zoneId]);
        
        $updatedZone = $this->getZone($zoneId);
        
        $this->logSuccess('/api/v1/danger-zones/{id}', 'PUT');
        $this->success($updatedZone, 'Опасная зона успешно обновлена');
    }
    
    /**
     * DELETE /api/v1/danger-zones/{id}
     * Удаление опасной зоны
     */
    public function delete()
    {
        if (!$this->requireScope('danger_zones:write')) return;
        
        $zoneId = $this->getRouteParam('id');
        if (!$zoneId) {
            $this->error('ID зоны не указан', 400);
        }
        
        $zone = $this->db->fetchOne(
            "SELECT * FROM danger_zones WHERE id = ? AND group_id = ?",
            [$zoneId, $this->groupId]
        );
        
        if (!$zone) {
            $this->error('Опасная зона не найдена', 404);
        }
        
        $this->db->delete('danger_zones', 'id = ?', [$zoneId]);
        
        $this->logSuccess('/api/v1/danger-zones/{id}', 'DELETE');
        $this->success(null, 'Опасная зона успешно удалена');
    }
    
    /**
     * Получение зоны по ID с форматированием
     * @param int $zoneId
     * @return array|null
     */
    private function getZone($zoneId)
    {
        $zone = $this->db->fetchOne(
            "SELECT dz.*, u.first_name, u.last_name
             FROM danger_zones dz
             JOIN users u ON dz.created_by = u.id
             WHERE dz.id = ?",
            [$zoneId]
        );
        
        if (!$zone) {
            return null;
        }
        
        $zone['latitude'] = (float)$zone['latitude'];
        $zone['longitude'] = (float)$zone['longitude'];
        
        return $zone;
    }
}
