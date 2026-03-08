<?php
/**
 * Модель SOS-вызовов
 */
class SosAlert
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Создание SOS-вызова
     */
    public function create($data)
    {
        return $this->db->insert('sos_alerts', [
            'user_id' => $data['user_id'],
            'group_id' => $data['group_id'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'comment' => $data['comment'] ?? null,
            'status' => SOS_STATUS_ACTIVE
        ]);
    }
    
    /**
     * Поиск по ID
     */
    public function findById($id)
    {
        return $this->db->fetchOne(
            "SELECT s.*, 
                    u.first_name, u.last_name, u.avatar,
                    g.name as group_name
             FROM sos_alerts s
             JOIN users u ON s.user_id = u.id
             JOIN groups g ON s.group_id = g.id
             WHERE s.id = ?",
            [$id]
        );
    }
    
    /**
     * Получение активных SOS в группе
     */
    public function getActiveByGroup($groupId)
    {
        return $this->db->fetchAll(
            "SELECT s.*, 
                    u.first_name, u.last_name, u.avatar
             FROM sos_alerts s
             JOIN users u ON s.user_id = u.id
             WHERE s.group_id = ? AND s.status = ?
             ORDER BY s.created_at DESC",
            [$groupId, SOS_STATUS_ACTIVE]
        );
    }
    
    /**
     * Получение всех SOS в группе
     */
    public function getAllByGroup($groupId, $limit = 20)
    {
        return $this->db->fetchAll(
            "SELECT s.*, 
                    u.first_name, u.last_name, u.avatar,
                    ru.first_name as resolved_by_first_name, ru.last_name as resolved_by_last_name
             FROM sos_alerts s
             JOIN users u ON s.user_id = u.id
             LEFT JOIN users ru ON s.resolved_by = ru.id
             WHERE s.group_id = ?
             ORDER BY s.created_at DESC
             LIMIT ?",
            [$groupId, $limit]
        );
    }
    
    /**
     * Получение последнего активного SOS пользователя
     */
    public function getLastActiveByUser($userId)
    {
        return $this->db->fetchOne(
            "SELECT * FROM sos_alerts 
             WHERE user_id = ? AND status = ?
             ORDER BY created_at DESC LIMIT 1",
            [$userId, SOS_STATUS_ACTIVE]
        );
    }
    
    /**
     * Разрешение (закрытие) SOS
     */
    public function resolve($id, $resolvedBy)
    {
        return $this->db->update('sos_alerts', [
            'status' => SOS_STATUS_RESOLVED,
            'resolved_by' => $resolvedBy,
            'resolved_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
    }
    
    /**
     * Проверка наличия активного SOS у пользователя
     */
    public function hasActiveSos($userId, $groupId)
    {
        return $this->db->exists(
            'sos_alerts',
            'user_id = ? AND group_id = ? AND status = ?',
            [$userId, $groupId, SOS_STATUS_ACTIVE]
        );
    }
    
    /**
     * Получение данных для отправки уведомления
     */
    public function getSosNotificationData($sosId)
    {
        $sos = $this->findById($sosId);
        
        if (!$sos) {
            return null;
        }
        
        $userModel = new User();
        
        return [
            'id' => $sos['id'],
            'user_name' => $userModel->getFullName($sos),
            'group_name' => $sos['group_name'],
            'latitude' => $sos['latitude'],
            'longitude' => $sos['longitude'],
            'comment' => $sos['comment'],
            'created_at' => $sos['created_at']
        ];
    }
    
    /**
     * Количество активных SOS в группе
     */
    public function countActiveByGroup($groupId)
    {
        return $this->db->count(
            'sos_alerts',
            'group_id = ? AND status = ?',
            [$groupId, SOS_STATUS_ACTIVE]
        );
    }
}
