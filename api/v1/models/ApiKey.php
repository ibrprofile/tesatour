<?php
/**
 * Модель для работы с API ключами
 * Управление созданием, валидацией и проверкой API токенов
 */
class ApiKey
{
    private $db;
    private $tokenPrefix = 'teso_'; // Префикс для токенов
    private $tokenLength = 32; // Длина токена после префикса
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Создание нового API ключа для группы
     * @param int $groupId
     * @param int $userId - кто создает ключ (должен быть owner группы)
     * @param string $keyName - название ключа
     * @param array $scopes - массив разрешений
     * @param int|null $expiresInDays - через сколько дней истекает (null = никогда)
     * @return array|false
     */
    public function create($groupId, $userId, $keyName, $scopes = [], $expiresInDays = null)
    {
        // Проверяем, что пользователь - владелец группы
        if (!$this->isGroupOwner($groupId, $userId)) {
            return false;
        }
        
        // Проверяем подписку владельца группы
        if (!$this->hasRequiredSubscription($groupId)) {
            return false;
        }
        
        // Валидируем данные
        if (empty($keyName) || strlen($keyName) < 3 || strlen($keyName) > 255) {
            return false;
        }
        
        // Генерируем токен
        $token = $this->generateToken();
        $tokenPrefix = $this->tokenPrefix . substr(bin2hex(random_bytes(10)), 0, 10);
        $tokenHash = hash('sha256', $token);
        
        // Вычисляем дату истечения
        $expiresAt = null;
        if ($expiresInDays && $expiresInDays > 0) {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiresInDays days"));
        }
        
        // Вставляем в БД
        $keyId = $this->db->insert('api_keys', [
            'group_id' => $groupId,
            'name' => $keyName,
            'token' => $tokenHash,
            'token_prefix' => $tokenPrefix,
            'is_active' => 1,
            'created_by' => $userId,
            'expires_at' => $expiresAt,
        ]);
        
        if (!$keyId) {
            return false;
        }
        
        // Добавляем scopes
        if (!empty($scopes)) {
            $this->addScopes($keyId, $scopes);
        }
        
        return [
            'id' => $keyId,
            'token' => $tokenPrefix . '_' . $token,
            'name' => $keyName,
            'group_id' => $groupId,
            'scopes' => $scopes,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expiresAt,
            'is_active' => true
        ];
    }
    
    /**
     * Генерирование случайного токена
     * @return string
     */
    private function generateToken()
    {
        return bin2hex(random_bytes($this->tokenLength));
    }
    
    /**
     * Проверка и получение ключа по токену
     * @param string $token - полный токен (например: teso_xxxxx_yyyyyy)
     * @return array|false
     */
    public function validateToken($token)
    {
        if (!$this->isValidTokenFormat($token)) {
            return false;
        }
        
        // Разбираем токен
        list($prefix, $suffix) = explode('_', $token, 2);
        $tokenHash = hash('sha256', $suffix);
        
        // Ищем в БД
        $apiKey = $this->db->fetchOne(
            "SELECT ak.*, g.owner_id FROM api_keys ak 
             JOIN groups g ON ak.group_id = g.id 
             WHERE ak.token = ? AND ak.is_active = 1",
            [$tokenHash]
        );
        
        if (!$apiKey) {
            return false;
        }
        
        // Проверяем, не истек ли ключ
        if ($apiKey['expires_at'] && strtotime($apiKey['expires_at']) < time()) {
            return false;
        }
        
        // Обновляем время последнего использования
        $this->updateLastUsed($apiKey['id']);
        
        // Получаем scopes
        $scopes = $this->getScopes($apiKey['id']);
        
        return array_merge($apiKey, ['scopes' => $scopes]);
    }
    
    /**
     * Получение ключей для группы
     * @param int $groupId
     * @return array
     */
    public function getByGroupId($groupId)
    {
        $keys = $this->db->fetchAll(
            "SELECT ak.id, ak.name, ak.token_prefix, ak.is_active, 
                    ak.created_by, ak.last_used_at, ak.expires_at, ak.created_at, ak.updated_at,
                    u.first_name, u.last_name
             FROM api_keys ak
             LEFT JOIN users u ON ak.created_by = u.id
             WHERE ak.group_id = ?
             ORDER BY ak.created_at DESC",
            [$groupId]
        );
        
        // Добавляем scopes для каждого ключа
        foreach ($keys as $key => $item) {
            $keys[$key]['scopes'] = $this->getScopes($item['id']);
        }
        
        return $keys;
    }
    
    /**
     * Получение одного ключа по ID
     * @param int $keyId
     * @return array|false
     */
    public function findById($keyId)
    {
        $key = $this->db->fetchOne(
            "SELECT * FROM api_keys WHERE id = ?",
            [$keyId]
        );
        
        if (!$key) {
            return false;
        }
        
        $key['scopes'] = $this->getScopes($keyId);
        return $key;
    }
    
    /**
     * Добавление разрешений (scopes) к ключу
     * @param int $keyId
     * @param array $scopes
     * @return bool
     */
    public function addScopes($keyId, $scopes)
    {
        if (empty($scopes)) {
            return true;
        }
        
        try {
            foreach ($scopes as $scope) {
                $this->db->insert('api_key_scopes', [
                    'api_key_id' => $keyId,
                    'scope' => $scope
                ]);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Получение разрешений для ключа
     * @param int $keyId
     * @return array
     */
    public function getScopes($keyId)
    {
        $result = $this->db->fetchAll(
            "SELECT scope FROM api_key_scopes WHERE api_key_id = ? ORDER BY scope",
            [$keyId]
        );
        
        return array_column($result, 'scope');
    }
    
    /**
     * Удаление ключа
     * @param int $keyId
     * @param int $userId - проверяем, что удаляет owner группы
     * @return bool
     */
    public function delete($keyId, $userId)
    {
        $key = $this->findById($keyId);
        if (!$key) {
            return false;
        }
        
        // Проверяем права
        if (!$this->isGroupOwner($key['group_id'], $userId)) {
            return false;
        }
        
        return $this->db->delete('api_keys', 'id = ?', [$keyId]);
    }
    
    /**
     * Деактивация ключа (soft delete)
     * @param int $keyId
     * @param int $userId
     * @return bool
     */
    public function deactivate($keyId, $userId)
    {
        $key = $this->findById($keyId);
        if (!$key) {
            return false;
        }
        
        if (!$this->isGroupOwner($key['group_id'], $userId)) {
            return false;
        }
        
        return $this->db->update('api_keys', 
            ['is_active' => 0],
            'id = ?',
            [$keyId]
        );
    }
    
    /**
     * Активация ключа
     * @param int $keyId
     * @param int $userId
     * @return bool
     */
    public function activate($keyId, $userId)
    {
        $key = $this->findById($keyId);
        if (!$key) {
            return false;
        }
        
        if (!$this->isGroupOwner($key['group_id'], $userId)) {
            return false;
        }
        
        return $this->db->update('api_keys',
            ['is_active' => 1],
            'id = ?',
            [$keyId]
        );
    }
    
    /**
     * Обновление scopes ключа
     * @param int $keyId
     * @param array $scopes
     * @param int $userId
     * @return bool
     */
    public function updateScopes($keyId, $scopes, $userId)
    {
        $key = $this->findById($keyId);
        if (!$key) {
            return false;
        }
        
        if (!$this->isGroupOwner($key['group_id'], $userId)) {
            return false;
        }
        
        // Удаляем старые
        $this->db->delete('api_key_scopes', 'api_key_id = ?', [$keyId]);
        
        // Добавляем новые
        return $this->addScopes($keyId, $scopes);
    }
    
    /**
     * Проверка, имеет ли пользователь разрешение в рамках API ключа
     * @param array $apiKey - данные ключа из validateToken
     * @param string $requiredScope - требуемое разрешение (например: members:read)
     * @return bool
     */
    public function hasScope($apiKey, $requiredScope)
    {
        // Полный доступ
        if (in_array('*', $apiKey['scopes'])) {
            return true;
        }
        
        // Проверяем конкретное разрешение
        return in_array($requiredScope, $apiKey['scopes']);
    }
    
    /**
     * Логирование API запроса
     * @param int|null $apiKeyId
     * @param string $endpoint
     * @param string $method
     * @param int $statusCode
     * @param int $responseTimeMs
     * @param string|null $errorMessage
     * @return bool
     */
    public function logRequest($apiKeyId, $endpoint, $method, $statusCode, $responseTimeMs, $errorMessage = null, $requestSize = 0, $responseSize = 0)
    {
        return $this->db->insert('api_logs', [
            'api_key_id' => $apiKeyId,
            'endpoint' => $endpoint,
            'method' => $method,
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'request_size' => $requestSize,
            'response_size' => $responseSize,
            'ip_address' => $this->getClientIp(),
            'error_message' => $errorMessage
        ]);
    }
    
    /**
     * Проверка rate limiting
     * @param int $apiKeyId
     * @param int $maxRequests - максимум запросов в минуту
     * @return bool
     */
    public function checkRateLimit($apiKeyId, $maxRequests = 60)
    {
        $now = time();
        $resetAt = date('Y-m-d H:i:00', $now); // Округляем до минуты
        
        $record = $this->db->fetchOne(
            "SELECT requests_count FROM api_rate_limits 
             WHERE api_key_id = ? AND reset_at = ?",
            [$apiKeyId, $resetAt]
        );
        
        if (!$record) {
            // Первый запрос в эту минуту
            $this->db->insert('api_rate_limits', [
                'api_key_id' => $apiKeyId,
                'requests_count' => 1,
                'reset_at' => $resetAt
            ]);
            return true;
        }
        
        if ($record['requests_count'] >= $maxRequests) {
            return false; // Превышен лимит
        }
        
        // Увеличиваем счетчик
        $this->db->update('api_rate_limits',
            ['requests_count' => $record['requests_count'] + 1],
            'api_key_id = ? AND reset_at = ?',
            [$apiKeyId, $resetAt]
        );
        
        return true;
    }
    
    /**
     * Получение IP адреса клиента
     * @return string
     */
    private function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Проверка, является ли пользователь владельцем группы
     * @param int $groupId
     * @param int $userId
     * @return bool
     */
    private function isGroupOwner($groupId, $userId)
    {
        $result = $this->db->fetchOne(
            "SELECT g.owner_id FROM groups g 
             JOIN group_members gm ON g.id = gm.group_id
             WHERE g.id = ? AND gm.user_id = ? AND gm.role = 'owner'",
            [$groupId, $userId]
        );
        
        return $result !== null;
    }
    
    /**
     * Проверка, имеет ли владелец группы необходимую подписку
     * @param int $groupId
     * @return bool
     */
    private function hasRequiredSubscription($groupId)
    {
        $group = $this->db->fetchOne(
            "SELECT g.owner_id FROM groups g WHERE g.id = ?",
            [$groupId]
        );
        
        if (!$group) {
            return false;
        }
        
        // Проверяем наличие активной подписки типа "Турагенство" нужно уточнить тип подписки
        // За сейчас пусть будет проверка просто на наличие активной подписки
        $subscription = $this->db->fetchOne(
            "SELECT s.id FROM subscriptions s 
             WHERE s.user_id = ? AND s.status = 'active' AND s.end_date > NOW()",
            [$group['owner_id']]
        );
        
        return $subscription !== null;
    }
    
    /**
     * Обновление времени последнего использования ключа
     * @param int $keyId
     */
    private function updateLastUsed($keyId)
    {
        $this->db->update('api_keys',
            ['last_used_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$keyId]
        );
    }
    
    /**
     * Валидация формата токена
     * @param string $token
     * @return bool
     */
    private function isValidTokenFormat($token)
    {
        // Формат: teso_xxxxx_yyyyyy
        if (strpos($token, $this->tokenPrefix) !== 0) {
            return false;
        }
        
        $parts = explode('_', $token);
        return count($parts) === 3 && strlen($parts[1]) === 10 && strlen($parts[2]) === 64;
    }
}
