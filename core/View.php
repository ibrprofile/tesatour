<?php
/**
 * Класс для рендеринга представлений
 */
class View
{
    private static $data = [];
    
    /**
     * Рендеринг представления
     */
    public static function render($view, $data = [], $layout = 'main')
    {
        self::$data = array_merge(self::$data, $data);
        extract(self::$data);
        
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$view}");
        }
        
        if ($layout) {
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            
            $layoutPath = VIEWS_PATH . '/layouts/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                echo $content;
            }
        } else {
            require $viewPath;
        }
    }
    
    /**
     * Рендеринг частичного представления
     */
    public static function partial($view, $data = [])
    {
        extract(array_merge(self::$data, $data));
        
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        }
    }
    
    /**
     * Установка глобальных данных
     */
    public static function share($key, $value)
    {
        self::$data[$key] = $value;
    }
    
    /**
     * Получение глобальных данных
     */
    public static function getData($key = null)
    {
        if ($key === null) {
            return self::$data;
        }
        return self::$data[$key] ?? null;
    }
    
    /**
     * Экранирование HTML
     */
    public static function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Вывод с экранированием
     */
    public static function e($value)
    {
        echo self::escape($value);
    }
}

/**
 * Хелпер для экранирования
 */
function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Хелпер для генерации URL
 */
function url($path = '')
{
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Хелпер для URL ассетов
 */
function asset($path)
{
    return ASSETS_URL . '/' . ltrim($path, '/');
}

/**
 * Хелпер для URL загруженных файлов
 */
function uploads($path)
{
    return UPLOADS_URL . '/' . ltrim($path, '/');
}

/**
 * Хелпер для проверки активного маршрута
 */
function isActive($path)
{
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return $currentPath === $path || strpos($currentPath, $path) === 0;
}

/**
 * Хелпер для форматирования даты
 */
function formatDate($date, $format = 'd.m.Y')
{
    return date($format, strtotime($date));
}

/**
 * Хелпер для форматирования даты и времени
 */
function formatDateTime($datetime, $format = 'd.m.Y H:i')
{
    return date($format, strtotime($datetime));
}

/**
 * Хелпер для относительного времени
 */
function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'только что';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' ' . pluralize($minutes, 'минуту', 'минуты', 'минут') . ' назад';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' ' . pluralize($hours, 'час', 'часа', 'часов') . ' назад';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' ' . pluralize($days, 'день', 'дня', 'дней') . ' назад';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Склонение числительных
 */
function pluralize($number, $one, $few, $many)
{
    $number = abs($number) % 100;
    $lastDigit = $number % 10;
    
    if ($number > 10 && $number < 20) {
        return $many;
    }
    
    if ($lastDigit > 1 && $lastDigit < 5) {
        return $few;
    }
    
    if ($lastDigit === 1) {
        return $one;
    }
    
    return $many;
}
