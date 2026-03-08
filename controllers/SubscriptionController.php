<?php

class SubscriptionController extends BaseController {
    
    private $shopId;
    private $secretKey;
    
    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
        $this->shopId = defined('YOOKASSA_SHOP_ID') ? YOOKASSA_SHOP_ID : '123456';
        $this->secretKey = defined('YOOKASSA_SECRET_KEY') ? YOOKASSA_SECRET_KEY : 'test_secret_key';
    }
    
    public function index() {
        $this->requireAuth();
        
        $user = $this->getUser();
        
        $subscription = null;
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM subscriptions 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$user['id']]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Таблица может не существовать
            $subscription = null;
        }
        
        $this->render('subscription/index', [
            'user' => $user,
            'subscription' => $subscription,
            'pageTitle' => 'Подписка'
        ]);
    }
    
    public function create() {
        $this->requireAuth();
        
        $user = $this->getUser();
        
        if (($user['account_type'] ?? 'amateur') !== 'agency') {
            Response::json([
                'success' => false,
                'message' => 'Подписка доступна только для турагентств'
            ], 400);
            return;
        }
        
        $amount = 499.00;
        $description = 'Подписка TESA Tour (турагентство) - 499 руб/мес';
        
        $idempotenceKey = uniqid('', true);
        
        $payment = [
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'RUB'
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => BASE_URL . '/subscription/callback'
            ],
            'capture' => true,
            'description' => $description,
            'metadata' => [
                'user_id' => $user['id'],
                'type' => 'subscription'
            ]
        ];
        
        $ch = curl_init('https://api.yookassa.ru/v3/payments');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Idempotence-Key: ' . $idempotenceKey
        ]);
        curl_setopt($ch, CURLOPT_USERPWD, $this->shopId . ':' . $this->secretKey);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            Response::json([
                'success' => false,
                'message' => 'Ошибка создания платежа'
            ], 500);
            return;
        }
        
        $paymentData = json_decode($response, true);
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO payments (user_id, payment_id, amount, status, created_at) 
                VALUES (?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $user['id'],
                $paymentData['id'],
                $amount
            ]);
        } catch (PDOException $e) {
            // payment logging failed
        }
        
        Response::json([
            'success' => true,
            'confirmation_url' => $paymentData['confirmation']['confirmation_url']
        ]);
    }
    
    public function webhook() {
        $body = file_get_contents('php://input');
        $event = json_decode($body, true);
        
        if (!isset($event['event']) || $event['event'] !== 'payment.succeeded') {
            http_response_code(200);
            return;
        }
        
        $payment = $event['object'];
        $userId = $payment['metadata']['user_id'] ?? null;
        
        if (!$userId) {
            http_response_code(200);
            return;
        }
        
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare("
                UPDATE payments 
                SET status = 'succeeded' 
                WHERE payment_id = ?
            ");
            $stmt->execute([$payment['id']]);
            
            $stmt = $this->db->prepare("
                SELECT * FROM subscriptions 
                WHERE user_id = ? AND status = 'active' 
                ORDER BY end_date DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $existingSubscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingSubscription) {
                $startDate = $existingSubscription['end_date'];
            } else {
                $startDate = date('Y-m-d H:i:s');
            }
            
            $endDate = date('Y-m-d H:i:s', strtotime($startDate . ' +1 month'));
            
            $stmt = $this->db->prepare("
                INSERT INTO subscriptions (user_id, start_date, end_date, status, created_at) 
                VALUES (?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$userId, $startDate, $endDate]);
            
            $this->db->commit();
            
            http_response_code(200);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
        }
    }
    
    /**
     * Страница после успешной оплаты (переименовано чтобы не конфликтовать с BaseController::success)
     */
    public function callback() {
        $this->requireAuth();
        
        Session::flash('success', 'Подписка успешно оформлена!');
        Response::redirect('/settings');
    }
    
    public function cancel() {
        $this->requireAuth();
        
        $user = $this->getUser();
        
        try {
            $stmt = $this->db->prepare("
                UPDATE subscriptions 
                SET status = 'cancelled' 
                WHERE user_id = ? AND status = 'active'
            ");
            $stmt->execute([$user['id']]);
        } catch (PDOException $e) {
            // ignore
        }
        
        if ($this->isAjax()) {
            $this->success(null, 'Подписка отменена');
            return;
        }
        
        Session::flash('success', 'Подписка отменена');
        Response::redirect('/settings');
    }
    
    public function checkStatus() {
        $this->requireAuth();
        
        $user = $this->getUser();
        
        $subscription = null;
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM subscriptions 
                WHERE user_id = ? AND status = 'active' AND end_date > NOW() 
                ORDER BY end_date DESC 
                LIMIT 1
            ");
            $stmt->execute([$user['id']]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // ignore
        }
        
        Response::json([
            'success' => true,
            'has_active_subscription' => $subscription !== false && $subscription !== null,
            'subscription' => $subscription
        ]);
    }
}
