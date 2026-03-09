<?php
/**
 * API контроллер для работы с группами
 */
class ApiGroupsController extends ApiBaseController
{
    private $groupModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->groupModel = new Group();
    }
    
    /**
     * GET /api/v1/groups/{id}
     * Получение информации о группе
     */
    public function get()
    {
        if (!$this->requireScope('groups:read')) return;
        
        $group = $this->groupModel->findById($this->groupId);
        if (!$this->checkResourceExists($group, 'Группа')) return;
        
        // Форматируем ответ
        $response = [
            'id' => $group['id'],
            'name' => $group['name'],
            'description' => $group['description'],
            'invite_code' => $group['invite_code'],
            'owner_id' => $group['owner_id'],
            'status' => $group['status'],
            'require_approval' => (bool)$group['require_approval'],
            'created_at' => $group['created_at'],
            'updated_at' => $group['updated_at']
        ];
        
        $this->logSuccess('/api/v1/groups/{id}', 'GET');
        $this->success($response, 'Информация о группе');
    }
    
    /**
     * PUT /api/v1/groups/{id}
     * Обновление информации о группе (только для owner)
     */
    public function update()
    {
        if (!$this->requireScope('groups:write')) return;
        
        $group = $this->groupModel->findById($this->groupId);
        if (!$this->checkResourceExists($group, 'Группа')) return;
        
        // Только owner может редактировать
        $userId = Session::getUserId();
        if ($group['owner_id'] != $userId) {
            $this->error('Только владелец группы может редактировать её', 403);
        }
        
        $data = $this->getPostData();
        $updateData = [];
        
        // Обновляем только разрешенные поля
        if (isset($data['name']) && !empty($data['name'])) {
            $name = trim($data['name']);
            if (strlen($name) < 3 || strlen($name) > 255) {
                $this->error('Название группы должно быть от 3 до 255 символов', 400);
            }
            $updateData['name'] = $name;
        }
        
        if (isset($data['description'])) {
            $updateData['description'] = trim($data['description']);
        }
        
        if (isset($data['require_approval'])) {
            $updateData['require_approval'] = (bool)$data['require_approval'] ? 1 : 0;
        }
        
        if (empty($updateData)) {
            $this->error('Нет данных для обновления', 400);
        }
        
        // Обновляем
        if (!$this->groupModel->update($this->groupId, $updateData)) {
            $this->logError('/api/v1/groups/{id}', 'PUT', 500, 'Ошибка при обновлении');
            $this->error('Ошибка при обновлении группы', 500);
        }
        
        $updatedGroup = $this->groupModel->findById($this->groupId);
        $response = [
            'id' => $updatedGroup['id'],
            'name' => $updatedGroup['name'],
            'description' => $updatedGroup['description'],
            'require_approval' => (bool)$updatedGroup['require_approval'],
            'updated_at' => $updatedGroup['updated_at']
        ];
        
        $this->logSuccess('/api/v1/groups/{id}', 'PUT');
        $this->success($response, 'Группа успешно обновлена');
    }
    
    /**
     * GET /api/v1/groups/{id}/stats
     * Получение статистики группы
     */
    public function getStats()
    {
        if (!$this->requireScope('groups:read')) return;
        
        // Количество участников
        $memberCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM group_members WHERE group_id = ?",
            [$this->groupId]
        )['count'];
        
        // Количество маршрутов
        $routeCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM route_plans WHERE group_id = ? AND is_active = 1",
            [$this->groupId]
        )['count'];
        
        // Количество опасных зон
        $dangerZoneCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM danger_zones WHERE group_id = ?",
            [$this->groupId]
        )['count'];
        
        // Активные SOS вызовы
        $activeSosCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM sos_alerts WHERE group_id = ? AND status = 'active'",
            [$this->groupId]
        )['count'];
        
        // Количество каналов
        $channelCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM group_channels WHERE group_id = ?",
            [$this->groupId]
        )['count'];
        
        $stats = [
            'members_count' => (int)$memberCount,
            'routes_count' => (int)$routeCount,
            'danger_zones_count' => (int)$dangerZoneCount,
            'active_sos_count' => (int)$activeSosCount,
            'channels_count' => (int)$channelCount
        ];
        
        $this->logSuccess('/api/v1/groups/{id}/stats', 'GET');
        $this->success($stats, 'Статистика группы');
    }
}
