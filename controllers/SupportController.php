<?php

class SupportController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function index()
    {
        $userId = Session::getUserId();
        
        $messages = [];
        try {
            $stmt = $this->db->prepare("
                SELECT sm.*, 
                       u.first_name as sender_first_name, 
                       u.last_name as sender_last_name,
                       u.avatar as sender_avatar
                FROM support_messages sm
                LEFT JOIN users u ON (sm.is_from_admin = 1 AND sm.admin_id = u.id) OR (sm.is_from_admin = 0 AND sm.user_id = u.id)
                WHERE sm.user_id = ?
                ORDER BY sm.created_at ASC
            ");
            $stmt->execute([$userId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Помечаем как прочитанные
            $stmt = $this->db->prepare("UPDATE support_messages SET is_read = 1 WHERE user_id = ? AND is_from_admin = 1 AND is_read = 0");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            $messages = [];
        }
        
        $this->render('support/index', [
            'pageTitle' => 'Техническая поддержка',
            'messages' => $messages,
            'userId' => $userId
        ]);
    }
    
    public function sendMessage()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
            return;
        }
        
        $data = $this->getPostData();
        $messageText = trim($data['message_text'] ?? '');
        
        if (empty($messageText)) {
            Response::json(['success' => false, 'message' => 'Введите сообщение'], 400);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO support_messages (user_id, message_text, is_from_admin, created_at)
                VALUES (?, ?, 0, NOW())
            ");
            $stmt->execute([Session::getUserId(), $messageText]);
            
            Response::json(['success' => true, 'message_id' => $this->db->lastInsertId()]);
        } catch (PDOException $e) {
            Response::json(['success' => false, 'message' => 'Ошибка отправки сообщения'], 500);
        }
    }
    
    public function getMessages()
    {
        $userId = Session::getUserId();
        
        try {
            $stmt = $this->db->prepare("
                SELECT sm.*, 
                       u.first_name, u.last_name, u.avatar
                FROM support_messages sm
                LEFT JOIN users u ON (sm.is_from_admin = 1 AND sm.admin_id = u.id) OR (sm.is_from_admin = 0 AND sm.user_id = u.id)
                WHERE sm.user_id = ?
                ORDER BY sm.created_at ASC
            ");
            $stmt->execute([$userId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            Response::json(['success' => true, 'messages' => $messages]);
        } catch (PDOException $e) {
            Response::json(['success' => true, 'messages' => []]);
        }
    }
}
