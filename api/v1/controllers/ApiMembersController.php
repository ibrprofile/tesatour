<?php
/**
 * API контроллер для работы с членами группы
 */
class ApiMembersController extends ApiBaseController
{
    /**
     * GET /api/v1/groups/{id}/members
     * Получение списка членов группы
     */
    public function list()
    {
        if (!$this->requireScope('members:read')) return;
        
        $limit = min((int)($_GET['limit'] ?? 50), 500);
        $offset = (int)($_GET['offset'] ?? 0);
        
        // Получаем членов группы
        $members = $this->db->fetchAll(
            "SELECT gm.id, gm.role, gm.joined_at, 
                    u.id as user_id, u.first_name, u.last_name, u.email, u.avatar,
                    u.last_latitude, u.last_longitude, u.last_location_update
             FROM group_members gm
             JOIN users u ON gm.user_id = u.id
             WHERE gm.group_id = ?
             ORDER BY gm.joined_at DESC
             LIMIT ? OFFSET ?",
            [$this->groupId, $limit, $offset]
        );
        
        // Форматируем ответ
        $formattedMembers = [];
        foreach ($members as $member) {
            $formattedMembers[] = [
                'id' => (int)$member['user_id'],
                'first_name' => $member['first_name'],
                'last_name' => $member['last_name'],
                'email' => $member['email'],
                'avatar' => $member['avatar'],
                'role' => $member['role'],
                'joined_at' => $member['joined_at'],
                'last_location' => $member['last_latitude'] && $member['last_longitude'] ? [
                    'latitude' => (float)$member['last_latitude'],
                    'longitude' => (float)$member['last_longitude'],
                    'updated_at' => $member['last_location_update']
                ] : null
            ];
        }
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM group_members WHERE group_id = ?",
            [$this->groupId]
        )['count'];
        
        $this->logSuccess('/api/v1/groups/{id}/members', 'GET');
        $this->success([
            'members' => $formattedMembers,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ], 'Список членов группы');
    }
    
    /**
     * GET /api/v1/groups/{id}/members/{userId}
     * Получение информации о конкретном члене
     */
    public function get()
    {
        if (!$this->requireScope('members:read')) return;
        
        $userId = $this->getRouteParam('userId');
        if (!$userId) {
            $this->error('ID пользователя не указан', 400);
        }
        
        $member = $this->db->fetchOne(
            "SELECT gm.id, gm.role, gm.joined_at, 
                    u.id as user_id, u.first_name, u.last_name, u.email, u.avatar,
                    u.last_latitude, u.last_longitude, u.last_location_update
             FROM group_members gm
             JOIN users u ON gm.user_id = u.id
             WHERE gm.group_id = ? AND gm.user_id = ?",
            [$this->groupId, $userId]
        );
        
        if (!$this->checkResourceExists($member, 'Член группы')) return;
        
        $response = [
            'id' => (int)$member['user_id'],
            'first_name' => $member['first_name'],
            'last_name' => $member['last_name'],
            'email' => $member['email'],
            'avatar' => $member['avatar'],
            'role' => $member['role'],
            'joined_at' => $member['joined_at'],
            'last_location' => $member['last_latitude'] && $member['last_longitude'] ? [
                'latitude' => (float)$member['last_latitude'],
                'longitude' => (float)$member['last_longitude'],
                'updated_at' => $member['last_location_update']
            ] : null
        ];
        
        $this->logSuccess('/api/v1/groups/{id}/members/{userId}', 'GET');
        $this->success($response, 'Информация о члене группы');
    }
    
    /**
     * POST /api/v1/groups/{id}/members/{userId}/role
     * Изменение роли члена (только для owner)
     */
    public function updateRole()
    {
        if (!$this->requireScope('members:write')) return;
        
        $userId = $this->getRouteParam('userId');
        if (!$userId) {
            $this->error('ID пользователя не указан', 400);
        }
        
        // Проверяем что это owner
        if (!$this->isGroupOwner()) {
            $this->error('Только владелец группы может изменять роли', 403);
        }
        
        $data = $this->getPostData();
        if (!isset($data['role'])) {
            $this->error('Роль не указана', 400);
        }
        
        $role = $data['role'];
        if (!in_array($role, ['owner', 'admin', 'member'])) {
            $this->error('Недействительная роль', 400);
        }
        
        // Проверяем, что member существит в группе
        $member = $this->db->fetchOne(
            "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?",
            [$this->groupId, $userId]
        );
        
        if (!$member) {
            $this->error('Член группы не найден', 404);
        }
        
        // Обновляем роль
        $this->db->update('group_members',
            ['role' => $role],
            'group_id = ? AND user_id = ?',
            [$this->groupId, $userId]
        );
        
        $this->logSuccess('/api/v1/groups/{id}/members/{userId}/role', 'POST');
        $this->success(['role' => $role], 'Роль успешно изменена');
    }
    
    /**
     * DELETE /api/v1/groups/{id}/members/{userId}
     * Удаление члена из группы (только для owner)
     */
    public function remove()
    {
        if (!$this->requireScope('members:write')) return;
        
        $userId = $this->getRouteParam('userId');
        if (!$userId) {
            $this->error('ID пользователя не указан', 400);
        }
        
        // Проверяем что это owner
        if (!$this->isGroupOwner()) {
            $this->error('Только владелец группы может удалять членов', 403);
        }
        
        // Проверяем, что member существит в группе
        $member = $this->db->fetchOne(
            "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?",
            [$this->groupId, $userId]
        );
        
        if (!$member) {
            $this->error('Член группы не найден', 404);
        }
        
        // Нельзя удалить owner группы
        if ($member['role'] === 'owner') {
            $this->error('Нельзя удалить владельца группы', 400);
        }
        
        // Удаляем
        $this->db->delete('group_members',
            'group_id = ? AND user_id = ?',
            [$this->groupId, $userId]
        );
        
        $this->logSuccess('/api/v1/groups/{id}/members/{userId}', 'DELETE');
        $this->success(null, 'Член успешно удален из группы');
    }
    
    /**
     * GET /api/v1/groups/{id}/members/{userId}/location
     * Получение истории местоположения члена
     */
    public function getLocationHistory()
    {
        if (!$this->requireScope('locations:read')) return;
        
        $userId = $this->getRouteParam('userId');
        if (!$userId) {
            $this->error('ID пользователя не указан', 400);
        }
        
        // Проверяем что пользователь в группе
        $member = $this->db->fetchOne(
            "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?",
            [$this->groupId, $userId]
        );
        
        if (!$member) {
            $this->error('Член группы не найден', 404);
        }
        
        $limit = min((int)($_GET['limit'] ?? 100), 1000);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $locations = $this->db->fetchAll(
            "SELECT id, latitude, longitude, accuracy, recorded_at 
             FROM location_history 
             WHERE user_id = ?
             ORDER BY recorded_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
        
        // Форматируем координаты
        foreach ($locations as &$loc) {
            $loc['latitude'] = (float)$loc['latitude'];
            $loc['longitude'] = (float)$loc['longitude'];
            $loc['accuracy'] = $loc['accuracy'] ? (float)$loc['accuracy'] : null;
        }
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM location_history WHERE user_id = ?",
            [$userId]
        )['count'];
        
        $this->logSuccess('/api/v1/groups/{id}/members/{userId}/location', 'GET');
        $this->success([
            'locations' => $locations,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ], 'История местоположения');
    }
    
    /**
     * Проверка что текущий пользователь - owner группы
     * @return bool
     */
    private function isGroupOwner()
    {
        $currentUserId = Session::getUserId();
        
        $result = $this->db->fetchOne(
            "SELECT * FROM group_members 
             WHERE group_id = ? AND user_id = ? AND role = 'owner'",
            [$this->groupId, $currentUserId]
        );
        
        return $result !== null;
    }
}
