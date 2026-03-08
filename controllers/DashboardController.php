<?php
/**
 * Контроллер дашборда
 */
class DashboardController extends BaseController
{
    private $groupModel;
    private $sosModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->groupModel = new Group();
        $this->sosModel = new SosAlert();
    }
    
    /**
     * Главная страница дашборда
     */
    public function index()
    {
        $userId = Session::getUserId();
        
        // Получаем активные группы пользователя
        $groups = $this->groupModel->getUserActiveGroups($userId);
        
        // Собираем активные SOS по всем группам
        $activeAlerts = [];
        foreach ($groups as $group) {
            $alerts = $this->sosModel->getActiveByGroup($group['id']);
            foreach ($alerts as $alert) {
                $alert['group_name'] = $group['name'];
                $activeAlerts[] = $alert;
            }
        }
        
        // Сортируем по времени создания
        usort($activeAlerts, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        $this->render('dashboard/index', [
            'pageTitle' => 'Главная',
            'groups' => $groups,
            'activeAlerts' => $activeAlerts,
            'groupsCount' => count($groups)
        ]);
    }
}
