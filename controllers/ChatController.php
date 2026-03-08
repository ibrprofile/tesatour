<?php

class ChatController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function show($groupId)
    {
        if (!$this->canAccessGroup($groupId)) {
            Response::redirect('/groups');
        }
        
        $stmt = $this->db->prepare("SELECT * FROM groups WHERE id = ?");
        $stmt->execute([$groupId]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$group) {
            Response::redirect('/groups');
        }
        
        $messages = [];
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, u.first_name, u.last_name, u.avatar,
                       gm.role as user_role,
                       rm.message_text as reply_to_text,
                       ru.first_name as reply_to_first_name,
                       ru.last_name as reply_to_last_name
                FROM chat_messages m
                JOIN users u ON m.user_id = u.id
                JOIN group_members gm ON m.user_id = gm.user_id AND gm.group_id = m.group_id
                LEFT JOIN chat_messages rm ON m.reply_to_message_id = rm.id
                LEFT JOIN users ru ON rm.user_id = ru.id
                WHERE m.group_id = ?
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([$groupId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $messages = [];
        }
        
        foreach ($messages as &$message) {
            try {
                $stmt = $this->db->prepare("
                    SELECT reaction, COUNT(*) as count,
                           GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') as users
                    FROM message_reactions cr
                    JOIN users u ON cr.user_id = u.id
                    WHERE cr.message_id = ? AND cr.message_type = 'chat'
                    GROUP BY reaction
                ");
                $stmt->execute([$message['id']]);
                $message['reactions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $message['reactions'] = [];
            }
        }
        
        View::render('chat/show', [
            'pageTitle' => 'Чат группы',
            'group' => $group,
            'messages' => $messages,
            'userRole' => $this->getUserRole($groupId),
            'userId' => Session::getUserId()
        ]);
    }
    
    public function sendMessage($groupId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        if (!$this->canAccessGroup($groupId)) {
            Response::json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        $messageText = trim($_POST['message_text'] ?? '');
        $replyToId = !empty($_POST['reply_to_message_id']) ? (int)$_POST['reply_to_message_id'] : null;
        
        if (empty($messageText)) {
            Response::json(['success' => false, 'message' => 'Message text required'], 400);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO chat_messages (group_id, user_id, message_text, reply_to_message_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$groupId, Session::getUserId(), $messageText, $replyToId]);
        
        $messageId = $this->db->lastInsertId();
        
        $this->notifyGroupMembers($groupId, $messageText);
        
        Response::json(['success' => true, 'message_id' => $messageId]);
    }
    
    public function editMessage($messageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $stmt = $this->db->prepare("SELECT * FROM chat_messages WHERE id = ?");
        $stmt->execute([$messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$message) {
            Response::json(['success' => false, 'message' => 'Message not found'], 404);
        }
        
        if ($message['user_id'] != Session::getUserId()) {
            Response::json(['success' => false, 'message' => 'Permission denied'], 403);
        }
        
        $newText = trim($_POST['message_text'] ?? '');
        
        if (empty($newText)) {
            Response::json(['success' => false, 'message' => 'Message text required'], 400);
        }
        
        $stmt = $this->db->prepare("
            UPDATE chat_messages
            SET message_text = ?, is_edited = 1, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$newText, $messageId]);
        
        Response::json(['success' => true]);
    }
    
    public function deleteMessage($messageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $stmt = $this->db->prepare("
            SELECT m.*, gm.role
            FROM chat_messages m
            LEFT JOIN group_members gm ON m.group_id = gm.group_id AND gm.user_id = ?
            WHERE m.id = ?
        ");
        $stmt->execute([Session::getUserId(), $messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$message) {
            Response::json(['success' => false, 'message' => 'Message not found'], 404);
        }
        
        $canDelete = $message['user_id'] == Session::getUserId() || 
                     in_array($message['role'], ['owner', 'admin']);
        
        if (!$canDelete) {
            Response::json(['success' => false, 'message' => 'Permission denied'], 403);
        }
        
        $stmt = $this->db->prepare("DELETE FROM chat_messages WHERE id = ?");
        $stmt->execute([$messageId]);
        
        Response::json(['success' => true]);
    }
    
    public function pinMessage($messageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $stmt = $this->db->prepare("
            SELECT m.group_id FROM chat_messages m WHERE m.id = ?
        ");
        $stmt->execute([$messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$message) {
            Response::json(['success' => false, 'message' => 'Message not found'], 404);
        }
        
        $userRole = $this->getUserRole($message['group_id']);
        if (!in_array($userRole, ['owner', 'admin'])) {
            Response::json(['success' => false, 'message' => 'Permission denied'], 403);
        }
        
        $isPinned = $_POST['pin'] ?? 1;
        
        $stmt = $this->db->prepare("UPDATE chat_messages SET is_pinned = ? WHERE id = ?");
        $stmt->execute([$isPinned, $messageId]);
        
        Response::json(['success' => true]);
    }
    
    public function addReaction($messageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $reaction = $_POST['reaction'] ?? '';
        $allowedReactions = ['👍', '👎', '💩', '🤡', '💀', '😈', '😍', '😇', '😂', '🥶', '😳', '😭', '🥳', '🤯', '🤬', '🫣', '❤️'];
        
        if (!in_array($reaction, $allowedReactions)) {
            Response::json(['success' => false, 'message' => 'Invalid reaction'], 400);
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO message_reactions (message_id, message_type, user_id, reaction)
                VALUES (?, 'chat', ?, ?)
                ON DUPLICATE KEY UPDATE reaction = VALUES(reaction), created_at = NOW()
            ");
            $stmt->execute([$messageId, Session::getUserId(), $reaction]);
            
            Response::json(['success' => true]);
        } catch (Exception $e) {
            Response::json(['success' => false, 'message' => 'Error adding reaction'], 500);
        }
    }
    
    public function removeReaction($messageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $reaction = $_POST['reaction'] ?? '';
        
        $stmt = $this->db->prepare("
            DELETE FROM message_reactions
            WHERE message_id = ? AND message_type = 'chat' AND user_id = ? AND reaction = ?
        ");
        $stmt->execute([$messageId, Session::getUserId(), $reaction]);
        
        Response::json(['success' => true]);
    }
    
    private function notifyGroupMembers($groupId, $messageText)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT u.push_subscription, g.name
                FROM users u
                JOIN group_members gm ON u.id = gm.user_id
                JOIN groups g ON gm.group_id = g.id
                WHERE gm.group_id = ? AND u.id != ? AND u.push_subscription IS NOT NULL
            ");
            $stmt->execute([$groupId, Session::getUserId()]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($members as $member) {
                $this->sendPushNotification(
                    $member['push_subscription'],
                    'Новое сообщение в ' . $member['name'],
                    mb_substr($messageText, 0, 100),
                    ['url' => "/chat/{$groupId}"]
                );
            }
        } catch (\Exception $e) {
            // push_subscription column may not exist - non-critical
            error_log('Chat notify error: ' . $e->getMessage());
        }
    }
    
    private function sendPushNotification($subscription, $title, $body, $data = [])
    {
        // TODO: Implement Web Push
    }
    
    private function canAccessGroup($groupId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM group_members WHERE group_id = ? AND user_id = ?
        ");
        $stmt->execute([$groupId, Session::getUserId()]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function getUserRole($groupId)
    {
        $stmt = $this->db->prepare("
            SELECT role FROM group_members WHERE group_id = ? AND user_id = ?
        ");
        $stmt->execute([$groupId, Session::getUserId()]);
        return $stmt->fetchColumn();
    }
}
