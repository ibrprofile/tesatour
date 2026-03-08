<?php
/**
 * Класс для работы с сессиями
 */
class Session
{
    private static $started = false;
    
    /**
     * Запуск сессии
     */
    public static function start()
    {
        if (self::$started) {
            return;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
        
        self::$started = true;
    }
    
    /**
     * Получение значения из сессии
     */
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Установка значения в сессию
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Проверка наличия ключа
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Удаление значения из сессии
     */
    public static function remove($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }
    
    /**
     * Очистка всей сессии
     */
    public static function clear()
    {
        self::start();
        $_SESSION = [];
    }
    
    /**
     * Уничтожение сессии
     */
    public static function destroy()
    {
        self::start();
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
        self::$started = false;
    }
    
    /**
     * Регенерация ID сессии
     */
    public static function regenerate()
    {
        self::start();
        session_regenerate_id(true);
    }
    
    /**
     * Установка flash-сообщения
     */
    public static function flash($key, $value)
    {
        self::set('_flash_' . $key, $value);
    }
    
    /**
     * Получение и удаление flash-сообщения
     */
    public static function getFlash($key, $default = null)
    {
        $value = self::get('_flash_' . $key, $default);
        self::remove('_flash_' . $key);
        return $value;
    }
    
    /**
     * Проверка авторизации пользователя
     */
    public static function isLoggedIn()
    {
        return self::has('user_id');
    }
    
    /**
     * Получение ID текущего пользователя
     */
    public static function getUserId()
    {
        return self::get('user_id');
    }
    
    /**
     * Установка авторизованного пользователя
     */
    public static function setUser($userId)
    {
        self::regenerate();
        self::set('user_id', $userId);
        self::set('login_time', time());
    }
    
    /**
     * Выход пользователя
     */
    public static function logout()
    {
        self::destroy();
    }
}
