<?php
/**
 * Контроллер групп
 */
class GroupController extends BaseController
{
    private $groupModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->groupModel = new Group();
    }
    
    /**
     * Список групп пользователя
     */
    public function index()
    {
        $groups = $this->groupModel->getUserGroups(Session::getUserId());
        
        $this->render('groups/index', [
            'pageTitle' => 'Мои группы',
            'groups' => $groups
        ]);
    }
    
    /**
     * Форма создания группы
     */
    public function createForm()
    {
        $this->render('groups/create', [
            'pageTitle' => 'Создать группу'
        ]);
    }
    
    /**
     * Создание группы
     */
    public function create()
    {
        $data = $this->getPostData();
        $user = $this->getUser();
        
        // Check group limits based on account type
        $accountType = $user['account_type'] ?? 'amateur';
        
        if ($accountType === 'amateur') {
            // Amateur can only have 1 active group (as owner)
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) FROM groups WHERE owner_id = ? AND status = 'active'");
            $stmt->execute([Session::getUserId()]);
            $activeGroupCount = $stmt->fetchColumn();
            
            if ($activeGroupCount >= 1) {
                if ($this->isAjax()) {
                    $this->error('На тарифе "Любитель" можно создать только 1 активную группу. Оформите подписку турагентства для безлимитных групп.', 403);
                    return;
                }
                Session::flash('error', 'На тарифе "Любитель" можно создать только 1 активную группу.');
                $this->redirect('/groups');
                return;
            }
        } elseif ($accountType === 'agency') {
            // Agency requires active subscription
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND status = 'active' AND end_date > NOW()");
            $stmt->execute([Session::getUserId()]);
            $hasSubscription = $stmt->fetchColumn() > 0;
            
            if (!$hasSubscription) {
                if ($this->isAjax()) {
                    $this->error('Для создания групп необходима активная подписка. Оформите подписку в разделе "Подписка".', 403);
                    return;
                }
                Session::flash('error', 'Для создания групп турагентством необходима активная подписка.');
                $this->redirect('/subscription');
                return;
            }
        }
        
        $validator = $this->validate($data, [
            'name' => 'required|min:3|max:100',
            'description' => 'max:500'
        ]);
        
        if (!$validator->validate()) {
            if ($this->isAjax()) {
                $this->error($validator->getFirstError(), 400, $validator->getErrors());
            }
            Session::flash('error', $validator->getFirstError());
            $this->redirect('/groups/create');
            return;
        }
        
        $groupId = $this->groupModel->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'owner_id' => Session::getUserId()
        ]);
        
        if ($this->isAjax()) {
            $this->success(['redirect' => '/groups/' . $groupId], 'Группа создана');
        }
        
        Session::flash('success', 'Группа успешно создана');
        $this->redirect('/groups/' . $groupId);
    }
    
    /**
     * Просмотр группы
     */
    public function show($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group) {
            Session::flash('error', 'Группа не найдена');
            $this->redirect('/groups');
            return;
        }
        
        $userId = Session::getUserId();
        
        if (!$this->groupModel->isMember($group['id'], $userId)) {
            Session::flash('error', 'Вы не являетесь участником этой группы');
            $this->redirect('/groups');
            return;
        }
        
        $members = $this->groupModel->getMembers($group['id']);
        $userRole = $this->groupModel->getUserRole($group['id'], $userId);
        $canEdit = $this->groupModel->canEdit($group['id'], $userId);
        $canClose = $this->groupModel->canClose($group['id'], $userId);
        
        // SOS вызовы
        $sosModel = new SosAlert();
        $activeAlerts = $sosModel->getActiveByGroup($group['id']);
        
        // Активный план-лист
        $routeModel = new RoutePlan();
        $activeRoute = $routeModel->getActiveByGroup($group['id']);
        
        $this->render('groups/show', [
            'pageTitle' => $group['name'],
            'group' => $group,
            'members' => $members,
            'userRole' => $userRole,
            'canEdit' => $canEdit,
            'canClose' => $canClose,
            'activeAlerts' => $activeAlerts,
            'activeRoute' => $activeRoute,
            'inviteUrl' => $this->groupModel->getInviteUrl($group['invite_code'])
        ]);
    }
    
    /**
     * Обновление группы
     */
    public function update($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Нет прав для редактирования');
            }
            $this->redirect('/groups');
            return;
        }
        
        $data = $this->getPostData();
        
        $validator = $this->validate($data, [
            'name' => 'required|min:3|max:100',
            'description' => 'max:500'
        ]);
        
        if (!$validator->validate()) {
            if ($this->isAjax()) {
                $this->error($validator->getFirstError(), 400, $validator->getErrors());
            }
            Session::flash('error', $validator->getFirstError());
            $this->redirect('/groups/' . $id);
            return;
        }
        
        $this->groupModel->update($group['id'], [
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);
        
        if ($this->isAjax()) {
            $this->success(null, 'Группа обновлена');
        }
        
        Session::flash('success', 'Группа успешно обновлена');
        $this->redirect('/groups/' . $id);
    }
    
    /**
     * Закрытие группы
     */
    public function close($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canClose($group['id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Нет прав для закрытия группы');
            }
            $this->redirect('/groups');
            return;
        }
        
        $this->groupModel->close($group['id']);
        
        if ($this->isAjax()) {
            $this->success(['redirect' => '/groups'], 'Группа закрыта');
        }
        
        Session::flash('success', 'Группа успешно закрыта');
        $this->redirect('/groups');
    }
    
    /**
     * Выход из группы
     */
    public function leave($id)
    {
        $group = $this->groupModel->findById((int) $id);
        $userId = Session::getUserId();
        
        if (!$group || !$this->groupModel->isMember($group['id'], $userId)) {
            if ($this->isAjax()) {
                $this->error('Вы не являетесь участником группы');
            }
            $this->redirect('/groups');
            return;
        }
        
        // Владелец не может выйти
        $role = $this->groupModel->getUserRole($group['id'], $userId);
        if ($role === ROLE_OWNER) {
            if ($this->isAjax()) {
                $this->error('Владелец не может покинуть группу. Закройте группу или передайте права.');
            }
            Session::flash('error', 'Владелец не может покинуть группу');
            $this->redirect('/groups/' . $id);
            return;
        }
        
        $this->groupModel->removeMember($group['id'], $userId);
        
        if ($this->isAjax()) {
            $this->success(['redirect' => '/groups'], 'Вы покинули группу');
        }
        
        Session::flash('success', 'Вы покинули группу');
        $this->redirect('/groups');
    }
    
    /**
     * Страница приглашения
     */
    public function invite($code)
    {
        $group = $this->groupModel->findByInviteCode($code);
        
        if (!$group) {
            Session::flash('error', 'Приглашение недействительно или группа закрыта');
            $this->redirect('/');
            return;
        }
        
        // Если авторизован и уже в группе
        if (Session::isLoggedIn()) {
            $userId = Session::getUserId();
            if ($this->groupModel->isMember($group['id'], $userId)) {
                $this->redirect('/groups/' . $group['id']);
                return;
            }
        }
        
        $this->render('groups/invite', [
            'pageTitle' => 'Приглашение в группу',
            'group' => $group,
            'inviteCode' => $code
        ], Session::isLoggedIn() ? 'main' : 'guest');
    }
    
    /**
     * Присоединение по приглашению
     */
    public function joinByInvite($code)
    {
        $group = $this->groupModel->findByInviteCode($code);
        
        if (!$group) {
            if ($this->isAjax()) {
                $this->error('Приглашение недействительно');
            }
            Session::flash('error', 'Приглашение недействительно');
            $this->redirect('/');
            return;
        }
        
        // Если не авторизован - на регистрацию с сохранением кода
        if (!Session::isLoggedIn()) {
            Session::set('pending_invite', $code);
            $this->redirect('/register');
            return;
        }
        
        $userId = Session::getUserId();
        
        // Проверяем черный список
        if ($this->groupModel->isBlacklisted($group['id'], $userId)) {
            if ($this->isAjax()) {
                $this->error('Вы не можете вступить в эту группу');
            }
            Session::flash('error', 'Вы не можете вступить в эту группу');
            $this->redirect('/groups');
            return;
        }
        
        // Проверяем, не в группе ли уже
        if ($this->groupModel->isMember($group['id'], $userId)) {
            if ($this->isAjax()) {
                $this->success(['redirect' => '/groups/' . $group['id']], 'Вы уже в группе');
            }
            $this->redirect('/groups/' . $group['id']);
            return;
        }
        
        // Если требуется одобрение
        if ($group['require_approval']) {
            // Проверяем, нет ли уже заявки
            if ($this->groupModel->hasPendingRequest($group['id'], $userId)) {
                if ($this->isAjax()) {
                    $this->success(['redirect' => '/groups'], 'Ваша заявка уже отправлена');
                }
                Session::flash('info', 'Ваша заявка уже отправлена и ожидает одобрения');
                $this->redirect('/groups');
                return;
            }
            
            // Создаем заявку
            $this->groupModel->createJoinRequest($group['id'], $userId);
            
            if ($this->isAjax()) {
                $this->success(['redirect' => '/groups'], 'Заявка отправлена');
            }
            
            Session::flash('success', 'Заявка на вступление отправлена. Дождитесь одобрения администратора.');
            $this->redirect('/groups');
            return;
        }
        
        // Добавляем в группу напрямую
        $this->groupModel->addMember($group['id'], $userId, ROLE_MEMBER);
        
        if ($this->isAjax()) {
            $this->success(['redirect' => '/groups/' . $group['id']], 'Вы присоединились к группе');
        }
        
        Session::flash('success', 'Вы успешно присоединились к группе "' . e($group['name']) . '"');
        $this->redirect('/groups/' . $group['id']);
    }
    
    // === Управление пригласительными ссылками ===
    
    /**
     * Страница управления ссылками
     */
    public function inviteLinks($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            Session::flash('error', 'Нет доступа');
            $this->redirect('/groups');
            return;
        }
        
        $pendingRequests = $this->groupModel->getPendingRequests($group['id']);
        
        $this->render('groups/invite-links', [
            'pageTitle' => 'Приглашения',
            'group' => $group,
            'pendingRequests' => $pendingRequests,
            'inviteUrl' => $this->groupModel->getInviteUrl($group['invite_code'])
        ]);
    }
    
    /**
     * Переключение режима одобрения
     */
    public function toggleApproval($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Нет доступа');
            }
            $this->redirect('/groups');
            return;
        }
        
        $requireApproval = $this->getPostData()['require_approval'] ?? 0;
        $this->groupModel->setRequireApproval($group['id'], (bool) $requireApproval);
        
        if ($this->isAjax()) {
            $this->success(null, 'Настройки обновлены');
        }
        
        Session::flash('success', 'Настройки обновлены');
        $this->redirect('/groups/' . $id . '/invites');
    }
    
    /**
     * Одобрение заявки
     */
    public function approveRequest($id, $requestId)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Нет доступа');
            }
            $this->redirect('/groups');
            return;
        }
        
        $this->groupModel->approveJoinRequest((int) $requestId, Session::getUserId());
        
        if ($this->isAjax()) {
            $this->success(null, 'Заявка одобрена');
        }
        
        Session::flash('success', 'Заявка одобрена');
        $this->redirect('/groups/' . $id . '/invites');
    }
    
    /**
     * Отклонение заявки
     */
    public function rejectRequest($id, $requestId)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Нет доступа');
            }
            $this->redirect('/groups');
            return;
        }
        
        $this->groupModel->rejectJoinRequest((int) $requestId, Session::getUserId());
        
        if ($this->isAjax()) {
            $this->success(null, 'Заявка отклонена');
        }
        
        Session::flash('success', 'Заявка отклонена');
        $this->redirect('/groups/' . $id . '/invites');
    }
    
    // === Черный список ===
    
    /**
     * Страница черного списка
     */
    public function blacklist($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            Session::flash('error', 'Нет доступа');
            $this->redirect('/groups');
            return;
        }
        
        $blacklist = $this->groupModel->getBlacklist($group['id']);
        
        $this->render('groups/blacklist', [
            'pageTitle' => 'Черный список',
            'group' => $group,
            'blacklist' => $blacklist
        ]);
    }
    
    /**
     * Добавление в черный список (при удалении участника)
     */
    public function addToBlacklist($id, $userId)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Нет доступа');
            }
            $this->redirect('/groups');
            return;
        }
        
        $data = $this->getPostData();
        $this->groupModel->addToBlacklist($group['id'], (int) $userId, Session::getUserId(), $data['reason'] ?? null);
        
        if ($this->isAjax()) {
            $this->success(null, 'Пользователь добавлен в черный список');
        }
        
        Session::flash('success', 'Пользователь добавлен в черный список');
        $this->redirect('/groups/' . $id . '/blacklist');
    }
    
    /**
     * Удаление из черного списка
     */
    public function removeFromBlacklist($id, $userId)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Нет доступа');
            }
            $this->redirect('/groups');
            return;
        }
        
        $this->groupModel->removeFromBlacklist($group['id'], (int) $userId);
        
        if ($this->isAjax()) {
            $this->success(null, 'Пользователь удален из черного списка');
        }
        
        Session::flash('success', 'Пользователь удален из черного списка');
        $this->redirect('/groups/' . $id . '/blacklist');
    }
    
    // === Настройки группы ===
    
    public function settings($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            Session::flash('error', 'Нет доступа');
            $this->redirect('/groups');
            return;
        }
        
        $this->render('groups/settings', [
            'pageTitle' => 'Настройки группы',
            'group' => $group
        ]);
    }
    
    public function updateSettings($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Нет доступа');
            }
            $this->redirect('/groups');
            return;
        }
        
        $data = $this->getPostData();
        $this->groupModel->update($group['id'], [
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);
        
        if ($this->isAjax()) {
            $this->success(null, 'Настройки обновлены');
        }
        
        Session::flash('success', 'Настройки обновлены');
        $this->redirect('/groups/' . $id . '/settings');
    }
}
