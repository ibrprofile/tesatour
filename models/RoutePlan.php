<?php
/**
 * Модель план-листов маршрутов
 */
class RoutePlan
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Создание план-листа
     */
    public function create($data)
    {
        return $this->db->insert('route_plans', [
            'group_id' => $data['group_id'],
            'created_by' => $data['created_by'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_active' => 1
        ]);
    }
    
    /**
     * Поиск по ID
     */
    public function findById($id)
    {
        return $this->db->fetchOne(
            "SELECT rp.*, 
                    u.first_name as creator_first_name, u.last_name as creator_last_name,
                    g.name as group_name
             FROM route_plans rp
             JOIN users u ON rp.created_by = u.id
             JOIN groups g ON rp.group_id = g.id
             WHERE rp.id = ?",
            [$id]
        );
    }
    
    /**
     * Получение план-листов группы
     */
    public function getByGroup($groupId)
    {
        return $this->db->fetchAll(
            "SELECT rp.*, 
                    u.first_name as creator_first_name, u.last_name as creator_last_name,
                    (SELECT COUNT(*) FROM route_points WHERE route_plan_id = rp.id) as points_count
             FROM route_plans rp
             JOIN users u ON rp.created_by = u.id
             WHERE rp.group_id = ?
             ORDER BY rp.is_active DESC, rp.created_at DESC",
            [$groupId]
        );
    }
    
    /**
     * Получение активного план-листа группы
     */
    public function getActiveByGroup($groupId)
    {
        return $this->db->fetchOne(
            "SELECT rp.*, 
                    u.first_name as creator_first_name, u.last_name as creator_last_name
             FROM route_plans rp
             JOIN users u ON rp.created_by = u.id
             WHERE rp.group_id = ? AND rp.is_active = 1
             ORDER BY rp.created_at DESC LIMIT 1",
            [$groupId]
        );
    }
    
    /**
     * Обновление план-листа
     */
    public function update($id, $data)
    {
        return $this->db->update('route_plans', $data, 'id = ?', [$id]);
    }
    
    /**
     * Удаление план-листа
     */
    public function delete($id)
    {
        return $this->db->delete('route_plans', 'id = ?', [$id]);
    }
    
    /**
     * Деактивация план-листа
     */
    public function deactivate($id)
    {
        return $this->update($id, ['is_active' => 0]);
    }
    
    /**
     * Активация план-листа (деактивирует остальные в группе)
     */
    public function activate($id)
    {
        $plan = $this->findById($id);
        
        if ($plan) {
            // Деактивируем все в группе
            $this->db->update(
                'route_plans',
                ['is_active' => 0],
                'group_id = ?',
                [$plan['group_id']]
            );
            
            // Активируем нужный
            $this->update($id, ['is_active' => 1]);
        }
    }
    
    /**
     * Добавление точки маршрута
     */
    public function addPoint($routePlanId, $data)
    {
        // Получаем максимальный order_index
        $maxOrder = $this->db->fetchColumn(
            "SELECT MAX(order_index) FROM route_points WHERE route_plan_id = ?",
            [$routePlanId]
        );
        
        return $this->db->insert('route_points', [
            'route_plan_id' => $routePlanId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'order_index' => ($maxOrder ?? 0) + 1
        ]);
    }
    
    /**
     * Обновление точки маршрута
     */
    public function updatePoint($pointId, $data)
    {
        return $this->db->update('route_points', $data, 'id = ?', [$pointId]);
    }
    
    /**
     * Удаление точки маршрута
     */
    public function deletePoint($pointId)
    {
        return $this->db->delete('route_points', 'id = ?', [$pointId]);
    }
    
    /**
     * Получение точек маршрута
     */
    public function getPoints($routePlanId)
    {
        return $this->db->fetchAll(
            "SELECT * FROM route_points 
             WHERE route_plan_id = ? 
             ORDER BY order_index ASC",
            [$routePlanId]
        );
    }
    
    /**
     * Получение точки по ID
     */
    public function findPointById($pointId)
    {
        return $this->db->fetchOne(
            "SELECT rp.*, rt.group_id
             FROM route_points rp
             JOIN route_plans rt ON rp.route_plan_id = rt.id
             WHERE rp.id = ?",
            [$pointId]
        );
    }
    
    /**
     * Количество точек в маршруте
     */
    public function getPointsCount($routePlanId)
    {
        return $this->db->count('route_points', 'route_plan_id = ?', [$routePlanId]);
    }
    
    /**
     * Отметка точки как пройденной
     */
    public function markPointAsCompleted($pointId, $userId)
    {
        return $this->db->update('route_points', [
            'is_completed' => 1,
            'completed_by' => $userId,
            'completed_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$pointId]);
    }
    
    /**
     * Снятие отметки с точки
     */
    public function unmarkPointAsCompleted($pointId)
    {
        return $this->db->update('route_points', [
            'is_completed' => 0,
            'completed_by' => null,
            'completed_at' => null
        ], 'id = ?', [$pointId]);
    }
    
    /**
     * Получение точек с информацией о завершении
     */
    public function getPointsWithCompletion($routePlanId)
    {
        return $this->db->fetchAll(
            "SELECT rp.*, 
                    u.first_name as completed_by_first_name, 
                    u.last_name as completed_by_last_name
             FROM route_points rp
             LEFT JOIN users u ON rp.completed_by = u.id
             WHERE rp.route_plan_id = ? 
             ORDER BY rp.order_index ASC",
            [$routePlanId]
        );
    }
}
