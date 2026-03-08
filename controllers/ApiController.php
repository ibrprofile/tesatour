<?php
/**
 * API контроллер
 */
class ApiController extends BaseController
{
    private $userModel;
    private $groupModel;
    private $sosModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->groupModel = new Group();
        $this->sosModel = new SosAlert();
    }
    
    /**
     * Обновление геолокации
     */
    public function updateLocation()
    {
        $data = $this->getPostData();
        
        if (empty($data['latitude']) || empty($data['longitude'])) {
            $this->error('Некорректные координаты');
            return;
        }
        
        $this->userModel->updateLocation(
            Session::getUserId(),
            (float) $data['latitude'],
            (float) $data['longitude']
        );
        
        $this->success(null, 'Локация обновлена');
    }
    
    /**
     * История локаций пользователя
     */
    public function locationHistory($userId)
    {
        $targetUserId = (int) $userId;
        $currentUserId = Session::getUserId();
        
        // Проверяем, что пользователи в одной группе
        $hasAccess = false;
        
        if ($currentUserId === $targetUserId) {
            $hasAccess = true;
        } else {
            // Проверяем общие группы
            $currentUserGroups = $this->groupModel->getUserActiveGroups($currentUserId);
            
            foreach ($currentUserGroups as $group) {
                if ($this->groupModel->isMember($group['id'], $targetUserId)) {
                    $hasAccess = true;
                    break;
                }
            }
        }
        
        if (!$hasAccess) {
            $this->error('Нет доступа к истории локаций', 403);
            return;
        }
        
        $limit = (int) $this->getQuery('limit', 50);
        $offset = (int) $this->getQuery('offset', 0);
        
        $history = $this->userModel->getLocationHistory($targetUserId, $limit, $offset);
        $lastLocation = $this->userModel->getLastLocation($targetUserId);
        
        $this->success([
            'history' => $history,
            'lastLocation' => $lastLocation
        ]);
    }
    
    /**
     * Активные SOS-вызовы группы
     */
    public function activeSos($groupId)
    {
        $group = $this->groupModel->findById((int) $groupId);
        
        if (!$group || !$this->groupModel->isMember($group['id'], Session::getUserId())) {
            $this->error('Группа не найдена', 404);
            return;
        }
        
        $alerts = $this->sosModel->getActiveByGroup($group['id']);
        
        $this->success(['alerts' => $alerts]);
    }
    
    /**
     * Локации участников группы
     */
    public function membersLocations($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->isMember($group['id'], Session::getUserId())) {
            $this->error('Группа не найдена', 404);
            return;
        }
        
        $locations = $this->groupModel->getMembersLocations($group['id']);
        
        // Добавляем URL аватаров
        foreach ($locations as &$loc) {
            $loc['avatar_url'] = $this->userModel->getAvatarUrl($loc['avatar']);
        }
        
        $this->success(['locations' => $locations]);
    }
}
