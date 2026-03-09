<?php
/**
 * API контроллер для работы с групповым чатом
 */
class ApiChatsController extends ApiBaseController
{
    /**
     * GET /api/v1/groups/{id}/chat
     * Получение сообщений группового чата с пагинацией
     */
    public function getMessages()
    {
        if (!$this->requireScope('chats:read')) return;
        
        $limit = min((int)($_GET['limit'] ?? 50), 500);
        $offset = (int)($_GET['offset'] ?? 0);
        
        $messages = $this->db->fetchAll(
            "SELECT gcm.id, gcm.group_id, gcm.user_id, gcm.message, gcm.is_pinned, 
                    gcm.is_edited, gcm.reply_to_id, gcm.created_at, gcm.edited_at,
                    u.first_name, u.last_name, u.email
             FROM group_chat_messages gcm
             JOIN users u ON gcm.user_id = u.id
             WHERE gcm.group_id = ?
             ORDER BY gcm.created_at DESC
             LIMIT ? OFFSET ?",
            [$this->groupId, $limit, $offset]
        );
        
        // Для каждого сообщения получаем реакции и информацию об исходном сообщении (если ответ)
        foreach ($messages as &$msg) {
            $msg['id'] = (int)$msg['id'];
            $msg['group_id'] = (int)$msg['group_id'];
            $msg['user_id'] = (int)$msg['user_id'];
            $msg['is_pinned'] = (bool)$msg['is_pinned'];
            $msg['is_edited'] = (bool)$msg['is_edited'];
            $msg['reply_to_id'] = $msg['reply_to_id'] ? (int)$msg['reply_to_id'] : null;
            
            // Получаем реакции
            $reactions = $this->db->fetchAll(
                "SELECT emoji, COUNT(*) as count FROM group_chat_reactions 
                 WHERE message_id = ? GROUP BY emoji ORDER BY count DESC",
                [$msg['id']]
            );
            $msg['reactions'] = $reactions ?: [];
            
            // Если это ответ, получаем исходное сообщение
            if ($msg['reply_to_id']) {
                $originalMsg = $this->db->fetchOne(
                    "SELECT gcm.id, gcm.message, u.first_name, u.last_name
                     FROM group_chat_messages gcm
                     JOIN users u ON gcm.user_id = u.id
                     WHERE gcm.id = ?",
                    [$msg['reply_to_id']]
                );
                if ($originalMsg) {
                    $msg['reply_to'] = [
                        'id' => (int)$originalMsg['id'],
                        'message' => $originalMsg['message'],
                        'author' => [
                            'first_name' => $originalMsg['first_name'],
                            'last_name' => $originalMsg['last_name']
                        ]
                    ];
                }
            }
        }
        
        // Получаем общее количество
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM group_chat_messages WHERE group_id = ?",
            [$this->groupId]
        )['count'];
        
        $this->logSuccess('/api/v1/groups/{id}/chat', 'GET');
        $this->success([
            'messages' => array_reverse($messages),
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ], 'Сообщения группового чата');
    }
    
    /**
     * POST /api/v1/groups/{id}/chat
     * Отправка сообщения в групповой чат
     */
    public function sendMessage()
    {
        if (!$this->requireScope('chats:write')) return;
        
        $data = $this->getPostData();
        
        if (!$this->validateRequired($data, ['message'])) {
            return;
        }
        
        $message = trim($data['message']);
        
        if (strlen($message) < 1 || strlen($message) > 5000) {
            $this->error('Сообщение должно быть от 1 до 5000 символов', 400);
        }
        
        $replyToId = null;
        if (isset($data['reply_to_id'])) {
            $replyToId = (int)$data['reply_to_id'];
            
            // Проверяем что исходное сообщение существует
            $originalMsg = $this->db->fetchOne(
                "SELECT id FROM group_chat_messages WHERE id = ? AND group_id = ?",
                [$replyToId, $this->groupId]
            );
            
            if (!$originalMsg) {
                $this->error('Исходное сообщение не найдено', 404);
            }
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
        
        $messageId = $this->db->insert('group_chat_messages', [
            'group_id' => $this->groupId,
            'user_id' => $userId,
            'message' => $message,
            'reply_to_id' => $replyToId
        ]);
        
        if (!$messageId) {
            $this->logError('/api/v1/groups/{id}/chat', 'POST', 500, 'Ошибка при отправке');
            $this->error('Ошибка при отправке сообщения', 500);
        }
        
        $msg = $this->db->fetchOne(
            "SELECT gcm.*, u.first_name, u.last_name, u.email
             FROM group_chat_messages gcm
             JOIN users u ON gcm.user_id = u.id
             WHERE gcm.id = ?",
            [$messageId]
        );
        
        $msg['id'] = (int)$msg['id'];
        $msg['group_id'] = (int)$msg['group_id'];
        $msg['user_id'] = (int)$msg['user_id'];
        $msg['is_pinned'] = (bool)$msg['is_pinned'];
        $msg['is_edited'] = (bool)$msg['is_edited'];
        $msg['reply_to_id'] = $msg['reply_to_id'] ? (int)$msg['reply_to_id'] : null;
        $msg['reactions'] = [];
        
        $this->logSuccess('/api/v1/groups/{id}/chat', 'POST');
        $this->success($msg, 'Сообщение отправлено', 201);
    }
    
    /**
     * PUT /api/v1/groups/{id}/chat/{messageId}
     * Редактирование сообщения
     */
    public function editMessage()
    {
        if (!$this->requireScope('chats:write')) return;
        
        $messageId = $this->getRouteParam('messageId');
        
        if (!$messageId) {
            $this->error('ID сообщения не указан', 400);
        }
        
        // Проверяем доступ
        $msg = $this->db->fetchOne(
            "SELECT * FROM group_chat_messages WHERE id = ? AND group_id = ?",
            [$messageId, $this->groupId]
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
        
        $this->db->update('group_chat_messages',
            [
                'message' => $message,
                'is_edited' => 1,
                'edited_at' => date('Y-m-d H:i:s')
            ],
            'id = ?',
            [$messageId]
        );
        
        $updated = $this->db->fetchOne(
            "SELECT gcm.*, u.first_name, u.last_name, u.email
             FROM group_chat_messages gcm
             JOIN users u ON gcm.user_id = u.id
             WHERE gcm.id = ?",
            [$messageId]
        );
        
        $reactions = $this->db->fetchAll(
            "SELECT emoji, COUNT(*) as count FROM group_chat_reactions 
             WHERE message_id = ? GROUP BY emoji ORDER BY count DESC",
            [$messageId]
        );
        
        $updated['id'] = (int)$updated['id'];
        $updated['group_id'] = (int)$updated['group_id'];
        $updated['user_id'] = (int)$updated['user_id'];
        $updated['is_pinned'] = (bool)$updated['is_pinned'];
        $updated['is_edited'] = (bool)$updated['is_edited'];
        $updated['reply_to_id'] = $updated['reply_to_id'] ? (int)$updated['reply_to_id'] : null;
        $updated['reactions'] = $reactions ?: [];
        
        $this->logSuccess('/api/v1/groups/{id}/chat/{messageId}', 'PUT');
        $this->success($updated, 'Сообщение отредактировано');
    }
    
    /**
     * DELETE /api/v1/groups/{id}/chat/{messageId}
     * Удаление сообщения
     */
    public function deleteMessage()
    {
        if (!$this->requireScope('chats:write')) return;
        
        $messageId = $this->getRouteParam('messageId');
        
        if (!$messageId) {
            $this->error('ID сообщения не указан', 400);
        }
        
        // Проверяем доступ и владение
        $msg = $this->db->fetchOne(
            "SELECT * FROM group_chat_messages WHERE id = ? AND group_id = ?",
            [$messageId, $this->groupId]
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
        $this->db->delete('group_chat_reactions', 'message_id = ?', [$messageId]);
        
        // Удаляем ответы (если есть)
        $replies = $this->db->fetchAll(
            "SELECT id FROM group_chat_messages WHERE reply_to_id = ?",
            [$messageId]
        );
        
        foreach ($replies as $reply) {
            $this->db->delete('group_chat_reactions', 'message_id = ?', [$reply['id']]);
        }
        
        $this->db->update('group_chat_messages',
            ['reply_to_id' => null],
            'reply_to_id = ?',
            [$messageId]
        );
        
        // Удаляем сообщение
        $this->db->delete('group_chat_messages', 'id = ?', [$messageId]);
        
        $this->logSuccess('/api/v1/groups/{id}/chat/{messageId}', 'DELETE');
        $this->success(null, 'Сообщение удалено');
    }
    
    /**
     * POST /api/v1/groups/{id}/chat/{messageId}/reactions
     * Добавление реакции на сообщение
     */
    public function addReaction()
    {
        if (!$this->requireScope('chats:write')) return;
        
        $messageId = $this->getRouteParam('messageId');
        
        if (!$messageId) {
            $this->error('ID сообщения не указано', 400);
        }
        
        // Проверяем доступ
        $msg = $this->db->fetchOne(
            "SELECT * FROM group_chat_messages WHERE id = ? AND group_id = ?",
            [$messageId, $this->groupId]
        );
        
        if (!$msg) {
            $this->error('Сообщение не найдено', 404);
        }
        
        $data = $this->getPostData();
        
        if (!$this->validateRequired($data, ['emoji'])) {
            return;
        }
        
        $emoji = trim($data['emoji']);
        
        if (strlen($emoji) < 1 || strlen($emoji) > 10) {
            $this->error('Некорректный emoji', 400);
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
        
        // Проверяем что этот пользователь еще не добавил такую реакцию
        $existing = $this->db->fetchOne(
            "SELECT id FROM group_chat_reactions WHERE message_id = ? AND user_id = ? AND emoji = ?",
            [$messageId, $userId, $emoji]
        );
        
        if ($existing) {
            // Удаляем реакцию (toggle)
            $this->db->delete('group_chat_reactions',
                'message_id = ? AND user_id = ? AND emoji = ?',
                [$messageId, $userId, $emoji]
            );
            $action = 'removed';
        } else {
            // Добавляем реакцию
            $this->db->insert('group_chat_reactions', [
                'message_id' => $messageId,
                'user_id' => $userId,
                'emoji' => $emoji
            ]);
            $action = 'added';
        }
        
        // Получаем обновленные реакции
        $reactions = $this->db->fetchAll(
            "SELECT emoji, COUNT(*) as count FROM group_chat_reactions 
             WHERE message_id = ? GROUP BY emoji ORDER BY count DESC",
            [$messageId]
        );
        
        $this->logSuccess('/api/v1/groups/{id}/chat/{messageId}/reactions', 'POST');
        $this->success([
            'action' => $action,
            'emoji' => $emoji,
            'reactions' => $reactions ?: []
        ], 'Реакция успешна');
    }
    
    /**
     * PUT /api/v1/groups/{id}/chat/{messageId}/pin
     * Закрепление/открепление сообщения
     */
    public function togglePin()
    {
        if (!$this->requireScope('chats:write')) return;
        
        $messageId = $this->getRouteParam('messageId');
        
        if (!$messageId) {
            $this->error('ID сообщения не указан', 400);
        }
        
        // Проверяем доступ
        $msg = $this->db->fetchOne(
            "SELECT * FROM group_chat_messages WHERE id = ? AND group_id = ?",
            [$messageId, $this->groupId]
        );
        
        if (!$msg) {
            $this->error('Сообщение не найдено', 404);
        }
        
        $userId = Session::getUserId();
        
        // Только админы и владельцы могут закреплять
        $member = $this->db->fetchOne(
            "SELECT role FROM group_members WHERE group_id = ? AND user_id = ?",
            [$this->groupId, $userId]
        );
        
        if ($member['role'] !== 'owner' && $member['role'] !== 'admin') {
            $this->error('Только администраторы могут закреплять сообщения', 403);
        }
        
        // Переключаем статус
        $newStatus = !$msg['is_pinned'];
        
        $this->db->update('group_chat_messages',
            ['is_pinned' => $newStatus ? 1 : 0],
            'id = ?',
            [$messageId]
        );
        
        $updated = $this->db->fetchOne(
            "SELECT gcm.*, u.first_name, u.last_name, u.email
             FROM group_chat_messages gcm
             JOIN users u ON gcm.user_id = u.id
             WHERE gcm.id = ?",
            [$messageId]
        );
        
        $updated['id'] = (int)$updated['id'];
        $updated['group_id'] = (int)$updated['group_id'];
        $updated['user_id'] = (int)$updated['user_id'];
        $updated['is_pinned'] = (bool)$updated['is_pinned'];
        $updated['is_edited'] = (bool)$updated['is_edited'];
        $updated['reply_to_id'] = $updated['reply_to_id'] ? (int)$updated['reply_to_id'] : null;
        
        $this->logSuccess('/api/v1/groups/{id}/chat/{messageId}/pin', 'PUT');
        $this->success($updated, 'Статус закрепления изменен');
    }
}
