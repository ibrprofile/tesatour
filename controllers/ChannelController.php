<?php

class ChannelController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function index($groupId)
    {
        if (!$this->canAccessGroup($groupId)) {
            Response::redirect('/groups');
        }
        
        $stmt = $this->db->prepare("
            SELECT c.*, u.first_name, u.last_name
            FROM group_channels c
            JOIN users u ON c.created_by = u.id
            WHERE c.group_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$groupId]);
        $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->prepare("SELECT * FROM groups WHERE id = ?");
        $stmt->execute([$groupId]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        
        View::render('channels/index', [
            'pageTitle' => 'Каналы группы',
            'group' => $group,
            'channels' => $channels,
            'userRole' => $this->getUserRole($groupId)
        ]);
    }
    
    public function show($channelId)
    {
        $stmt = $this->db->prepare("
            SELECT c.*, g.name as group_name, g.id as group_id
            FROM group_channels c
            JOIN groups g ON c.group_id = g.id
            WHERE c.id = ?
        ");
        $stmt->execute([$channelId]);
        $channel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$channel || !$this->canAccessGroup($channel['group_id'])) {
            Response::redirect('/groups');
        }
        
        $stmt = $this->db->prepare("
            SELECT m.*, u.first_name, u.last_name, u.avatar,
                   (SELECT role FROM group_members WHERE user_id = m.user_id AND group_id = ?) as user_role,
                   (SELECT COUNT(*) FROM channel_reactions WHERE message_id = m.id) as reactions_count
            FROM channel_messages m
            JOIN users u ON m.user_id = u.id
            WHERE m.channel_id = ?
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$channel['group_id'], $channelId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($messages as &$message) {
            $stmt = $this->db->prepare("SELECT * FROM channel_files WHERE message_id = ?");
            $stmt->execute([$message['id']]);
            $message['files'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $this->db->prepare("
                SELECT reaction, COUNT(*) as count,
                       GROUP_CONCAT(u.first_name SEPARATOR ', ') as users
                FROM channel_reactions cr
                JOIN users u ON cr.user_id = u.id
                WHERE cr.message_id = ?
                GROUP BY reaction
            ");
            $stmt->execute([$message['id']]);
            $message['reactions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        View::render('channels/show', [
            'pageTitle' => $channel['name'],
            'channel' => $channel,
            'messages' => $messages,
            'userRole' => $this->getUserRole($channel['group_id']),
            'userId' => Session::getUserId()
        ]);
    }
    
    public function create($groupId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $userRole = $this->getUserRole($groupId);
        if (!in_array($userRole, ['owner', 'admin'])) {
            Response::json(['success' => false, 'message' => 'Permission denied'], 403);
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            Response::json(['success' => false, 'message' => 'Channel name is required'], 400);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO group_channels (group_id, name, description, created_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$groupId, $name, $description, Session::getUserId()]);
        
        $channelId = $this->db->lastInsertId();
        
        Response::json(['success' => true, 'channel_id' => $channelId]);
    }
    
    public function postMessage($channelId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $stmt = $this->db->prepare("SELECT group_id FROM group_channels WHERE id = ?");
        $stmt->execute([$channelId]);
        $channel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$channel) {
            Response::json(['success' => false, 'message' => 'Channel not found'], 404);
        }
        
        $userRole = $this->getUserRole($channel['group_id']);
        if (!in_array($userRole, ['owner', 'admin'])) {
            Response::json(['success' => false, 'message' => 'Only admins and owners can post'], 403);
        }
        
        $messageText = trim($_POST['message_text'] ?? '');
        
        if (empty($messageText) && empty($_FILES['files'])) {
            Response::json(['success' => false, 'message' => 'Message text or files required'], 400);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO channel_messages (channel_id, user_id, message_text)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$channelId, Session::getUserId(), $messageText]);
        
        $messageId = $this->db->lastInsertId();
        
        if (!empty($_FILES['files']['name'][0])) {
            $this->uploadFiles($messageId);
        }
        
        $this->notifyChannelSubscribers($channelId, $messageText);
        
        Response::json(['success' => true, 'message_id' => $messageId]);
    }
    
    private function uploadFiles($messageId)
    {
        $uploadDir = UPLOADS_PATH . '/channels/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $files = $_FILES['files'];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $fileName = basename($files['name'][$i]);
            $fileSize = $files['size'][$i];
            $fileType = $files['type'][$i];
            $tmpName = $files['tmp_name'][$i];
            
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = uniqid() . '_' . time() . '.' . $ext;
            $filePath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($tmpName, $filePath)) {
                $stmt = $this->db->prepare("
                    INSERT INTO channel_files (message_id, file_name, file_path, file_type, file_size)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $messageId,
                    $fileName,
                    '/uploads/channels/' . $newFileName,
                    $fileType,
                    $fileSize
                ]);
            }
        }
    }
    
    public function editMessage($messageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $stmt = $this->db->prepare("
            SELECT m.*, c.group_id
            FROM channel_messages m
            JOIN group_channels c ON m.channel_id = c.id
            WHERE m.id = ?
        ");
        $stmt->execute([$messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$message) {
            Response::json(['success' => false, 'message' => 'Message not found'], 404);
        }
        
        $userRole = $this->getUserRole($message['group_id']);
        if ($message['user_id'] != Session::getUserId() && !in_array($userRole, ['owner', 'admin'])) {
            Response::json(['success' => false, 'message' => 'Permission denied'], 403);
        }
        
        $newText = trim($_POST['message_text'] ?? '');
        
        $stmt = $this->db->prepare("
            UPDATE channel_messages
            SET message_text = ?, is_edited = 1, edited_at = NOW()
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
            SELECT m.*, c.group_id
            FROM channel_messages m
            JOIN group_channels c ON m.channel_id = c.id
            WHERE m.id = ?
        ");
        $stmt->execute([$messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$message) {
            Response::json(['success' => false, 'message' => 'Message not found'], 404);
        }
        
        $userRole = $this->getUserRole($message['group_id']);
        if ($message['user_id'] != Session::getUserId() && !in_array($userRole, ['owner', 'admin'])) {
            Response::json(['success' => false, 'message' => 'Permission denied'], 403);
        }
        
        $stmt = $this->db->prepare("DELETE FROM channel_messages WHERE id = ?");
        $stmt->execute([$messageId]);
        
        Response::json(['success' => true]);
    }
    
    public function pinMessage($messageId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $stmt = $this->db->prepare("
            SELECT m.*, c.group_id
            FROM channel_messages m
            JOIN group_channels c ON m.channel_id = c.id
            WHERE m.id = ?
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
        
        $stmt = $this->db->prepare("UPDATE channel_messages SET is_pinned = ? WHERE id = ?");
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
                INSERT INTO channel_reactions (message_id, user_id, reaction)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE created_at = NOW()
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
            DELETE FROM channel_reactions
            WHERE message_id = ? AND user_id = ? AND reaction = ?
        ");
        $stmt->execute([$messageId, Session::getUserId(), $reaction]);
        
        Response::json(['success' => true]);
    }
    
    private function notifyChannelSubscribers($channelId, $messageText)
    {
        $stmt = $this->db->prepare("
            SELECT c.name, c.group_id FROM group_channels c WHERE c.id = ?
        ");
        $stmt->execute([$channelId]);
        $channel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->prepare("
            SELECT u.push_subscription
            FROM users u
            JOIN group_members gm ON u.id = gm.user_id
            WHERE gm.group_id = ? AND u.id != ? AND u.push_subscription IS NOT NULL
        ");
        $stmt->execute([$channel['group_id'], Session::getUserId()]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($subscriptions as $subscription) {
            $this->sendPushNotification(
                $subscription,
                $channel['name'],
                mb_substr($messageText, 0, 100),
                ['url' => "/channels/{$channelId}"]
            );
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
