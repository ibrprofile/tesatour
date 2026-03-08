<?php
/**
 * Класс маршрутизации
 * Обрабатывает HTTP запросы и направляет к соответствующим контроллерам
 */
class Router
{
    private $routes = [];
    private $middleware = [];
    
    /**
     * Регистрация GET маршрута
     */
    public function get($path, $handler, $middleware = [])
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    /**
     * Регистрация POST маршрута
     */
    public function post($path, $handler, $middleware = [])
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    /**
     * Добавление маршрута
     */
    private function addRoute($method, $path, $handler, $middleware)
    {
        $pattern = $this->pathToPattern($path);
        $this->routes[$method][$pattern] = [
            'handler' => $handler,
            'middleware' => $middleware,
            'path' => $path
        ];
        return $this;
    }
    
    /**
     * Преобразование пути в регулярное выражение
     */
    private function pathToPattern($path)
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Добавление глобального middleware
     */
    public function addMiddleware($middleware)
    {
        $this->middleware[] = $middleware;
        return $this;
    }
    
    /**
     * Обработка запроса
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();
        
        // Выполнение глобальных middleware
        foreach ($this->middleware as $middleware) {
            $result = call_user_func($middleware);
            if ($result === false) {
                return;
            }
        }
        
        // Поиск маршрута
        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }
        
        foreach ($this->routes[$method] as $pattern => $route) {
            if (preg_match($pattern, $uri, $matches)) {
                // Извлечение параметров
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Выполнение middleware маршрута
                foreach ($route['middleware'] as $middleware) {
                    $result = call_user_func($middleware);
                    if ($result === false) {
                        return;
                    }
                }
                
                // Вызов обработчика
                $this->callHandler($route['handler'], $params);
                return;
            }
        }
        
        $this->notFound();
    }
    
    /**
     * Получение URI из запроса
     */
    private function getUri()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rawurldecode($uri);
        return $uri === '' ? '/' : $uri;
    }
    
    /**
     * Вызов обработчика маршрута
     */
    private function callHandler($handler, $params)
    {
        if (is_array($handler)) {
            [$controller, $method] = $handler;
            if (is_string($controller)) {
                $controller = new $controller();
            }
            call_user_func_array([$controller, $method], $params);
        } else {
            call_user_func_array($handler, $params);
        }
    }
    
    /**
     * Обработка 404 ошибки
     */
    private function notFound()
    {
        http_response_code(404);
        require VIEWS_PATH . '/errors/404.php';
    }
    
    /**
     * Редирект
     */
    public static function redirect($url, $code = 302)
    {
        http_response_code($code);
        header('Location: ' . $url);
        exit;
    }
}
