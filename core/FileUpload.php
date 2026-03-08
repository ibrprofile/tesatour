<?php
/**
 * Класс для загрузки файлов
 */
class FileUpload
{
    private $file;
    private $errors = [];
    private $allowedTypes;
    private $maxSize;
    
    public function __construct($file, $allowedTypes = [], $maxSize = 0)
    {
        $this->file = $file;
        $this->allowedTypes = $allowedTypes ?: ALLOWED_IMAGE_TYPES;
        $this->maxSize = $maxSize ?: MAX_UPLOAD_SIZE;
    }
    
    /**
     * Валидация файла
     */
    public function validate()
    {
        // Проверка на ошибки загрузки
        if ($this->file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($this->file['error']);
            return false;
        }
        
        // Проверка размера
        if ($this->file['size'] > $this->maxSize) {
            $this->errors[] = 'Размер файла превышает допустимый (' . $this->formatSize($this->maxSize) . ')';
            return false;
        }
        
        // Проверка типа
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($this->file['tmp_name']);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            $this->errors[] = 'Недопустимый тип файла';
            return false;
        }
        
        return true;
    }
    
    /**
     * Сохранение файла
     */
    public function save($directory, $filename = null)
    {
        if (!$this->validate()) {
            return null;
        }
        
        // Создание директории
        $uploadPath = UPLOADS_PATH . '/' . $directory;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Генерация имени файла
        if (!$filename) {
            $extension = $this->getExtension();
            $filename = $this->generateFilename() . '.' . $extension;
        }
        
        $filePath = $uploadPath . '/' . $filename;
        
        // Перемещение файла
        if (move_uploaded_file($this->file['tmp_name'], $filePath)) {
            return $directory . '/' . $filename;
        }
        
        $this->errors[] = 'Ошибка сохранения файла';
        return null;
    }
    
    /**
     * Сохранение изображения с обработкой
     */
    public function saveImage($directory, $maxWidth = 800, $maxHeight = 800)
    {
        if (!$this->validate()) {
            return null;
        }
        
        // Создание директории
        $uploadPath = UPLOADS_PATH . '/' . $directory;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Генерация имени файла
        $filename = $this->generateFilename() . '.jpg';
        $filePath = $uploadPath . '/' . $filename;
        
        // Загрузка изображения
        $sourceImage = $this->createImageFromFile();
        if (!$sourceImage) {
            $this->errors[] = 'Ошибка обработки изображения';
            return null;
        }
        
        // Получение размеров
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);
        
        // Вычисление новых размеров
        $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
        $newWidth = (int) ($width * $ratio);
        $newHeight = (int) ($height * $ratio);
        
        // Создание нового изображения
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Сохранение
        imagejpeg($newImage, $filePath, 90);
        
        // Освобождение памяти
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return $directory . '/' . $filename;
    }
    
    /**
     * Получение ошибок
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Создание изображения из файла
     */
    private function createImageFromFile()
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($this->file['tmp_name']);
        
        if ($mimeType === 'image/jpeg') {
            return imagecreatefromjpeg($this->file['tmp_name']);
        } elseif ($mimeType === 'image/png') {
            return imagecreatefrompng($this->file['tmp_name']);
        } elseif ($mimeType === 'image/gif') {
            return imagecreatefromgif($this->file['tmp_name']);
        } elseif ($mimeType === 'image/webp') {
            return imagecreatefromwebp($this->file['tmp_name']);
        }
        return false;
    }
    
    /**
     * Получение расширения файла
     */
    private function getExtension()
    {
        return strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
    }
    
    /**
     * Генерация уникального имени файла
     */
    private function generateFilename()
    {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Форматирование размера файла
     */
    private function formatSize($bytes)
    {
        $units = ['Б', 'КБ', 'МБ', 'ГБ'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Получение сообщения об ошибке загрузки
     */
    private function getUploadErrorMessage($error)
    {
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            return 'Превышен максимальный размер файла';
        } elseif ($error === UPLOAD_ERR_PARTIAL) {
            return 'Файл загружен частично';
        } elseif ($error === UPLOAD_ERR_NO_FILE) {
            return 'Файл не был загружен';
        } elseif ($error === UPLOAD_ERR_NO_TMP_DIR) {
            return 'Отсутствует временная папка';
        } elseif ($error === UPLOAD_ERR_CANT_WRITE) {
            return 'Ошибка записи файла';
        } elseif ($error === UPLOAD_ERR_EXTENSION) {
            return 'Загрузка остановлена расширением';
        }
        return 'Неизвестная ошибка загрузки';
    }
    
    /**
     * Удаление файла
     */
    public static function delete($path)
    {
        $fullPath = UPLOADS_PATH . '/' . $path;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}
