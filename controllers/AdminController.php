<?php

class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
        $this->requireAdmin();
    }
    
    private function requireAdmin()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('/auth/login');
        }
        
        $userId = Session::getUserId();
        $stmt = $this->db->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !$user['is_admin']) {
            Response::redirect('/dashboard');
        }
    }
    
    private function logAction($actionType, $entityType = null, $entityId = null, $description = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO admin_logs (admin_id, action_type, entity_type, entity_id, description, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            Session::getUserId(),
            $actionType,
            $entityType,
            $entityId,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
    
    public function index()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users");
        $usersCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM groups WHERE status = 'active'");
        $groupsCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM sos_alerts WHERE status = 'active'");
        $sosCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $this->db->query("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM groups WHERE owner_id = u.id) as groups_owned
            FROM users u
            ORDER BY u.created_at DESC
            LIMIT 10
        ");
        $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->query("
            SELECT g.*, u.first_name, u.last_name,
                   (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as members_count
            FROM groups g
            JOIN users u ON g.owner_id = u.id
            ORDER BY g.created_at DESC
            LIMIT 10
        ");
        $recentGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        View::render('admin/index', [
            'pageTitle' => 'Панель администратора',
            'usersCount' => $usersCount,
            'groupsCount' => $groupsCount,
            'sosCount' => $sosCount,
            'recentUsers' => $recentUsers,
            'recentGroups' => $recentGroups
        ]);
    }
    
    public function users()
    {
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $whereClause = '';
        $params = [];
        
        if ($search) {
            $whereClause = "WHERE email LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users {$whereClause}");
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $this->db->prepare("
            SELECT u.*,
                   (SELECT COUNT(*) FROM groups WHERE owner_id = u.id) as groups_owned,
                   (SELECT COUNT(*) FROM group_members WHERE user_id = u.id) as groups_joined
            FROM users u
            {$whereClause}
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        View::render('admin/users', [
            'pageTitle' => 'Управление пользователями',
            'users' => $users,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }
    
    public function groups()
    {
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $whereClause = '';
        $params = [];
        
        if ($search) {
            $whereClause = "WHERE g.name LIKE ?";
            $params = ["%{$search}%"];
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM groups g {$whereClause}");
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $this->db->prepare("
            SELECT g.*, u.first_name, u.last_name, u.email,
                   (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as members_count
            FROM groups g
            JOIN users u ON g.owner_id = u.id
            {$whereClause}
            ORDER BY g.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        View::render('admin/groups', [
            'pageTitle' => 'Управление группами',
            'groups' => $groups,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }
    
    public function toggleAdminMode()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $userId = Session::getUserId();
        $currentMode = $_POST['mode'] ?? 0;
        $newMode = $currentMode ? 0 : 1;
        
        $stmt = $this->db->prepare("UPDATE users SET admin_mode = ? WHERE id = ?");
        $stmt->execute([$newMode, $userId]);
        
        $this->logAction('toggle_admin_mode', 'user', $userId, "Admin mode: " . ($newMode ? 'ON' : 'OFF'));
        
        Response::json(['success' => true, 'admin_mode' => $newMode]);
    }
    
    public function toggleAdmin($userId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
            return;
        }
        
        $targetUserId = (int)$userId;
        
        if ($targetUserId == Session::getUserId()) {
            Response::json(['success' => false, 'message' => 'Нельзя изменить свои права'], 400);
            return;
        }
        
        $stmt = $this->db->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$targetUserId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Пользователь не найден'], 404);
            return;
        }
        
        $newAdminStatus = $user['is_admin'] ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE users SET is_admin = ?, admin_mode = 0 WHERE id = ?");
        $stmt->execute([$newAdminStatus, $targetUserId]);
        
        $this->logAction($newAdminStatus ? 'grant_admin' : 'revoke_admin', 'user', $targetUserId, 
            ($newAdminStatus ? 'Granted' : 'Revoked') . ' admin rights');
        
        Response::json(['success' => true, 'is_admin' => $newAdminStatus]);
    }
    
    public function supportList()
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.first_name, u.last_name, u.email, u.avatar,
                   (SELECT message_text FROM support_messages WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as last_message,
                   (SELECT created_at FROM support_messages WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as last_message_at,
                   (SELECT COUNT(*) FROM support_messages WHERE user_id = u.id AND is_from_admin = 0 AND is_read = 0) as unread_count
            FROM users u
            WHERE u.id IN (SELECT DISTINCT user_id FROM support_messages)
            ORDER BY last_message_at DESC
        ");
        $stmt->execute();
        $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        View::render('admin/support-list', [
            'pageTitle' => 'Техподдержка',
            'chats' => $chats
        ]);
    }
    
    public function supportChat($userId)
    {
        $targetUserId = (int)$userId;
        
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, email, avatar FROM users WHERE id = ?");
        $stmt->execute([$targetUserId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$targetUser) {
            Response::redirect('/admin/support');
            return;
        }
        
        $stmt = $this->db->prepare("
            SELECT sm.*, u.first_name, u.last_name, u.avatar
            FROM support_messages sm
            LEFT JOIN users u ON (sm.is_from_admin = 1 AND sm.admin_id = u.id) OR (sm.is_from_admin = 0 AND sm.user_id = u.id)
            WHERE sm.user_id = ?
            ORDER BY sm.created_at ASC
        ");
        $stmt->execute([$targetUserId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark as read
        $stmt = $this->db->prepare("UPDATE support_messages SET is_read = 1 WHERE user_id = ? AND is_from_admin = 0 AND is_read = 0");
        $stmt->execute([$targetUserId]);
        
        View::render('admin/support-chat', [
            'pageTitle' => 'Чат с ' . $targetUser['first_name'],
            'targetUser' => $targetUser,
            'messages' => $messages
        ]);
    }
    
    public function sendSupportReply($userId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
            return;
        }
        
        $targetUserId = (int)$userId;
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $messageText = trim($data['message_text'] ?? '');
        
        if (empty($messageText)) {
            Response::json(['success' => false, 'message' => 'Введите сообщение'], 400);
            return;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO support_messages (user_id, admin_id, message_text, is_from_admin, created_at)
            VALUES (?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$targetUserId, Session::getUserId(), $messageText]);
        
        Response::json(['success' => true]);
    }
    
    public function deleteUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $targetUserId = $_POST['user_id'] ?? null;
        
        if (!$targetUserId || $targetUserId == Session::getUserId()) {
            Response::json(['success' => false, 'message' => 'Cannot delete own account'], 400);
        }
        
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$targetUserId]);
        
        $this->logAction('delete_user', 'user', $targetUserId, "Deleted user account");
        
        Response::json(['success' => true]);
    }
    
    public function deleteGroup()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $groupId = $_POST['group_id'] ?? null;
        
        if (!$groupId) {
            Response::json(['success' => false, 'message' => 'Group ID required'], 400);
        }
        
        $stmt = $this->db->prepare("DELETE FROM groups WHERE id = ?");
        $stmt->execute([$groupId]);
        
        $this->logAction('delete_group', 'group', $groupId, "Deleted group");
        
        Response::json(['success' => true]);
    }
    
    public function sendNotification()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $targetType = $_POST['target_type'] ?? 'all';
        $targetId = $_POST['target_id'] ?? null;
        
        if (!$title || !$message) {
            Response::json(['success' => false, 'message' => 'Title and message required'], 400);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO system_notifications (title, message, target_type, target_id, sent_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $message, $targetType, $targetId, Session::getUserId()]);
        
        $notificationId = $this->db->lastInsertId();
        
        $this->sendPushNotifications($title, $message, $targetType, $targetId);
        
        $this->logAction('send_notification', 'notification', $notificationId, "Sent notification: {$title}");
        
        Response::json(['success' => true]);
    }
    
    private function sendPushNotifications($title, $message, $targetType, $targetId)
    {
        $whereClause = '';
        $params = [];
        
        if ($targetType === 'user' && $targetId) {
            $whereClause = "WHERE id = ?";
            $params = [$targetId];
        } elseif ($targetType === 'group' && $targetId) {
            $whereClause = "WHERE id IN (SELECT user_id FROM group_members WHERE group_id = ?)";
            $params = [$targetId];
        }
        
        $whereAnd = $whereClause ? $whereClause . ' AND' : 'WHERE';
        $stmt = $this->db->prepare("SELECT push_subscription FROM users {$whereAnd} push_subscription IS NOT NULL");
        $stmt->execute($params);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($subscriptions as $subscription) {
            $this->sendPushNotification($subscription, $title, $message);
        }
    }
    
    private function sendPushNotification($subscription, $title, $message)
    {
        // TODO: Implement Web Push using web-push library
    }
    
    public function logs()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM admin_logs");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $this->db->prepare("
            SELECT al.*, u.first_name, u.last_name, u.email
            FROM admin_logs al
            JOIN users u ON al.admin_id = u.id
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        View::render('admin/logs', [
            'pageTitle' => 'Логи администратора',
            'logs' => $logs,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }
}
