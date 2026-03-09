<?php
/**
 * API контроллер для управления API ключами
 * Позволяет создавать, редактировать, удалять API ключи
 */
class ApiKeysController extends ApiBaseController
{
    private $apiKeyModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->apiKeyModel = new ApiKey();
    }
    
    /**
     * GET /api/v1/keys
     * Получение всех API ключей группы (только для owner)
     */
    public function list()
    {
        if (!$this->requireScope('groups:read')) return;
        
        // Проверяем, что это owner группы
        if (!$this->isGroupOwner()) {
            $this->error('Только владелец группы может просматривать API ключи', 403);
        }
        
        $keys = $this->apiKeyModel->getByGroupId($this->groupId);
        $this->logSuccess('/api/v1/keys', 'GET');
        $this->success($keys, 'Список API ключей получен');
    }
    
    /**
     * POST /api/v1/keys
     * Создание нового API ключа
     */
    public function create()
    {
        if (!$this->requireScope('groups:write')) return;
        
        // Только owner группы может создавать ключи
        if (!$this->isGroupOwner()) {
            $this->error('Только владелец группы может создавать API ключи', 403);
        }
        
        $data = $this->getPostData();
        
        // Валидируем данные
        if (!$this->validateRequired($data, ['name', 'scopes'])) {
            return;
        }
        
        $name = trim($data['name']);
        $scopes = (array)$data['scopes'];
        $expiresInDays = isset($data['expires_in_days']) ? (int)$data['expires_in_days'] : null;
        
        // Валидируем название
        if (strlen($name) < 3 || strlen($name) > 255) {
            $this->error('Название ключа должно быть от 3 до 255 символов', 400);
        }
        
        // Валидируем scopes
        if (empty($scopes)) {
            $this->error('Необходимо указать хотя бы одно разрешение', 400);
        }
        
        // Проверяем наличие всех scopes в reference
        if (!$this->validateScopes($scopes)) {
            $this->error('Некоторые разрешения недействительны', 400);
        }
        
        // Получаем ID текущего пользователя из сессии
        $userId = Session::getUserId();
        
        // Создаем ключ
        $result = $this->apiKeyModel->create($this->groupId, $userId, $name, $scopes, $expiresInDays);
        
        if (!$result) {
            $this->logError('/api/v1/keys', 'POST', 500, 'Ошибка при создании ключа');
            $this->error('Ошибка при создании API ключа', 500);
        }
        
        $this->logSuccess('/api/v1/keys', 'POST');
        $this->success($result, 'API ключ успешно создан', 201);
    }
    
    /**
     * GET /api/v1/keys/{id}
     * Получение информации о конкретном ключе
     */
    public function get()
    {
        if (!$this->requireScope('groups:read')) return;
        
        if (!$this->isGroupOwner()) {
            $this->error('Только владелец группы может просматривать API ключи', 403);
        }
        
        $keyId = $this->getRouteParam('id');
        if (!$keyId) {
            $this->error('ID ключа не указан', 400);
        }
        
        $key = $this->apiKeyModel->findById($keyId);
        if (!$this->checkResourceExists($key, 'API ключ')) return;
        
        if (!$this->checkGroupAccess($key['group_id'])) return;
        
        $this->logSuccess('/api/v1/keys/{id}', 'GET');
        $this->success($key, 'Информация о ключе');
    }
    
    /**
     * PUT /api/v1/keys/{id}
     * Обновление разрешений ключа
     */
    public function update()
    {
        if (!$this->requireScope('groups:write')) return;
        
        if (!$this->isGroupOwner()) {
            $this->error('Только владелец группы может редактировать API ключи', 403);
        }
        
        $keyId = $this->getRouteParam('id');
        if (!$keyId) {
            $this->error('ID ключа не указан', 400);
        }
        
        $key = $this->apiKeyModel->findById($keyId);
        if (!$this->checkResourceExists($key, 'API ключ')) return;
        
        if (!$this->checkGroupAccess($key['group_id'])) return;
        
        $data = $this->getPostData();
        
        // Обновляем scopes если они предоставлены
        if (isset($data['scopes'])) {
            $scopes = (array)$data['scopes'];
            
            if (empty($scopes)) {
                $this->error('Необходимо указать хотя бы одно разрешение', 400);
            }
            
            if (!$this->validateScopes($scopes)) {
                $this->error('Некоторые разрешения недействительны', 400);
            }
            
            $userId = Session::getUserId();
            if (!$this->apiKeyModel->updateScopes($keyId, $scopes, $userId)) {
                $this->error('Ошибка при обновлении разрешений', 500);
            }
        }
        
        // Обновляем статус активности если он предоставлен
        if (isset($data['is_active'])) {
            $isActive = (bool)$data['is_active'];
            $userId = Session::getUserId();
            
            if ($isActive) {
                $this->apiKeyModel->activate($keyId, $userId);
            } else {
                $this->apiKeyModel->deactivate($keyId, $userId);
            }
        }
        
        $updatedKey = $this->apiKeyModel->findById($keyId);
        $this->logSuccess('/api/v1/keys/{id}', 'PUT');
        $this->success($updatedKey, 'API ключ успешно обновлен');
    }
    
    /**
     * DELETE /api/v1/keys/{id}
     * Удаление API ключа
     */
    public function delete()
    {
        if (!$this->requireScope('groups:write')) return;
        
        if (!$this->isGroupOwner()) {
            $this->error('Только владелец группы может удалять API ключи', 403);
        }
        
        $keyId = $this->getRouteParam('id');
        if (!$keyId) {
            $this->error('ID ключа не указан', 400);
        }
        
        $key = $this->apiKeyModel->findById($keyId);
        if (!$this->checkResourceExists($key, 'API ключ')) return;
        
        if (!$this->checkGroupAccess($key['group_id'])) return;
        
        $userId = Session::getUserId();
        
        if (!$this->apiKeyModel->delete($keyId, $userId)) {
            $this->error('Ошибка при удалении API ключа', 500);
        }
        
        $this->logSuccess('/api/v1/keys/{id}', 'DELETE');
        $this->success(null, 'API ключ успешно удален');
    }
    
    /**
     * GET /api/v1/keys/{id}/logs
     * Получение логов использования ключа
     */
    public function getLogs()
    {
        if (!$this->requireScope('logs:read')) return;
        
        if (!$this->isGroupOwner()) {
            $this->error('Только владелец группы может просматривать логи', 403);
        }
        
        $keyId = $this->getRouteParam('id');
        if (!$keyId) {
            $this->error('ID ключа не указан', 400);
        }
        
        $key = $this->apiKeyModel->findById($keyId);
        if (!$this->checkResourceExists($key, 'API ключ')) return;
        
        if (!$this->checkGroupAccess($key['group_id'])) return;
        
        $limit = min((int)($_GET['limit'] ?? 100), 1000);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $logs = $this->db->fetchAll(
            "SELECT * FROM api_logs WHERE api_key_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$keyId, $limit, $offset]
        );
        
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM api_logs WHERE api_key_id = ?",
            [$keyId]
        )['count'];
        
        $this->logSuccess('/api/v1/keys/{id}/logs', 'GET');
        $this->success([
            'logs' => $logs,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ], 'Логи получены');
    }
    
    /**
     * Проверка, является ли текущий пользователь owner группы
     * @return bool
     */
    private function isGroupOwner()
    {
        $userId = Session::getUserId();
        
        $result = $this->db->fetchOne(
            "SELECT gm.id FROM group_members gm 
             WHERE gm.group_id = ? AND gm.user_id = ? AND gm.role = 'owner'",
            [$this->groupId, $userId]
        );
        
        return $result !== null;
    }
    
    /**
     * Валидация scopes
     * @param array $scopes
     * @return bool
     */
    private function validateScopes($scopes)
    {
        // Получаем все возможные scopes
        $validScopes = $this->db->fetchAll(
            "SELECT scope FROM api_scopes_reference"
        );
        
        $validScopesList = array_column($validScopes, 'scope');
        
        foreach ($scopes as $scope) {
            if (!in_array($scope, $validScopesList)) {
                return false;
            }
        }
        
        return true;
    }
}
