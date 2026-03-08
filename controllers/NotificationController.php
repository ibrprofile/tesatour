<?php

class NotificationController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getUnread() {
        $this->requireAuth();
        
        $userId = $this->getUser()['id'];
        
        $stmt = $this->db->prepare("
            SELECT * FROM push_notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll();
        
        Response::json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }
    
    public function markAsRead() {
        $this->requireAuth();
        
        $userId = $this->getUser()['id'];
        $data = $this->getPostData();
        
        if (isset($data['notification_id'])) {
            $stmt = $this->db->prepare("
                UPDATE push_notifications 
                SET is_read = 1 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$data['notification_id'], $userId]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE push_notifications 
                SET is_read = 1 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
        }
        
        Response::json(['success' => true]);
    }
    
    public function subscribe() {
        $this->requireAuth();
        
        $userId = $this->getUser()['id'];
        $data = $this->getPostData();
        
        if (empty($data['subscription'])) {
            Response::json(['success' => false, 'message' => 'Subscription data required'], 400);
            return;
        }
        
        $subscription = json_encode($data['subscription']);
        
        $stmt = $this->db->prepare("
            INSERT INTO push_subscriptions (user_id, subscription, created_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE subscription = ?, updated_at = NOW()
        ");
        $stmt->execute([$userId, $subscription, $subscription]);
        
        Response::json(['success' => true]);
    }
    
    public function unsubscribe() {
        $this->requireAuth();
        
        $userId = $this->getUser()['id'];
        
        $stmt = $this->db->prepare("
            DELETE FROM push_subscriptions WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        
        Response::json(['success' => true]);
    }
}
