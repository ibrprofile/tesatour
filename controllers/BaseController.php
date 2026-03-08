<?php
/**
 * Базовый контроллер
 */
abstract class BaseController
{
    protected $db;
    protected $currentUser = null;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadCurrentUser();
    }
    
    /**
     * Загрузка текущего пользователя
     */
    protected function loadCurrentUser()
    {
        if (Session::isLoggedIn()) {
            $userModel = new User();
            $this->currentUser = $userModel->findById(Session::getUserId());
            
            // Делаем пользователя доступным во views
            View::share('currentUser', $this->currentUser);
        }
    }
    
    /**
     * Получение текущего пользователя
     */
    protected function getUser()
    {
        return $this->currentUser;
    }
    
    /**
     * Проверка авторизации
     */
    protected function requireAuth()
    {
        if (!Session::isLoggedIn()) {
            if ($this->isAjax()) {
                Response::unauthorized();
            }
            Router::redirect('/login');
        }
    }
    
    /**
     * Требование гостя (не авторизованного)
     */
    protected function requireGuest()
    {
        if (Session::isLoggedIn()) {
            Router::redirect('/dashboard');
        }
    }
    
    /**
     * Проверка AJAX запроса
     */
    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
    
    /**
     * Получение POST данных
     */
    protected function getPostData()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }
        
        return $_POST;
    }
    
    /**
     * Получение GET параметра
     */
    protected function getQuery($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Валидация данных
     */
    protected function validate($data, $rules)
    {
        $validator = new Validator($data, $rules);
        $validator->validate();
        return $validator;
    }
    
    /**
     * Рендер view
     */
    protected function render($view, $data = [], $layout = 'main')
    {
        View::render($view, $data, $layout);
    }
    
    /**
     * JSON ответ
     */
    protected function json($data, $code = 200)
    {
        Response::json($data, $code);
    }
    
    /**
     * Успешный ответ
     */
    protected function success($data = null, $message = 'Успешно')
    {
        Response::success($data, $message);
    }
    
    /**
     * Ответ с ошибкой
     */
    protected function error($message, $code = 400, $errors = [])
    {
        Response::error($message, $code, $errors);
    }
    
    /**
     * Редирект
     */
    protected function redirect($url)
    {
        Router::redirect($url);
    }
    
    /**
     * Flash сообщение и редирект
     */
    protected function redirectWithMessage($url, $message, $type = 'success')
    {
        Session::flash($type, $message);
        $this->redirect($url);
    }
}
