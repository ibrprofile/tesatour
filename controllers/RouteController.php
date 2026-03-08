<?php
/**
 * Контроллер план-листов маршрутов
 */
class RouteController extends BaseController
{
    private $routeModel;
    private $groupModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->routeModel = new RoutePlan();
        $this->groupModel = new Group();
    }
    
    /**
     * Список маршрутов группы
     */
    public function index($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->isMember($group['id'], Session::getUserId())) {
            Session::flash('error', 'Группа не найдена');
            $this->redirect('/groups');
            return;
        }
        
        $routes = $this->routeModel->getByGroup($group['id']);
        $canCreate = $this->groupModel->canEdit($group['id'], Session::getUserId());
        
        $this->render('routes/index', [
            'pageTitle' => 'Маршруты',
            'group' => $group,
            'routes' => $routes,
            'canCreate' => $canCreate
        ]);
    }
    
    /**
     * Форма создания маршрута
     */
    public function createForm($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            Session::flash('error', 'Нет прав для создания маршрута');
            $this->redirect('/groups');
            return;
        }
        
        $this->render('routes/create', [
            'pageTitle' => 'Новый маршрут',
            'group' => $group
        ]);
    }
    
    /**
     * Создание маршрута
     */
    public function create($id)
    {
        $group = $this->groupModel->findById((int) $id);
        
        if (!$group || !$this->groupModel->canEdit($group['id'], Session::getUserId())) {
            $this->error('Нет прав для создания маршрута');
            return;
        }
        
        $data = $this->getPostData();
        
        $validator = $this->validate($data, [
            'title' => 'required|min:3|max:100',
            'description' => 'max:500'
        ]);
        
        if (!$validator->validate()) {
            if ($this->isAjax()) {
                $this->error($validator->getFirstError(), 400, $validator->getErrors());
            }
            Session::flash('error', $validator->getFirstError());
            $this->redirect('/groups/' . $id . '/routes/create');
            return;
        }
        
        $routeId = $this->routeModel->create([
            'group_id' => $group['id'],
            'created_by' => Session::getUserId(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null
        ]);
        
        if ($this->isAjax()) {
            $this->success(['redirect' => '/routes/' . $routeId], 'Маршрут создан');
        }
        
        Session::flash('success', 'Маршрут успешно создан');
        $this->redirect('/routes/' . $routeId);
    }
    
    /**
     * Просмотр маршрута
     */
    public function show($id)
    {
        $route = $this->routeModel->findById((int) $id);
        
        if (!$route) {
            Session::flash('error', 'Маршрут не найден');
            $this->redirect('/groups');
            return;
        }
        
        if (!$this->groupModel->isMember($route['group_id'], Session::getUserId())) {
            Session::flash('error', 'Нет доступа к маршруту');
            $this->redirect('/groups');
            return;
        }
        
        $points = $this->routeModel->getPoints($route['id']);
        $canEdit = $this->groupModel->canEdit($route['group_id'], Session::getUserId());
        
        $this->render('routes/show', [
            'pageTitle' => $route['title'],
            'route' => $route,
            'points' => $points,
            'canEdit' => $canEdit
        ]);
    }
    
    /**
     * Обновление маршрута
     */
    public function update($id)
    {
        $route = $this->routeModel->findById((int) $id);
        
        if (!$route || !$this->groupModel->canEdit($route['group_id'], Session::getUserId())) {
            $this->error('Нет прав для редактирования');
            return;
        }
        
        $data = $this->getPostData();
        
        $validator = $this->validate($data, [
            'title' => 'required|min:3|max:100',
            'description' => 'max:500'
        ]);
        
        if (!$validator->validate()) {
            if ($this->isAjax()) {
                $this->error($validator->getFirstError(), 400, $validator->getErrors());
            }
            Session::flash('error', $validator->getFirstError());
            $this->redirect('/routes/' . $id);
            return;
        }
        
        $this->routeModel->update($route['id'], [
            'title' => $data['title'],
            'description' => $data['description'] ?? null
        ]);
        
        // Активация/деактивация
        if (isset($data['is_active'])) {
            if ($data['is_active']) {
                $this->routeModel->activate($route['id']);
            } else {
                $this->routeModel->deactivate($route['id']);
            }
        }
        
        if ($this->isAjax()) {
            $this->success(null, 'Маршрут обновлен');
        }
        
        Session::flash('success', 'Маршрут успешно обновлен');
        $this->redirect('/routes/' . $id);
    }
    
    /**
     * Удаление маршрута
     */
    public function delete($id)
    {
        $route = $this->routeModel->findById((int) $id);
        
        if (!$route || !$this->groupModel->canEdit($route['group_id'], Session::getUserId())) {
            $this->error('Нет прав для удаления');
            return;
        }
        
        $groupId = $route['group_id'];
        $this->routeModel->delete($route['id']);
        
        if ($this->isAjax()) {
            $this->success(['redirect' => '/groups/' . $groupId . '/routes'], 'Маршрут удален');
        }
        
        Session::flash('success', 'Маршрут удален');
        $this->redirect('/groups/' . $groupId . '/routes');
    }
    
    /**
     * Добавление точки
     */
    public function addPoint($id)
    {
        $route = $this->routeModel->findById((int) $id);
        
        if (!$route || !$this->groupModel->canEdit($route['group_id'], Session::getUserId())) {
            $this->error('Нет прав для редактирования');
            return;
        }
        
        $data = $this->getPostData();
        
        if (empty($data['title']) || empty($data['latitude']) || empty($data['longitude'])) {
            $this->error('Заполните все обязательные поля');
            return;
        }
        
        $pointId = $this->routeModel->addPoint($route['id'], [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude']
        ]);
        
        $this->success(['id' => $pointId], 'Точка добавлена');
    }
    
    /**
     * Удаление точки
     */
    public function deletePoint($id)
    {
        $point = $this->routeModel->findPointById((int) $id);
        
        if (!$point || !$this->groupModel->canEdit($point['group_id'], Session::getUserId())) {
            $this->error('Нет прав для удаления');
            return;
        }
        
        $this->routeModel->deletePoint($point['id']);
        
        $this->success(null, 'Точка удалена');
    }
    
    /**
     * Отметка точки как пройденной
     */
    public function markPointCompleted($pointId)
    {
        $point = $this->routeModel->findPointById((int) $pointId);
        
        if (!$point || !$this->groupModel->isMember($point['group_id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Нет доступа');
            }
            return;
        }
        
        // Проверяем, можно ли отмечать (только админы)
        if (!$this->groupModel->canEdit($point['group_id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Только администраторы могут отмечать точки');
            }
            return;
        }
        
        $this->routeModel->markPointAsCompleted($point['id'], Session::getUserId());
        
        if ($this->isAjax()) {
            $this->success(null, 'Точка отмечена как пройденная');
        }
        
        Session::flash('success', 'Точка отмечена как пройденная');
        $this->redirect('/routes/' . $point['route_plan_id']);
    }
    
    /**
     * Снятие отметки с точки
     */
    public function unmarkPointCompleted($pointId)
    {
        $point = $this->routeModel->findPointById((int) $pointId);
        
        if (!$point || !$this->groupModel->canEdit($point['group_id'], Session::getUserId())) {
            if ($this->isAjax()) {
                $this->error('Нет доступа');
            }
            return;
        }
        
        $this->routeModel->unmarkPointAsCompleted($point['id']);
        
        if ($this->isAjax()) {
            $this->success(null, 'Отметка снята');
        }
        
        Session::flash('success', 'Отметка снята');
        $this->redirect('/routes/' . $point['route_plan_id']);
    }
}
