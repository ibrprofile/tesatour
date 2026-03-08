<?php
/**
 * Контроллер настроек
 */
class SettingsController extends BaseController
{
    private $userModel;
    private $telegram;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->telegram = new TelegramBot();
    }
    
    /**
     * Страница настроек
     */
    public function index()
    {
        $this->render('settings/index', [
            'pageTitle' => 'Настройки',
            'user' => $this->currentUser,
            'telegramBotUsername' => TELEGRAM_BOT_USERNAME
        ]);
    }
    
    /**
     * Привязка Telegram
     */
    public function linkTelegram()
    {
        $data = $this->getPostData();
        
        // Проверяем данные от Telegram Login Widget
        if (!$this->telegram->verifyLoginWidget($data)) {
            if ($this->isAjax()) {
                $this->error('Ошибка проверки данных Telegram');
            }
            Session::flash('error', 'Ошибка проверки данных Telegram');
            $this->redirect('/settings');
            return;
        }
        
        $telegramId = (int) $data['id'];
        $telegramUsername = $data['username'] ?? null;
        
        // Проверяем, не привязан ли уже к другому аккаунту
        $existingUser = $this->userModel->findByTelegramId($telegramId);
        
        if ($existingUser && $existingUser['id'] !== $this->currentUser['id']) {
            if ($this->isAjax()) {
                $this->error('Этот Telegram аккаунт уже привязан к другому пользователю');
            }
            Session::flash('error', 'Этот Telegram аккаунт уже привязан к другому пользователю');
            $this->redirect('/settings');
            return;
        }
        
        $this->userModel->linkTelegram(Session::getUserId(), $telegramId, $telegramUsername);
        
        // Отправляем приветственное сообщение
        $this->telegram->sendMessage(
            $telegramId,
            "<b>Добро пожаловать в TESA Tour!</b>\n\n" .
            "Ваш аккаунт успешно привязан. Теперь вы будете получать уведомления о SOS-вызовах в группах."
        );
        
        if ($this->isAjax()) {
            $this->success(null, 'Telegram успешно привязан');
        }
        
        Session::flash('success', 'Telegram успешно привязан');
        $this->redirect('/settings');
    }
    
    /**
     * Отвязка Telegram
     */
    public function unlinkTelegram()
    {
        $this->userModel->unlinkTelegram(Session::getUserId());
        
        if ($this->isAjax()) {
            $this->success(null, 'Telegram отвязан');
        }
        
        Session::flash('success', 'Telegram успешно отвязан');
        $this->redirect('/settings');
    }
    
    /**
     * Смена пароля
     */
    public function changePassword()
    {
        $data = $this->getPostData();
        
        $validator = $this->validate($data, [
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required'
        ]);
        
        if (!$validator->validate()) {
            if ($this->isAjax()) {
                $this->error($validator->getFirstError(), 400, $validator->getErrors());
            }
            Session::flash('error', $validator->getFirstError());
            $this->redirect('/settings');
            return;
        }
        
        // Проверяем совпадение паролей
        if ($data['new_password'] !== $data['confirm_password']) {
            if ($this->isAjax()) {
                $this->error('Пароли не совпадают');
            }
            Session::flash('error', 'Пароли не совпадают');
            $this->redirect('/settings');
            return;
        }
        
        // Проверяем текущий пароль
        if (!$this->userModel->verifyPassword($data['current_password'], $this->currentUser['password_hash'])) {
            if ($this->isAjax()) {
                $this->error('Неверный текущий пароль');
            }
            Session::flash('error', 'Неверный текущий пароль');
            $this->redirect('/settings');
            return;
        }
        
        // Обновляем пароль
        $this->userModel->update(Session::getUserId(), [
            'password_hash' => password_hash($data['new_password'], PASSWORD_BCRYPT)
        ]);
        
        if ($this->isAjax()) {
            $this->success(null, 'Пароль успешно изменен');
        }
        
        Session::flash('success', 'Пароль успешно изменен');
        $this->redirect('/settings');
    }
    
    /**
     * Переключение на аккаунт турагентства
     */
    public function upgradeToAgency()
    {
        $this->userModel->update(Session::getUserId(), [
            'account_type' => 'agency'
        ]);
        
        // Update session
        $updatedUser = $this->userModel->findById(Session::getUserId());
        Session::setUser($updatedUser);
        
        if ($this->isAjax()) {
            $this->success(['redirect' => '/subscription'], 'Аккаунт переключен на Турагентство. Оформите подписку.');
        }
        
        Session::flash('success', 'Аккаунт переключен на Турагентство. Оформите подписку для создания групп.');
        $this->redirect('/subscription');
    }
}
