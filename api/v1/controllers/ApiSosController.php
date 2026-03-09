<?php
/**
 * API контроллер для работы с SOS вызовами
 */
class ApiSosController extends ApiBaseController
{
    /**
     * GET /api/v1/groups/{id}/sos
     * Получение SOS вызовов группы
     */
    public function list()
    {
        if (!$this->requireScope('sos:read')) return;
        
        // Фильтры
        $status = $_GET['status'] ?? null; // active or resolved
        $limit = min((int)($_GET['limit'] ?? 50), 500);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $query = "SELECT sa.*, u.first_name, u.last_name, u.email, 
                         ru.first_name as resolved_by_first_name, ru.last_name as resolved_by_last_name
                  FROM sos_alerts sa
                  JOIN users u ON sa.user_id = u.id
                  LEFT JOIN users ru ON sa.resolved_by = ru.id
                  WHERE sa.group_id = ?";
        $params = [$this->groupId];
        
        if ($status && in_array($status, ['active', 'resolved'])) {
            $query .= " AND sa.status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY sa.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $alerts = $this->db->fetchAll($query, $params);
        
        // Форматируем
        foreach ($alerts as &$alert) {
            $alert['latitude'] = (float)$alert['latitude'];
            $alert['longitude'] = (float)$alert['longitude'];
        }
        
        // Получаем общее количество
        $countQuery = "SELECT COUNT(*) as count FROM sos_alerts WHERE group_id = ?";
        $countParams = [$this->groupId];
        
        if ($status && in_array($status, ['active', 'resolved'])) {
            $countQuery .= " AND status = ?";
            $countParams[] = $status;
        }
        
        $total = $this->db->fetchOne($countQuery, $countParams)['count'];
        
        $this->logSuccess('/api/v1/groups/{id}/sos', 'GET');
        $this->success([
            'alerts' => $alerts,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ], 'Список SOS вызовов');
    }
    
    /**
     * POST /api/v1/groups/{id}/sos
     * Создание SOS вызова
     */
    public function create()
    {
        if (!$this->requireScope('sos:write')) return;
        
        $data = $this->getPostData();
        
        if (!$this->validateRequired($data, ['latitude', 'longitude'])) {
            return;
        }
        
        $latitude = (float)$data['latitude'];
        $longitude = (float)$data['longitude'];
        $comment = trim($data['comment'] ?? '');
        
        // Валидируем координаты
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $this->error('Неверные координаты', 400);
        }
        
        $userId = Session::getUserId();
        
        // Проверяем что использует он есть в группе
        $member = $this->db->fetchOne(
            "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?",
            [$this->groupId, $userId]
        );
        
        if (!$member) {
            $this->error('Вы не состоите в этой группе', 403);
        }
        
        $sosId = $this->db->insert('sos_alerts', [
            'group_id' => $this->groupId,
            'user_id' => $userId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'comment' => $comment,
            'status' => 'active'
        ]);
        
        if (!$sosId) {
            $this->logError('/api/v1/groups/{id}/sos', 'POST', 500, 'Ошибка при создании');
            $this->error('Ошибка при создании SOS вызова', 500);
        }
        
        $sos = $this->getSosById($sosId);
        
        $this->logSuccess('/api/v1/groups/{id}/sos', 'POST');
        $this->success($sos, 'SOS вызов успешно создан', 201);
    }
    
    /**
     * GET /api/v1/sos/{id}
     * Получение информации об SOS вызове
     */
    public function get()
    {
        if (!$this->requireScope('sos:read')) return;
        
        $sosId = $this->getRouteParam('id');
        if (!$sosId) {
            $this->error('ID вызова не указан', 400);
        }
        
        $sos = $this->getSosById($sosId);
        
        if (!$sos) {
            $this->error('SOS вызов не найден', 404);
        }
        
        if ($sos['group_id'] != $this->groupId) {
            $this->error('Доступ запрещен', 403);
        }
        
        $this->logSuccess('/api/v1/sos/{id}', 'GET');
        $this->success($sos, 'Информация об SOS вызове');
    }
    
    /**
     * POST /api/v1/sos/{id}/resolve
     * Разрешение SOS вызова (отметить как разрешенный)
     */
    public function resolve()
    {
        if (!$this->requireScope('sos:write')) return;
        
        $sosId = $this->getRouteParam('id');
        if (!$sosId) {
            $this->error('ID вызова не указан', 400);
        }
        
        $sos = $this->db->fetchOne(
            "SELECT * FROM sos_alerts WHERE id = ? AND group_id = ?",
            [$sosId, $this->groupId]
        );
        
        if (!$sos) {
            $this->error('SOS вызов не найден', 404);
        }
        
        if ($sos['status'] === 'resolved') {
            $this->error('Этот вызов уже был разрешен', 400);
        }
        
        $data = $this->getPostData();
        $resolutionComment = trim($data['comment'] ?? '');
        
        $userId = Session::getUserId();
        
        // Обновляем статус
        $this->db->update('sos_alerts',
            [
                'status' => 'resolved',
                'resolved_by' => $userId,
                'resolved_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$sosId]
        );
        
        $updated = $this->getSosById($sosId);
        
        $this->logSuccess('/api/v1/sos/{id}/resolve', 'POST');
        $this->success($updated, 'SOS вызов успешно разрешен');
    }
    
    /**
     * Получение SOS вызова по ID с форматированием
     * @param int $sosId
     * @return array|null
     */
    private function getSosById($sosId)
    {
        $sos = $this->db->fetchOne(
            "SELECT sa.*, u.first_name, u.last_name, u.email,
                    ru.first_name as resolved_by_first_name, ru.last_name as resolved_by_last_name
             FROM sos_alerts sa
             JOIN users u ON sa.user_id = u.id
             LEFT JOIN users ru ON sa.resolved_by = ru.id
             WHERE sa.id = ?",
            [$sosId]
        );
        
        if (!$sos) {
            return null;
        }
        
        $sos['latitude'] = (float)$sos['latitude'];
        $sos['longitude'] = (float)$sos['longitude'];
        
        return $sos;
    }
}
