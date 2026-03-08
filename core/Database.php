<?php
/**
 * Класс для работы с базой данных MySQL
 * Реализует паттерн Singleton и подготовленные запросы
 */
class Database
{
    private static $instance = null;
    private $connection = null;
    
    private function __construct()
    {
        $this->connect();
    }
    
    /**
     * Получение экземпляра класса (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Подключение к базе данных
     */
    private function connect()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die('Ошибка подключения к БД: ' . $e->getMessage());
            }
            die('Ошибка подключения к базе данных');
        }
    }
    
    /**
     * Получение PDO соединения
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Выполнение запроса с параметрами
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Получение одной записи
     */
    public function fetchOne($sql, $params = [])
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }
    
    /**
     * Получение всех записей
     */
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Получение значения одного поля
     */
    public function fetchColumn($sql, $params = [], $column = 0)
    {
        return $this->query($sql, $params)->fetchColumn($column);
    }
    
    /**
     * Вставка записи
     */
    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return (int) $this->connection->lastInsertId();
    }
    
    /**
     * Обновление записей
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Удаление записей
     */
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Проверка существования записи
     */
    public function exists($table, $where, $params = [])
    {
        $sql = "SELECT 1 FROM {$table} WHERE {$where} LIMIT 1";
        return (bool) $this->fetchColumn($sql, $params);
    }
    
    /**
     * Подсчет записей
     */
    public function count($table, $where = '1', $params = [])
    {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        return (int) $this->fetchColumn($sql, $params);
    }
    
    /**
     * Начало транзакции
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Подтверждение транзакции
     */
    public function commit()
    {
        return $this->connection->commit();
    }
    
    /**
     * Откат транзакции
     */
    public function rollback()
    {
        return $this->connection->rollBack();
    }
    
    /**
     * Запрет клонирования
     */
    private function __clone() {}
    
    /**
     * Запрет десериализации
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }
}
