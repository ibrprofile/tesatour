<?php
/**
 * Класс для формирования HTTP ответов
 */
class Response
{
    /**
     * JSON ответ
     */
    public static function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Успешный JSON ответ
     */
    public static function success($data = null, $message = 'Успешно')
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Ответ с ошибкой
     */
    public static function error($message, $code = 400, $errors = [])
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
    
    /**
     * Ответ 401 Unauthorized
     */
    public static function unauthorized($message = 'Требуется авторизация')
    {
        self::error($message, 401);
    }
    
    /**
     * Ответ 403 Forbidden
     */
    public static function forbidden($message = 'Доступ запрещен')
    {
        self::error($message, 403);
    }
    
    /**
     * Ответ 404 Not Found
     */
    public static function notFound($message = 'Не найдено')
    {
        self::error($message, 404);
    }
    
    /**
     * Ответ 500 Server Error
     */
    public static function serverError($message = 'Внутренняя ошибка сервера')
    {
        self::error($message, 500);
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
