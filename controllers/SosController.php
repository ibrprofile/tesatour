<?php
/**
 * Контроллер SOS-вызовов
 */
class SosController extends BaseController
{
    private $sosModel;
    private $groupModel;
    private $telegram;
    
    public function __construct()
    {
        parent::__construct();
        $this->sosModel = new SosAlert();
        $this->groupModel = new Group();
        $this->telegram = new TelegramBot();
    }
    
    /**
     * Список SOS-вызовов группы
     */
    public function index($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->isMember($group['id'], Session::getUserId())) {
            Session::flash('error', 'Группа не найдена');
            $this->redirect('/groups');
            return;
        }
        
        $alerts = $this->sosModel->getAllByGroup($group['id'], 50);
        
        $this->render('sos/index', [
            'pageTitle' => 'SOS-вызовы',
            'group' => $group,
            'alerts' => $alerts
        ]);
    }
    
    /**
     * Создание SOS-вызова
     */
    public function create($id)
    {
        $group = $this->groupModel->findById((int) $id);
        $userId = Session::getUserId();
        
        if (!$group || !$this->groupModel->isMember($group['id'], $userId)) {
            $this->error('Группа не найдена');
            return;
        }
        
        if ($group['status'] !== GROUP_STATUS_ACTIVE) {
            $this->error('Группа закрыта');
            return;
        }
        
        // Проверяем, нет ли уже активного SOS
        if ($this->sosModel->hasActiveSos($userId, $group['id'])) {
            $this->error('У вас уже есть активный SOS-вызов');
            return;
        }
        
        $data = $this->getPostData();
        
        if (empty($data['latitude']) || empty($data['longitude'])) {
            $this->error('Не удалось определить местоположение');
            return;
        }
        
        // Создаём SOS
        $sosId = $this->sosModel->create([
            'user_id' => $userId,
            'group_id' => $group['id'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'comment' => $data['comment'] ?? null
        ]);
        
        // Отправляем уведомления участникам
        $this->notifyMembers($sosId);
        
        $this->success(['redirect' => '/sos/' . $sosId], 'SOS-сигнал отправлен!');
    }
    
    /**
     * Просмотр SOS-вызова
     */
    public function view($id)
    {
        $sos = $this->sosModel->findById((int) $id);
        
        if (!$sos) {
            Session::flash('error', 'SOS-вызов не найден');
            $this->redirect('/');
            return;
        }
        
        // Проверяем доступ (участник группы или публичный просмотр для Telegram)
        $canResolve = false;
        
        if (Session::isLoggedIn()) {
            $userId = Session::getUserId();
            $canResolve = $this->groupModel->isMember($sos['group_id'], $userId) 
                && $sos['status'] === SOS_STATUS_ACTIVE;
        }
        
        $this->render('sos/view', [
            'pageTitle' => 'SOS-вызов',
            'sos' => $sos,
            'canResolve' => $canResolve
        ], Session::isLoggedIn() ? 'main' : 'guest');
    }
    
    /**
     * Разрешение SOS-вызова
     */
    public function resolve($id)
    {
        $sos = $this->sosModel->findById((int) $id);
        $userId = Session::getUserId();
        
        if (!$sos || !$this->groupModel->isMember($sos['group_id'], $userId)) {
            $this->error('SOS-вызов не найден');
            return;
        }
        
        if ($sos['status'] !== SOS_STATUS_ACTIVE) {
            $this->error('SOS-вызов уже закрыт');
            return;
        }
        
        $this->sosModel->resolve($sos['id'], $userId);
        
        if ($this->isAjax()) {
            $this->success(['redirect' => '/groups/' . $sos['group_id']], 'SOS-вызов закрыт');
        }
        
        Session::flash('success', 'SOS-вызов успешно закрыт');
        $this->redirect('/groups/' . $sos['group_id']);
    }
    
    /**
     * Отправка уведомлений участникам группы
     */
    private function notifyMembers($sosId)
    {
        $sosData = $this->sosModel->getSosNotificationData($sosId);
        
        if (!$sosData) {
            return;
        }
        
        $sos = $this->sosModel->findById($sosId);
        $members = $this->groupModel->getMembers($sos['group_id']);
        
        foreach ($members as $member) {
            if ($member['id'] == $sos['user_id']) {
                continue;
            }
            
            if (!empty($member['telegram_id'])) {
                $this->telegram->sendSosAlert($member['telegram_id'], $sosData);
            }
        }
        
        // Попытка записать push-уведомления в БД (таблица может не существовать)
        try {
            $notificationTitle = 'SOS от ' . $sosData['user_name'];
            $notificationBody = $sosData['comment'] ?: 'Требуется помощь!';
            $notificationData = json_encode([
                'type' => 'sos',
                'sos_id' => $sosId,
                'group_id' => $sos['group_id'],
                'latitude' => $sos['latitude'],
                'longitude' => $sos['longitude']
            ]);
            
            $this->db->query(
                "INSERT INTO push_notifications (user_id, title, body, data, created_at)
                 SELECT id, ?, ?, ?, NOW()
                 FROM users
                 WHERE id IN (
                     SELECT user_id FROM group_members 
                     WHERE group_id = ? AND user_id != ?
                 )",
                [
                    $notificationTitle,
                    $notificationBody,
                    $notificationData,
                    $sos['group_id'],
                    $sos['user_id']
                ]
            );
        } catch (\Exception $e) {
            // Таблица push_notifications может не существовать - не критично
            error_log('Push notification save failed: ' . $e->getMessage());
        }
    }
}
