<?php

class DangerZoneController extends BaseController
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
        
        $stmt = $this->db->prepare("SELECT * FROM groups WHERE id = ?");
        $stmt->execute([$groupId]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$group) {
            Response::redirect('/groups');
        }
        
        $stmt = $this->db->prepare("
            SELECT dz.*, u.first_name, u.last_name
            FROM danger_zones dz
            JOIN users u ON dz.created_by = u.id
            WHERE dz.group_id = ?
            ORDER BY dz.created_at DESC
        ");
        $stmt->execute([$groupId]);
        $dangerZones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        View::render('danger-zones/index', [
            'pageTitle' => 'Опасные зоны',
            'group' => $group,
            'dangerZones' => $dangerZones,
            'userRole' => $this->getUserRole($groupId)
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
        
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $latitude = floatval($_POST['latitude'] ?? 0);
        $longitude = floatval($_POST['longitude'] ?? 0);
        
        if (empty($title) || !$latitude || !$longitude) {
            Response::json(['success' => false, 'message' => 'Title and coordinates required'], 400);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO danger_zones (group_id, created_by, title, description, latitude, longitude)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $groupId,
            Session::getUserId(),
            $title,
            $description,
            $latitude,
            $longitude
        ]);
        
        $zoneId = $this->db->lastInsertId();
        
        Response::json(['success' => true, 'zone_id' => $zoneId]);
    }
    
    public function update($zoneId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $stmt = $this->db->prepare("SELECT group_id FROM danger_zones WHERE id = ?");
        $stmt->execute([$zoneId]);
        $zone = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$zone) {
            Response::json(['success' => false, 'message' => 'Zone not found'], 404);
        }
        
        $userRole = $this->getUserRole($zone['group_id']);
        if (!in_array($userRole, ['owner', 'admin'])) {
            Response::json(['success' => false, 'message' => 'Permission denied'], 403);
        }
        
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $latitude = floatval($_POST['latitude'] ?? 0);
        $longitude = floatval($_POST['longitude'] ?? 0);
        
        if (empty($title) || !$latitude || !$longitude) {
            Response::json(['success' => false, 'message' => 'Title and coordinates required'], 400);
        }
        
        $stmt = $this->db->prepare("
            UPDATE danger_zones
            SET title = ?, description = ?, latitude = ?, longitude = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $description, $latitude, $longitude, $zoneId]);
        
        Response::json(['success' => true]);
    }
    
    public function delete($zoneId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Invalid request'], 405);
        }
        
        $stmt = $this->db->prepare("SELECT group_id FROM danger_zones WHERE id = ?");
        $stmt->execute([$zoneId]);
        $zone = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$zone) {
            Response::json(['success' => false, 'message' => 'Zone not found'], 404);
        }
        
        $userRole = $this->getUserRole($zone['group_id']);
        if (!in_array($userRole, ['owner', 'admin'])) {
            Response::json(['success' => false, 'message' => 'Permission denied'], 403);
        }
        
        $stmt = $this->db->prepare("DELETE FROM danger_zones WHERE id = ?");
        $stmt->execute([$zoneId]);
        
        Response::json(['success' => true]);
    }
    
    public function getZones($groupId)
    {
        if (!$this->canAccessGroup($groupId)) {
            Response::json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        $stmt = $this->db->prepare("
            SELECT dz.*, u.first_name, u.last_name
            FROM danger_zones dz
            JOIN users u ON dz.created_by = u.id
            WHERE dz.group_id = ?
        ");
        $stmt->execute([$groupId]);
        $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        Response::json(['success' => true, 'zones' => $zones]);
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
