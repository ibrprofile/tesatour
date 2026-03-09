<?php
/**
 * Базовый API контроллер
 * Предоставляет общие методы для всех API контроллеров
 */
abstract class ApiBaseController
{
    protected $db;
    protected $apiAuth;
    protected $apiKeyData;
    protected $groupId;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->apiAuth = new ApiAuthMiddleware();
        
        // Выполняем аутентификацию
        $this->apiKeyData = $this->apiAuth->authenticate();
        
        if (!$this->apiKeyData) {
            exit; // ApiAuthMiddleware уже вывел ошибку
        }
        
        $this->groupId = $this->apiKeyData['group_id'];
    }
    
    /**
     * Проверка разрешения
     * @param string $scope
     * @return bool
     */
    protected function requireScope($scope)
    {
        if (!$this->apiAuth->hasScope($this->apiKeyData, $scope)) {
            $this->apiAuth->respondForbidden("Недостаточно разрешений. Требуется: $scope");
            return false;
        }
        return true;
    }
    
    /**
     * Проверка rate limit
     * @param int $maxRequests
     */
    protected function checkRateLimit($maxRequests = 1000)
    {
        if (!$this->apiAuth->checkRateLimit($this->apiKeyData['id'], $maxRequests)) {
            exit;
        }
    }
    
    /**
     * Логирование успешного запроса
     * @param string $endpoint
     * @param string $method
     */
    protected function logSuccess($endpoint, $method)
    {
        $this->apiAuth->logRequest(
            $this->apiKeyData['id'] ?? null,
            $endpoint,
            $method,
            200
        );
    }
    
    /**
     * Логирование ошибки
     * @param string $endpoint
     * @param string $method
     * @param int $statusCode
     * @param string $error
     */
    protected function logError($endpoint, $method, $statusCode, $error)
    {
        $this->apiAuth->logRequest(
            $this->apiKeyData['id'] ?? null,
            $endpoint,
            $method,
            $statusCode,
            $error
        );
    }
    
    /**
     * Получение POST данных
     * @return array
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
     * Получение параметров GET
     * @return array
     */
    protected function getQueryParams()
    {
        return $_GET;
    }
    
    /**
     * Успешный ответ
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     */
    protected function success($data = null, $message = 'Успешно', $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Ответ с ошибкой
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @param string $errorCode
     */
    protected function error($message, $statusCode = 400, $errors = [], $errorCode = 'ERROR')
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => false,
            'error' => $errorCode,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Валидация обязательных полей
     * @param array $data
     * @param array $required
     * @return bool
     */
    protected function validateRequired($data, $required = [])
    {
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->error(
                'Отсутствуют обязательные поля',
                400,
                ['missing_fields' => $missing],
                'VALIDATION_ERROR'
            );
            return false;
        }
        
        return true;
    }
    
    /**
     * Получение ID ресурса из URL
     * @param string $paramName
     * @return int|false
     */
    protected function getRouteParam($paramName)
    {
        global $routeParams;
        
        if (!isset($routeParams[$paramName])) {
            return false;
        }
        
        return (int)$routeParams[$paramName];
    }
    
    /**
     * Проверка доступа к группе
     * @param int $resourceGroupId
     * @return bool
     */
    protected function checkGroupAccess($resourceGroupId)
    {
        if ($this->groupId !== $resourceGroupId) {
            $this->error('Доступ к этому ресурсу запрещен', 403, [], 'FORBIDDEN');
            return false;
        }
        return true;
    }
    
    /**
     * Получение информации о группе
     * @return array|false
     */
    protected function getGroupInfo()
    {
        $group = new Group();
        return $group->findById($this->groupId);
    }
    
    /**
     * Проверка существования ресурса
     * @param mixed $resource
     * @param string $resourceName
     * @return bool
     */
    protected function checkResourceExists($resource, $resourceName = 'Ресурс')
    {
        if (!$resource) {
            $this->error("$resourceName не найден", 404, [], 'NOT_FOUND');
            return false;
        }
        return true;
    }
}
