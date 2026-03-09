<?php
/**
 * API Authentication Middleware
 * Проверяет наличие и валидность API токена
 */
class ApiAuthMiddleware
{
    private $apiKey;
    private $startTime;
    
    public function __construct()
    {
        $this->apiKey = new ApiKey();
        $this->startTime = microtime(true);
    }
    
    /**
     * Основной метод middleware
     * Проверяет наличие и корректность токена
     * @return array|false - возвращает данные ключа или false
     */
    public function authenticate()
    {
        // Получаем токен из заголовка Authorization
        $token = $this->getTokenFromHeader();
        
        if (!$token) {
            $this->respondUnauthorized('API token не найден. Используйте заголовок: Authorization: Bearer {token}');
            return false;
        }
        
        // Валидируем токен
        $apiKeyData = $this->apiKey->validateToken($token);
        
        if (!$apiKeyData) {
            $this->respondUnauthorized('Неверный API токен');
            return false;
        }
        
        return $apiKeyData;
    }
    
    /**
     * Проверка разрешений
     * @param array $apiKeyData - данные ключа
     * @param string $scope - требуемое разрешение
     * @return bool
     */
    public function hasScope($apiKeyData, $scope)
    {
        return $this->apiKey->hasScope($apiKeyData, $scope);
    }
    
    /**
     * Проверка rate limit
     * @param int $apiKeyId
     * @param int $maxRequests
     * @return bool
     */
    public function checkRateLimit($apiKeyId, $maxRequests = 1000)
    {
        if (!$this->apiKey->checkRateLimit($apiKeyId, $maxRequests)) {
            $this->respondTooManyRequests('Превышен лимит запросов. Подождите 1 минуту');
            return false;
        }
        return true;
    }
    
    /**
     * Логирование запроса
     * @param int|null $apiKeyId
     * @param string $endpoint
     * @param string $method
     * @param int $statusCode
     * @param string|null $error
     */
    public function logRequest($apiKeyId, $endpoint, $method, $statusCode, $error = null)
    {
        $responseTimeMs = intval((microtime(true) - $this->startTime) * 1000);
        
        $this->apiKey->logRequest(
            $apiKeyId,
            $endpoint,
            $method,
            $statusCode,
            $responseTimeMs,
            $error
        );
    }
    
    /**
     * Получение токена из заголовка Authorization
     * @return string|null
     */
    private function getTokenFromHeader()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (!preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return null;
        }
        
        return trim($matches[1]);
    }
    
    /**
     * Ответ: Неавторизирован
     * @param string $message
     */
    private function respondUnauthorized($message = 'Неавторизирован')
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'UNAUTHORIZED',
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Ответ: Доступ запрещен
     * @param string $message
     */
    public function respondForbidden($message = 'Доступ запрещен')
    {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'FORBIDDEN',
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Ответ: Слишком много запросов
     * @param string $message
     */
    private function respondTooManyRequests($message = 'Слишком много запросов')
    {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        header('Retry-After: 60');
        echo json_encode([
            'success' => false,
            'error' => 'RATE_LIMIT_EXCEEDED',
            'message' => $message,
            'retry_after' => 60,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
