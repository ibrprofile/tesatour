<?php
/**
 * Модель туристической группы
 */
class Group
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Создание группы
     */
    public function create($data)
    {
        $inviteCode = $this->generateInviteCode();
        
        $groupId = $this->db->insert('groups', [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'invite_code' => $inviteCode,
            'owner_id' => $data['owner_id'],
            'status' => GROUP_STATUS_ACTIVE
        ]);
        
        // Добавляем владельца как участника
        $this->addMember($groupId, $data['owner_id'], ROLE_OWNER);
        
        return $groupId;
    }
    
    /**
     * Поиск группы по ID
     */
    public function findById($id)
    {
        return $this->db->fetchOne(
            "SELECT g.*, u.first_name as owner_first_name, u.last_name as owner_last_name
             FROM groups g
             JOIN users u ON g.owner_id = u.id
             WHERE g.id = ?",
            [$id]
        );
    }
    
    /**
     * Поиск группы по коду приглашения
     */
    public function findByInviteCode($code)
    {
        return $this->db->fetchOne(
            "SELECT * FROM groups WHERE invite_code = ? AND status = ?",
            [$code, GROUP_STATUS_ACTIVE]
        );
    }
    
    /**
     * Обновление группы
     */
    public function update($id, $data)
    {
        return $this->db->update('groups', $data, 'id = ?', [$id]);
    }
    
    /**
     * Закрытие группы
     */
    public function close($id)
    {
        return $this->update($id, [
            'status' => GROUP_STATUS_CLOSED,
            'closed_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Получение групп пользователя
     */
    public function getUserGroups($userId)
    {
        return $this->db->fetchAll(
            "SELECT g.*, gm.role, gm.joined_at,
                    (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as members_count
             FROM groups g
             JOIN group_members gm ON g.id = gm.group_id
             WHERE gm.user_id = ?
             ORDER BY g.status ASC, gm.joined_at DESC",
            [$userId]
        );
    }
    
    /**
     * Получение активных групп пользователя
     */
    public function getUserActiveGroups($userId)
    {
        return $this->db->fetchAll(
            "SELECT g.*, gm.role, gm.joined_at,
                    (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as members_count
             FROM groups g
             JOIN group_members gm ON g.id = gm.group_id
             WHERE gm.user_id = ? AND g.status = ?
             ORDER BY gm.joined_at DESC",
            [$userId, GROUP_STATUS_ACTIVE]
        );
    }
    
    /**
     * Добавление участника
     */
    public function addMember($groupId, $userId, $role = ROLE_MEMBER)
    {
        return $this->db->insert('group_members', [
            'group_id' => $groupId,
            'user_id' => $userId,
            'role' => $role
        ]);
    }
    
    /**
     * Удаление участника
     */
    public function removeMember($groupId, $userId)
    {
        return $this->db->delete('group_members', 'group_id = ? AND user_id = ?', [$groupId, $userId]);
    }
    
    /**
     * Получение участников группы
     */
    public function getMembers($groupId)
    {
        return $this->db->fetchAll(
            "SELECT u.*, gm.role, gm.joined_at
             FROM users u
             JOIN group_members gm ON u.id = gm.user_id
             WHERE gm.group_id = ?
             ORDER BY 
                CASE gm.role 
                    WHEN 'owner' THEN 1 
                    WHEN 'admin' THEN 2 
                    ELSE 3 
                END,
                gm.joined_at ASC",
            [$groupId]
        );
    }
    
    /**
     * Получение участника группы
     */
    public function getMember($groupId, $userId)
    {
        return $this->db->fetchOne(
            "SELECT u.*, gm.role, gm.joined_at
             FROM users u
             JOIN group_members gm ON u.id = gm.user_id
             WHERE gm.group_id = ? AND gm.user_id = ?",
            [$groupId, $userId]
        );
    }
    
    /**
     * Проверка членства в группе
     */
    public function isMember($groupId, $userId)
    {
        return $this->db->exists('group_members', 'group_id = ? AND user_id = ?', [$groupId, $userId]);
    }
    
    /**
     * Получение роли пользователя в группе
     */
    public function getUserRole($groupId, $userId)
    {
        $member = $this->db->fetchOne(
            "SELECT role FROM group_members WHERE group_id = ? AND user_id = ?",
            [$groupId, $userId]
        );
        
        return $member['role'] ?? null;
    }
    
    /**
     * Изменение роли участника
     */
    public function changeRole($groupId, $userId, $role)
    {
        return $this->db->update(
            'group_members',
            ['role' => $role],
            'group_id = ? AND user_id = ?',
            [$groupId, $userId]
        );
    }
    
    /**
     * Проверка права управления участником
     */
    public function canManageMember($groupId, $managerId, $targetUserId)
    {
        $managerRole = $this->getUserRole($groupId, $managerId);
        $targetRole = $this->getUserRole($groupId, $targetUserId);
        
        if (!$managerRole || !$targetRole) {
            return false;
        }
        
        // Владелец может управлять всеми
        if ($managerRole === ROLE_OWNER) {
            return $targetRole !== ROLE_OWNER;
        }
        
        // Админ может управлять только участниками
        if ($managerRole === ROLE_ADMIN) {
            return $targetRole === ROLE_MEMBER;
        }
        
        return false;
    }
    
    /**
     * Проверка права изменения роли
     */
    public function canChangeRole($groupId, $userId)
    {
        $role = $this->getUserRole($groupId, $userId);
        return $role === ROLE_OWNER;
    }
    
    /**
     * Проверка права редактирования группы
     */
    public function canEdit($groupId, $userId)
    {
        $role = $this->getUserRole($groupId, $userId);
        return in_array($role, [ROLE_OWNER, ROLE_ADMIN]);
    }
    
    /**
     * Проверка права закрытия группы
     */
    public function canClose($groupId, $userId)
    {
        $role = $this->getUserRole($groupId, $userId);
        return $role === ROLE_OWNER;
    }
    
    /**
     * Количество участников
     */
    public function getMembersCount($groupId)
    {
        return $this->db->count('group_members', 'group_id = ?', [$groupId]);
    }
    
    /**
     * Генерация пригласительного кода
     */
    private function generateInviteCode()
    {
        do {
            $code = bin2hex(random_bytes(8));
        } while ($this->db->exists('groups', 'invite_code = ?', [$code]));
        
        return $code;
    }
    
    /**
     * Обновление пригласительного кода
     */
    public function regenerateInviteCode($groupId)
    {
        $code = $this->generateInviteCode();
        $this->update($groupId, ['invite_code' => $code]);
        return $code;
    }
    
    /**
     * Получение пригласительной ссылки
     */
    public function getInviteUrl($inviteCode)
    {
        return APP_URL . '/invite/' . $inviteCode;
    }
    
    /**
     * Получение локаций участников группы
     */
    public function getMembersLocations($groupId)
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.first_name, u.last_name, u.avatar,
                    u.last_latitude, u.last_longitude, u.last_location_update,
                    gm.role
             FROM users u
             JOIN group_members gm ON u.id = gm.user_id
             WHERE gm.group_id = ? 
               AND u.last_latitude IS NOT NULL 
               AND u.last_longitude IS NOT NULL
             ORDER BY u.last_location_update DESC",
            [$groupId]
        );
    }
    
    /**
     * Форматирование названия роли
     */
    public function getRoleName($role)
    {
        if ($role === ROLE_OWNER) {
            return 'Владелец';
        } elseif ($role === ROLE_ADMIN) {
            return 'Администратор';
        } elseif ($role === ROLE_MEMBER) {
            return 'Участник';
        }
        return 'Неизвестно';
    }
    
    // === Черный список ===
    
    /**
     * Добавление пользователя в черный список
     */
    public function addToBlacklist($groupId, $userId, $bannedBy, $reason = null)
    {
        // Удаляем из группы, если там есть
        $this->removeMember($groupId, $userId);
        
        return $this->db->insert('group_blacklist', [
            'group_id' => $groupId,
            'user_id' => $userId,
            'banned_by' => $bannedBy,
            'reason' => $reason
        ]);
    }
    
    /**
     * Удаление из черного списка
     */
    public function removeFromBlacklist($groupId, $userId)
    {
        return $this->db->delete('group_blacklist', 'group_id = ? AND user_id = ?', [$groupId, $userId]);
    }
    
    /**
     * Проверка на наличие в черном списке
     */
    public function isBlacklisted($groupId, $userId)
    {
        return $this->db->exists('group_blacklist', 'group_id = ? AND user_id = ?', [$groupId, $userId]);
    }
    
    /**
     * Получение черного списка группы
     */
    public function getBlacklist($groupId)
    {
        return $this->db->fetchAll(
            "SELECT gb.*, 
                    u.first_name, u.last_name, u.avatar,
                    b.first_name as banned_by_first_name, b.last_name as banned_by_last_name
             FROM group_blacklist gb
             JOIN users u ON gb.user_id = u.id
             JOIN users b ON gb.banned_by = b.id
             WHERE gb.group_id = ?
             ORDER BY gb.id DESC",
            [$groupId]
        );
    }
    
    // === Заявки на вступление ===
    
    /**
     * Создание заявки на вступление
     */
    public function createJoinRequest($groupId, $userId)
    {
        // Проверяем черный список
        if ($this->isBlacklisted($groupId, $userId)) {
            return false;
        }
        
        return $this->db->insert('group_join_requests', [
            'group_id' => $groupId,
            'user_id' => $userId,
            'status' => 'pending'
        ]);
    }
    
    /**
     * Одобрение заявки
     */
    public function approveJoinRequest($requestId, $reviewedBy)
    {
        $request = $this->db->fetchOne('SELECT * FROM group_join_requests WHERE id = ?', [$requestId]);
        
        if (!$request || $request['status'] !== 'pending') {
            return false;
        }
        
        // Обновляем статус заявки
        $this->db->update('group_join_requests', [
            'status' => 'approved',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$requestId]);
        
        // Добавляем в группу
        $this->addMember($request['group_id'], $request['user_id'], ROLE_MEMBER);
        
        return true;
    }
    
    /**
     * Отклонение заявки
     */
    public function rejectJoinRequest($requestId, $reviewedBy)
    {
        return $this->db->update('group_join_requests', [
            'status' => 'rejected',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$requestId]);
    }
    
    /**
     * Получение ожидающих заявок группы
     */
    public function getPendingRequests($groupId)
    {
        return $this->db->fetchAll(
            "SELECT r.*, u.first_name, u.last_name, u.avatar
             FROM group_join_requests r
             JOIN users u ON r.user_id = u.id
             WHERE r.group_id = ? AND r.status = 'pending'
             ORDER BY r.created_at DESC",
            [$groupId]
        );
    }
    
    /**
     * Проверка наличия активной заявки
     */
    public function hasPendingRequest($groupId, $userId)
    {
        return $this->db->exists(
            'group_join_requests', 
            'group_id = ? AND user_id = ? AND status = ?', 
            [$groupId, $userId, 'pending']
        );
    }
    
    /**
     * Включение/выключение режима одобрения заявок
     */
    public function setRequireApproval($groupId, $requireApproval)
    {
        return $this->update($groupId, ['require_approval' => $requireApproval ? 1 : 0]);
    }
}
