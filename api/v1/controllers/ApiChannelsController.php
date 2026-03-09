<?php
/**
 * API контроллер для работы с каналами группы
 */
class ApiChannelsController extends ApiBaseController
{
    /**
     * GET /api/v1/groups/{id}/channels
     * Получение списка каналов группы
     */
    public function list()
    {
        if (!$this->requireScope('channels:read')) return;
        
        $limit = min((int)($_GET['limit'] ?? 50), 500);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $channels = $this->db->fetchAll(
            "SELECT id, group_id, name, description, is_private, created_by, created_at
             FROM group_channels
             WHERE group_id = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            [$this->groupId, $limit, $offset]
        );
        
        // Получаем информацию о создателе для каждого канала
        foreach ($channels as &$channel) {
            $creator = $this->db->fetchOne(
                "SELECT first_name, last_name FROM users WHERE id = ?",
                [$channel['created_by']]
            );
            $channel['created_by_name'] = [
                'first_name' => $creator['first_name'] ?? '',
                'last_name' => $creator['last_name'] ?? ''
            ];
            
            // Получаем количество сообщений
            $msgCount = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM channel_messages WHERE channel_id = ?",
                [$channel['id']]
            );
            $channel['messages_count'] = (int)$msgCount['count'];
            
            // Получаем последнее сообщение
            $lastMsg = $this->db->fetchOne(
                "SELECT message, created_at FROM channel_messages WHERE channel_id = ? ORDER BY created_at DESC LIMIT 1",
                [$channel['id']]
            );
            if ($lastMsg) {
                $channel['last_message'] = $lastMsg['message'];
                $channel['last_message_at'] = $lastMsg['created_at'];
            }
        }
        
        // Получаем общее количество
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM group_channels WHERE group_id = ?",
            [$this->groupId]
        )['count'];
        
        $this->logSuccess('/api/v1/groups/{id}/channels', 'GET');
        $this->success([
            'channels' => $channels,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ], 'Список каналов');
    }
    
    /**
     * POST /api/v1/groups/{id}/channels
     * Создание нового канала
     */
    public function create()
    {
        if (!$this->requireScope('channels:write')) return;
        
        $data = $this->getPostData();
        
        if (!$this->validateRequired($data, ['name'])) {
            return;
        }
        
        $name = trim($data['name']);
        
        if (strlen($name) < 1 || strlen($name) > 100) {
            $this->error('Имя канала должно быть от 1 до 100 символов', 400);
        }
        
        $description = trim($data['description'] ?? '');
        if (strlen($description) > 500) {
            $this->error('Описание не может быть длиннее 500 символов', 400);
        }
        
        $isPrivate = isset($data['is_private']) ? (bool)$data['is_private'] : false;
        
        $userId = Session::getUserId();
        
        $channelId = $this->db->insert('group_channels', [
            'group_id' => $this->groupId,
            'name' => $name,
            'description' => $description,
            'is_private' => $isPrivate ? 1 : 0,
            'created_by' => $userId
        ]);
        
        if (!$channelId) {
            $this->logError('/api/v1/groups/{id}/channels', 'POST', 500, 'Ошибка при создании канала');
            $this->error('Ошибка при создании канала', 500);
        }
        
        $channel = [
            'id' => $channelId,
            'group_id' => $this->groupId,
            'name' => $name,
            'description' => $description,
            'is_private' => $isPrivate,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'messages_count' => 0
        ];
        
        $this->logSuccess('/api/v1/groups/{id}/channels', 'POST');
        $this->success($channel, 'Канал успешно создан', 201);
    }
    
    /**
     * GET /api/v1/channels/{id}
     * Получение информации о канале
     */
    public function get()
    {
        if (!$this->requireScope('channels:read')) return;
        
        $channelId = $this->getRouteParam('id');
        if (!$channelId) {
            $this->error('ID канала не указан', 400);
        }
        
        $channel = $this->db->fetchOne(
            "SELECT * FROM group_channels WHERE id = ?",
            [$channelId]
        );
        
        if (!$channel || $channel['group_id'] != $this->groupId) {
            $this->error('Канал не найден', 404);
        }
        
        $creator = $this->db->fetchOne(
            "SELECT first_name, last_name FROM users WHERE id = ?",
            [$channel['created_by']]
        );
        
        $msgCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM channel_messages WHERE channel_id = ?",
            [$channel['id']]
        );
        
        $channel['created_by_name'] = [
            'first_name' => $creator['first_name'] ?? '',
            'last_name' => $creator['last_name'] ?? ''
        ];
        $channel['messages_count'] = (int)$msgCount['count'];
        
        $this->logSuccess('/api/v1/channels/{id}', 'GET');
        $this->success($channel, 'Информация о канале');
    }
    
    /**
     * GET /api/v1/channels/{id}/messages
     * Получение сообщений канала (с пагинацией)
     */
    public function getMessages()
    {
        if (!$this->requireScope('channels:read')) return;
        
        $channelId = $this->getRouteParam('id');
        if (!$channelId) {
            $this->error('ID канала не указан', 400);
        }
        
        // Проверяем доступ
        $channel = $this->db->fetchOne(
            "SELECT * FROM group_channels WHERE id = ? AND group_id = ?",
            [$channelId, $this->groupId]
        );
        
        if (!$channel) {
            $this->error('Канал не найден', 404);
        }
        
        $limit = min((int)($_GET['limit'] ?? 50), 500);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $messages = $this->db->fetchAll(
            "SELECT cm.id, cm.channel_id, cm.user_id, cm.message, cm.is_pinned, 
                    cm.is_edited, cm.created_at, cm.edited_at,
                    u.first_name, u.last_name, u.email
             FROM channel_messages cm
             JOIN users u ON cm.user_id = u.id
             WHERE cm.channel_id = ?
             ORDER BY cm.created_at DESC
             LIMIT ? OFFSET ?",
            [$channelId, $limit, $offset]
        );
        
        // Для каждого сообщения получаем реакции
        foreach ($messages as &$msg) {
            $reactions = $this->db->fetchAll(
                "SELECT emoji, COUNT(*) as count FROM channel_reactions 
                 WHERE message_id = ? GROUP BY emoji ORDER BY count DESC",
                [$msg['id']]
            );
            $msg['reactions'] = $reactions ?: [];
        }
        
        // Получаем общее количество
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM channel_messages WHERE channel_id = ?",
            [$channelId]
        )['count'];
        
        $this->logSuccess('/api/v1/channels/{id}/messages', 'GET');
        $this->success([
            'messages' => array_reverse($messages),
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ], 'Сообщения канала');
    }
    
    /**
     * POST /api/v1/channels/{id}/messages
     * Отправка сообщения в канал
     */
    public function sendMessage()
    {
        if (!$this->requireScope('channels:write')) return;
        
        $channelId = $this->getRouteParam('id');
        if (!$channelId) {
            $this->error('ID канала не указан', 400);
        }
        
        // Проверяем доступ
        $channel = $this->db->fetchOne(
            "SELECT * FROM group_channels WHERE id = ? AND group_id = ?",
            [$channelId, $this->groupId]
        );
        
        if (!$channel) {
            $this->error('Канал не найден', 404);
        }
        
        $data = $this->getPostData();
        
        if (!$this->validateRequired($data, ['message'])) {
            return;
        }
        
        $message = trim($data['message']);
        
        if (strlen($message) < 1 || strlen($message) > 5000) {
            $this->error('Сообщение должно быть от 1 до 5000 символов', 400);
        }
        
        $userId = Session::getUserId();
        
        // Проверяем что пользователь в группе
        $member = $this->db->fetchOne(
            "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?",
            [$this->groupId, $userId]
        );
        
        if (!$member) {
            $this->error('Вы не состоите в этой группе', 403);
        }
        
        $messageId = $this->db->insert('channel_messages', [
            'channel_id' => $channelId,
            'user_id' => $userId,
            'message' => $message
        ]);
        
        if (!$messageId) {
            $this->logError('/api/v1/channels/{id}/messages', 'POST', 500, 'Ошибка при отправке');
            $this->error('Ошибка при отправке сообщения', 500);
        }
        
        $msg = $this->db->fetchOne(
            "SELECT cm.*, u.first_name, u.last_name, u.email
             FROM channel_messages cm
             JOIN users u ON cm.user_id = u.id
             WHERE cm.id = ?",
            [$messageId]
        );
        
        $msg['reactions'] = [];
        
        $this->logSuccess('/api/v1/channels/{id}/messages', 'POST');
        $this->success($msg, 'Сообщение отправлено', 201);
    }
    
    /**
     * PUT /api/v1/channels/{id}/messages/{messageId}
     * Редактирование сообщения
     */
    public function editMessage()
    {
        if (!$this->requireScope('channels:write')) return;
        
        $channelId = $this->getRouteParam('id');
        $messageId = $this->getRouteParam('messageId');
        
        if (!$channelId || !$messageId) {
            $this->error('ID канала или сообщения не указаны', 400);
        }
        
        // Проверяем доступ
        $msg = $this->db->fetchOne(
            "SELECT cm.* FROM channel_messages cm
             JOIN group_channels gc ON cm.channel_id = gc.id
             WHERE cm.id = ? AND cm.channel_id = ? AND gc.group_id = ?",
            [$messageId, $channelId, $this->groupId]
        );
        
        if (!$msg) {
            $this->error('Сообщение не найдено', 404);
        }
        
        $userId = Session::getUserId();
        
        // Только автор может редактировать
        if ($msg['user_id'] != $userId) {
            $this->error('Вы не можете редактировать это сообщение', 403);
        }
        
        $data = $this->getPostData();
        
        if (!$this->validateRequired($data, ['message'])) {
            return;
        }
        
        $message = trim($data['message']);
        
        if (strlen($message) < 1 || strlen($message) > 5000) {
            $this->error('Сообщение должно быть от 1 до 5000 символов', 400);
        }
        
        $this->db->update('channel_messages',
            [
                'message' => $message,
                'is_edited' => 1,
                'edited_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$messageId]
        );
        
        $updated = $this->db->fetchOne(
            "SELECT cm.*, u.first_name, u.last_name, u.email
             FROM channel_messages cm
             JOIN users u ON cm.user_id = u.id
             WHERE cm.id = ?",
            [$messageId]
        );
        
        $reactions = $this->db->fetchAll(
            "SELECT emoji, COUNT(*) as count FROM channel_reactions 
             WHERE message_id = ? GROUP BY emoji ORDER BY count DESC",
            [$messageId]
        );
        $updated['reactions'] = $reactions ?: [];
        
        $this->logSuccess('/api/v1/channels/{id}/messages/{messageId}', 'PUT');
        $this->success($updated, 'Сообщение отредактировано');
    }
    
    /**
     * DELETE /api/v1/channels/{id}/messages/{messageId}
     * Удаление сообщения
     */
    public function deleteMessage()
    {
        if (!$this->requireScope('channels:write')) return;
        
        $channelId = $this->getRouteParam('id');
        $messageId = $this->getRouteParam('messageId');
        
        if (!$channelId || !$messageId) {
            $this->error('ID канала или сообщения не указаны', 400);
        }
        
        // Проверяем доступ и владение
        $msg = $this->db->fetchOne(
            "SELECT cm.* FROM channel_messages cm
             JOIN group_channels gc ON cm.channel_id = gc.id
             WHERE cm.id = ? AND cm.channel_id = ? AND gc.group_id = ?",
            [$messageId, $channelId, $this->groupId]
        );
        
        if (!$msg) {
            $this->error('Сообщение не найдено', 404);
        }
        
        $userId = Session::getUserId();
        
        // Только автор или админ группы может удалить
        $member = $this->db->fetchOne(
            "SELECT role FROM group_members WHERE group_id = ? AND user_id = ?",
            [$this->groupId, $userId]
        );
        
        if ($msg['user_id'] != $userId && $member['role'] !== 'owner' && $member['role'] !== 'admin') {
            $this->error('Вы не можете удалить это сообщение', 403);
        }
        
        // Удаляем реакции
        $this->db->delete('channel_reactions', 'message_id = ?', [$messageId]);
        
        // Удаляем сообщение
        $this->db->delete('channel_messages', 'id = ?', [$messageId]);
        
        $this->logSuccess('/api/v1/channels/{id}/messages/{messageId}', 'DELETE');
        $this->success(null, 'Сообщение удалено');
    }
}
