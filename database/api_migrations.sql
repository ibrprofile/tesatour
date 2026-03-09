-- ===============================================
-- TESA Tour API System Database Tables
-- ===============================================

-- Таблица API ключей для групп
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `token_prefix` VARCHAR(20) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT UNSIGNED NOT NULL,
  `last_used_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_group_id` (`group_id`),
  INDEX `idx_token_prefix` (`token_prefix`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_created_by` (`created_by`),
  INDEX `idx_expires_at` (`expires_at`),
  
  FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица разрешений (scopes) для API ключей
CREATE TABLE IF NOT EXISTS `api_key_scopes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `api_key_id` INT UNSIGNED NOT NULL,
  `scope` VARCHAR(100) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE KEY `unique_key_scope` (`api_key_id`, `scope`),
  INDEX `idx_api_key_id` (`api_key_id`),
  INDEX `idx_scope` (`scope`),
  
  FOREIGN KEY (`api_key_id`) REFERENCES `api_keys`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица логирования API запросов
CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `api_key_id` INT UNSIGNED DEFAULT NULL,
  `endpoint` VARCHAR(255) NOT NULL,
  `method` VARCHAR(10) NOT NULL,
  `status_code` INT DEFAULT NULL,
  `response_time_ms` INT DEFAULT NULL,
  `request_size` INT DEFAULT NULL,
  `response_size` INT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_api_key_id` (`api_key_id`),
  INDEX `idx_endpoint` (`endpoint`),
  INDEX `idx_method` (`method`),
  INDEX `idx_status_code` (`status_code`),
  INDEX `idx_created_at` (`created_at`),
  
  FOREIGN KEY (`api_key_id`) REFERENCES `api_keys`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица для отслеживания злоупотреблений API (rate limiting)
CREATE TABLE IF NOT EXISTS `api_rate_limits` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `api_key_id` INT UNSIGNED NOT NULL,
  `requests_count` INT DEFAULT 0,
  `reset_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE KEY `unique_key_period` (`api_key_id`, `reset_at`),
  INDEX `idx_api_key_id` (`api_key_id`),
  INDEX `idx_reset_at` (`reset_at`),
  
  FOREIGN KEY (`api_key_id`) REFERENCES `api_keys`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Определение доступных scopes/разрешений
-- ===============================================

-- Для удобства создадим таблицу со всеми возможными scopes
CREATE TABLE IF NOT EXISTS `api_scopes_reference` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `scope` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_scope` (`scope`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вставляем доступные scopes
INSERT INTO `api_scopes_reference` (`scope`, `description`, `category`) VALUES
-- Участники группы
('members:read', 'Получение информации об участниках', 'members'),
('members:write', 'Редактирование информации участников', 'members'),

-- Маршруты
('routes:read', 'Получение маршрутов и их точек', 'routes'),
('routes:write', 'Создание и редактирование маршрутов', 'routes'),

-- Опасные зоны
('danger_zones:read', 'Получение информации об опасных зонах', 'danger_zones'),
('danger_zones:write', 'Создание и редактирование опасных зон', 'danger_zones'),

-- Каналы
('channels:read', 'Получение информации о каналах', 'channels'),
('channels:write', 'Редактирование каналов и отправка сообщений', 'channels'),

-- Чаты
('chats:read', 'Получение сообщений из чата группы', 'chats'),
('chats:write', 'Отправка сообщений в чат группы', 'chats'),

-- SOS вызовы
('sos:read', 'Получение информации об SOS вызовах', 'sos'),
('sos:write', 'Создание и обновление SOS вызовов', 'sos'),

-- Геолокация
('locations:read', 'Получение информации о местоположении участников', 'locations'),
('locations:write', 'Обновление информации о местоположении', 'locations'),

-- Группы
('groups:read', 'Получение информации о группе', 'groups'),
('groups:write', 'Редактирование параметров группы', 'groups'),

-- Логи и аналитика
('logs:read', 'Получение логов API запросов', 'logs'),

-- Всеобщий доступ (осторожно!)
('*', 'Полный доступ ко всем эндпоинтам', 'system');

-- Триггер для автоматического обновления updated_at в api_keys
DELIMITER ;;
CREATE TRIGGER `api_keys_before_update` BEFORE UPDATE ON `api_keys` FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END;;
DELIMITER ;
