<?php
/**
 * Класс для работы с Telegram Bot API
 */
class TelegramBot
{
    private $token;
    private $apiUrl;
    
    public function __construct($token = null)
    {
        $this->token = $token ?? TELEGRAM_BOT_TOKEN;
        $this->apiUrl = 'https://api.telegram.org/bot' . $this->token . '/';
    }
    
    /**
     * Отправка запроса к API
     */
    private function request($method, $params = [])
    {
        $url = $this->apiUrl . $method;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('Telegram API Error: ' . $error);
            return null;
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !$result['ok']) {
            error_log('Telegram API Response Error: ' . $response);
            return null;
        }
        
        return $result['result'];
    }
    
    /**
     * Отправка текстового сообщения
     */
    public function sendMessage($chatId, $text, $options = [])
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true
        ], $options);
        
        return $this->request('sendMessage', $params);
    }
    
    /**
     * Отправка сообщения с inline-кнопками
     */
    public function sendMessageWithButtons($chatId, $text, $buttons)
    {
        $keyboard = ['inline_keyboard' => $buttons];
        
        return $this->sendMessage($chatId, $text, [
            'reply_markup' => json_encode($keyboard)
        ]);
    }
    
    /**
     * Отправка локации
     */
    public function sendLocation($chatId, $latitude, $longitude)
    {
        return $this->request('sendLocation', [
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);
    }
    
    /**
     * Отправка SOS уведомления
     */
    public function sendSosAlert($chatId, $sosData)
    {
        $text = "<b>🚨 SOS ВЫЗОВ!</b>\n\n";
        $text .= "<b>От кого:</b> {$sosData['user_name']}\n";
        $text .= "<b>Группа:</b> {$sosData['group_name']}\n";
        $text .= "<b>Время:</b> " . date('d.m.Y H:i', strtotime($sosData['created_at'])) . "\n";
        
        if (!empty($sosData['comment'])) {
            $text .= "<b>Комментарий:</b> {$sosData['comment']}\n";
        }
        
        $text .= "\n<b>Координаты:</b> {$sosData['latitude']}, {$sosData['longitude']}";
        
        $buttons = [[
            ['text' => '📍 Посмотреть на карте', 'url' => APP_URL . '/sos/' . $sosData['id']]
        ]];
        
        // Отправляем сообщение
        $this->sendMessageWithButtons($chatId, $text, $buttons);
        
        // Отправляем локацию
        return $this->sendLocation($chatId, $sosData['latitude'], $sosData['longitude']);
    }
    
    /**
     * Проверка данных Telegram Login Widget
     */
    public function verifyLoginWidget($authData)
    {
        if (!isset($authData['hash'])) {
            return false;
        }
        
        $checkHash = $authData['hash'];
        unset($authData['hash']);
        
        // Сортируем данные
        ksort($authData);
        
        // Формируем строку для проверки
        $dataCheckString = [];
        foreach ($authData as $key => $value) {
            $dataCheckString[] = $key . '=' . $value;
        }
        $dataCheckString = implode("\n", $dataCheckString);
        
        // Вычисляем хеш
        $secretKey = hash('sha256', $this->token, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);
        
        return hash_equals($hash, $checkHash);
    }
    
    /**
     * Установка webhook
     */
    public function setWebhook($url)
    {
        return $this->request('setWebhook', ['url' => $url]);
    }
    
    /**
     * Удаление webhook
     */
    public function deleteWebhook()
    {
        return $this->request('deleteWebhook');
    }
    
    /**
     * Получение информации о боте
     */
    public function getMe()
    {
        return $this->request('getMe');
    }
}
