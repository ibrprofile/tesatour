<?php
/**
 * Модель пользователя
 */
class User
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Создание нового пользователя
     */
    public function create($data)
    {
        $insertData = [
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'birth_date' => $data['birth_date'],
        ];
        
        if ($this->hasColumn('users', 'middle_name')) {
            $insertData['middle_name'] = $data['middle_name'] ?? null;
        }
        if ($this->hasColumn('users', 'avatar')) {
            $insertData['avatar'] = $data['avatar'] ?? null;
        }
        if ($this->hasColumn('users', 'account_type')) {
            $insertData['account_type'] = $data['account_type'] ?? 'amateur';
        }
        
        return $this->db->insert('users', $insertData);
    }
    
    /**
     * Проверка существования столбца в таблице
     */
    private function hasColumn($table, $column)
    {
        try {
            $result = $this->db->fetchAll(
                "SHOW COLUMNS FROM `{$table}` LIKE ?",
                [$column]
            );
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Поиск пользователя по ID
     */
    public function findById($id)
    {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
    }
    
    /**
     * Поиск пользователя по email
     */
    public function findByEmail($email)
    {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }
    
    /**
     * Поиск пользователя по Telegram ID
     */
    public function findByTelegramId($telegramId)
    {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE telegram_id = ?",
            [$telegramId]
        );
    }
    
    /**
     * Проверка пароля
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Обновление данных пользователя
     */
    public function update($id, $data)
    {
        return $this->db->update('users', $data, 'id = ?', [$id]);
    }
    
    /**
     * Обновление аватара
     */
    public function updateAvatar($id, $avatarPath)
    {
        return $this->update($id, ['avatar' => $avatarPath]);
    }
    
    /**
     * Привязка Telegram
     */
    public function linkTelegram($userId, $telegramId, $username = null)
    {
        return $this->update($userId, [
            'telegram_id' => $telegramId,
            'telegram_username' => $username
        ]);
    }
    
    /**
     * Отвязка Telegram
     */
    public function unlinkTelegram($userId)
    {
        return $this->update($userId, [
            'telegram_id' => null,
            'telegram_username' => null
        ]);
    }
    
    /**
     * Обновление геолокации
     */
    public function updateLocation($userId, $latitude, $longitude)
    {
        // Обновляем последнюю локацию пользователя
        $this->update($userId, [
            'last_latitude' => $latitude,
            'last_longitude' => $longitude,
            'last_location_update' => date('Y-m-d H:i:s'),
            'geolocation_enabled' => 1
        ]);
        
        // Добавляем в историю
        $this->db->insert('location_history', [
            'user_id' => $userId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'recorded_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Получение истории локаций
     */
    public function getLocationHistory($userId, $limit = 50, $offset = 0)
    {
        return $this->db->fetchAll(
            "SELECT * FROM location_history 
             WHERE user_id = ? 
             ORDER BY recorded_at DESC 
             LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }
    
    /**
     * Получение последней локации
     */
    public function getLastLocation($userId)
    {
        $user = $this->findById($userId);
        
        if ($user && $user['last_latitude'] && $user['last_longitude']) {
            return [
                'latitude' => (float) $user['last_latitude'],
                'longitude' => (float) $user['last_longitude'],
                'updated_at' => $user['last_location_update']
            ];
        }
        
        return null;
    }
    
    /**
     * Получение полного имени пользователя
     */
    public function getFullName($user)
    {
        $parts = [$user['last_name'], $user['first_name']];
        
        if (!empty($user['middle_name'])) {
            $parts[] = $user['middle_name'];
        }
        
        return implode(' ', $parts);
    }
    
    /**
     * Получение короткого имени (Фамилия И.)
     */
    public function getShortName($user)
    {
        return $user['last_name'] . ' ' . mb_substr($user['first_name'], 0, 1) . '.';
    }
    
    /**
     * Проверка существования email
     */
    public function emailExists($email, $exceptId = null)
    {
        if ($exceptId) {
            return $this->db->exists('users', 'email = ? AND id != ?', [$email, $exceptId]);
        }
        return $this->db->exists('users', 'email = ?', [$email]);
    }
    
    /**
     * Получение URL аватара
     */
    public function getAvatarUrl($avatar)
    {
        if ($avatar && file_exists(UPLOADS_PATH . '/' . $avatar)) {
            return uploads($avatar);
        }
        return asset('images/default-avatar.svg');
    }
}
