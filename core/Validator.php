<?php
/**
 * Класс валидации данных
 */
class Validator
{
    private $data;
    private $errors = [];
    private $rules;
    
    private $messages = [
        'required' => 'Поле :field обязательно для заполнения',
        'email' => 'Некорректный email адрес',
        'min' => 'Поле :field должно содержать минимум :param символов',
        'max' => 'Поле :field должно содержать максимум :param символов',
        'match' => 'Поля :field и :param должны совпадать',
        'unique' => 'Такой :field уже существует',
        'date' => 'Некорректная дата',
        'alpha' => 'Поле :field должно содержать только буквы',
        'alpha_space' => 'Поле :field должно содержать только буквы и пробелы',
        'numeric' => 'Поле :field должно содержать только цифры',
        'file' => 'Ошибка загрузки файла',
        'image' => 'Файл должен быть изображением',
        'max_size' => 'Размер файла не должен превышать :param',
    ];
    
    private $fieldNames = [
        'email' => 'Email',
        'password' => 'Пароль',
        'password_confirm' => 'Подтверждение пароля',
        'last_name' => 'Фамилия',
        'first_name' => 'Имя',
        'middle_name' => 'Отчество',
        'birth_date' => 'Дата рождения',
        'avatar' => 'Аватар',
    ];
    
    public function __construct($data, $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }
    
    /**
     * Валидация данных
     */
    public function validate()
    {
        foreach ($this->rules as $field => $rules) {
            $rulesArray = explode('|', $rules);
            
            foreach ($rulesArray as $rule) {
                $params = [];
                
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramString] = explode(':', $rule);
                    $params = explode(',', $paramString);
                }
                
                $method = 'validate' . ucfirst($rule);
                
                if (method_exists($this, $method)) {
                    $this->$method($field, $params);
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Получение ошибок
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Получение первой ошибки
     */
    public function getFirstError()
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
    
    /**
     * Добавление ошибки
     */
    private function addError($field, $rule, $params = [])
    {
        $message = $this->messages[$rule] ?? 'Ошибка валидации';
        $fieldName = $this->fieldNames[$field] ?? $field;
        
        $message = str_replace(':field', $fieldName, $message);
        
        if (!empty($params)) {
            $message = str_replace(':param', $params[0], $message);
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * Получение значения поля
     */
    private function getValue($field)
    {
        return $this->data[$field] ?? null;
    }
    
    // --- Правила валидации ---
    
    private function validateRequired($field, $params)
    {
        $value = $this->getValue($field);
        
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'required');
        }
    }
    
    private function validateEmail($field, $params)
    {
        $value = $this->getValue($field);
        
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
        }
    }
    
    private function validateMin($field, $params)
    {
        $value = $this->getValue($field);
        $min = (int) $params[0];
        
        if ($value && mb_strlen($value) < $min) {
            $this->addError($field, 'min', $params);
        }
    }
    
    private function validateMax($field, $params)
    {
        $value = $this->getValue($field);
        $max = (int) $params[0];
        
        if ($value && mb_strlen($value) > $max) {
            $this->addError($field, 'max', $params);
        }
    }
    
    private function validateMatch($field, $params)
    {
        $value = $this->getValue($field);
        $matchField = $params[0];
        $matchValue = $this->getValue($matchField);
        
        if ($value !== $matchValue) {
            $this->addError($field, 'match', [$this->fieldNames[$matchField] ?? $matchField]);
        }
    }
    
    private function validateUnique($field, $params)
    {
        $value = $this->getValue($field);
        $table = $params[0];
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;
        
        if (!$value) {
            return;
        }
        
        $db = Database::getInstance();
        
        if ($exceptId) {
            $exists = $db->fetchOne(
                "SELECT id FROM {$table} WHERE {$column} = ? AND id != ? LIMIT 1",
                [$value, $exceptId]
            );
        } else {
            $exists = $db->fetchOne(
                "SELECT id FROM {$table} WHERE {$column} = ? LIMIT 1",
                [$value]
            );
        }
        
        if ($exists) {
            $this->addError($field, 'unique');
        }
    }
    
    private function validateDate($field, $params)
    {
        $value = $this->getValue($field);
        
        if ($value) {
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->addError($field, 'date');
            }
        }
    }
    
    private function validateAlpha($field, $params)
    {
        $value = $this->getValue($field);
        
        if ($value && !preg_match('/^[\p{L}]+$/u', $value)) {
            $this->addError($field, 'alpha');
        }
    }
    
    private function validateAlpha_space($field, $params)
    {
        $value = $this->getValue($field);
        
        if ($value && !preg_match('/^[\p{L}\s\-]+$/u', $value)) {
            $this->addError($field, 'alpha_space');
        }
    }
    
    private function validateNumeric($field, $params)
    {
        $value = $this->getValue($field);
        
        if ($value && !is_numeric($value)) {
            $this->addError($field, 'numeric');
        }
    }
}
