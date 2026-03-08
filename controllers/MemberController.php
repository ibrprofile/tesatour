<?php
/**
 * Контроллер участников группы
 */
class MemberController extends BaseController
{
    private $groupModel;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->groupModel = new Group();
        $this->userModel = new User();
    }
    
    /**
     * Список участников группы
     */
    public function index($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->isMember($group['id'], Session::getUserId())) {
            Session::flash('error', 'Группа не найдена');
            $this->redirect('/groups');
            return;
        }
        
        $members = $this->groupModel->getMembers($group['id']);
        $userRole = $this->groupModel->getUserRole($group['id'], Session::getUserId());
        
        $this->render('groups/members', [
            'pageTitle' => 'Участники',
            'group' => $group,
            'members' => $members,
            'userRole' => $userRole,
            'canManage' => in_array($userRole, [ROLE_OWNER, ROLE_ADMIN]),
            'canChangeRoles' => $userRole === ROLE_OWNER
        ]);
    }
    
    /**
     * Исключение участника
     */
    public function kick($groupId, $userId)
    {
        $group = $this->groupModel->findById((int) $groupId);
        $currentUserId = Session::getUserId();
        $targetUserId = (int) $userId;
        
        if (!$group || !$this->groupModel->canManageMember($group['id'], $currentUserId, $targetUserId)) {
            if ($this->isAjax()) {
                $this->error('Нет прав для выполнения действия');
            }
            $this->redirect('/groups/' . $groupId . '/members');
            return;
        }
        
        $data = $this->getPostData();
        $addToBlacklist = isset($data['add_to_blacklist']) && $data['add_to_blacklist'];
        $reason = $data['reason'] ?? null;
        
        if ($addToBlacklist) {
            // Добавляем в черный список (автоматически удаляет из группы)
            $this->groupModel->addToBlacklist($group['id'], $targetUserId, $currentUserId, $reason);
            $message = 'Участник исключен и добавлен в черный список';
        } else {
            // Просто удаляем из группы
            $this->groupModel->removeMember($group['id'], $targetUserId);
            $message = 'Участник исключен из группы';
        }
        
        if ($this->isAjax()) {
            $this->success(null, $message);
        }
        
        Session::flash('success', $message);
        $this->redirect('/groups/' . $groupId . '/members');
    }
    
    /**
     * Изменение роли участника
     */
    public function changeRole($groupId, $userId)
    {
        $group = $this->groupModel->findById((int) $groupId);
        $currentUserId = Session::getUserId();
        $targetUserId = (int) $userId;
        
        if (!$group || !$this->groupModel->canChangeRole($group['id'], $currentUserId)) {
            if ($this->isAjax()) {
                $this->error('Только владелец может изменять роли');
            }
            $this->redirect('/groups/' . $groupId . '/members');
            return;
        }
        
        $data = $this->getPostData();
        $newRole = $data['role'] ?? '';
        
        if (!in_array($newRole, [ROLE_ADMIN, ROLE_MEMBER])) {
            if ($this->isAjax()) {
                $this->error('Недопустимая роль');
            }
            $this->redirect('/groups/' . $groupId . '/members');
            return;
        }
        
        // Нельзя менять роль владельца
        $currentRole = $this->groupModel->getUserRole($group['id'], $targetUserId);
        if ($currentRole === ROLE_OWNER) {
            if ($this->isAjax()) {
                $this->error('Нельзя изменить роль владельца');
            }
            $this->redirect('/groups/' . $groupId . '/members');
            return;
        }
        
        $this->groupModel->changeRole($group['id'], $targetUserId, $newRole);
        
        if ($this->isAjax()) {
            $this->success(null, 'Роль изменена');
        }
        
        Session::flash('success', 'Роль участника изменена');
        $this->redirect('/groups/' . $groupId . '/members');
    }
    
    /**
     * Просмотр локации участника
     */
    public function location($groupId, $userId)
    {
        $group = $this->groupModel->findById((int) $groupId);
        $currentUserId = Session::getUserId();
        $targetUserId = (int) $userId;
        
        if (!$group || !$this->groupModel->isMember($group['id'], $currentUserId)) {
            Session::flash('error', 'Группа не найдена');
            $this->redirect('/groups');
            return;
        }
        
        // Проверяем, что целевой пользователь - участник группы
        $member = $this->groupModel->getMember($group['id'], $targetUserId);
        
        if (!$member) {
            Session::flash('error', 'Участник не найден');
            $this->redirect('/groups/' . $groupId . '/members');
            return;
        }
        
        // Получаем историю локаций
        $locationHistory = $this->userModel->getLocationHistory($targetUserId, 100);
        $lastLocation = $this->userModel->getLastLocation($targetUserId);
        
        $this->render('groups/member-location', [
            'pageTitle' => 'Локация участника',
            'group' => $group,
            'member' => $member,
            'locationHistory' => $locationHistory,
            'lastLocation' => $lastLocation
        ]);
    }
}
