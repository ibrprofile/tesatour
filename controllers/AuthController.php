<?php
/**
 * Контроллер авторизации
 */
class AuthController extends BaseController
{
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Форма авторизации
     */
    public function loginForm()
    {
        $this->render('auth/login', [
            'pageTitle' => 'Вход'
        ], 'guest');
    }
    
    /**
     * Обработка авторизации
     */
    public function login()
    {
        $data = $this->getPostData();
        
        $validator = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!$validator->validate()) {
            if ($this->isAjax()) {
                $this->error($validator->getFirstError(), 400, $validator->getErrors());
            }
            Session::flash('error', $validator->getFirstError());
            $this->redirect('/login');
            return;
        }
        
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user || !$this->userModel->verifyPassword($data['password'], $user['password_hash'])) {
            if ($this->isAjax()) {
                $this->error('Неверный email или пароль');
            }
            Session::flash('error', 'Неверный email или пароль');
            $this->redirect('/login');
            return;
        }
        
        Session::setUser($user['id']);
        
        if ($this->isAjax()) {
            $this->success(['redirect' => '/dashboard'], 'Добро пожаловать!');
        }
        
        Session::flash('success', 'Добро пожаловать, ' . e($user['first_name']) . '!');
        $this->redirect('/dashboard');
    }
    
    /**
     * Форма регистрации
     */
    public function registerForm()
    {
        $this->render('auth/register', [
            'pageTitle' => 'Регистрация'
        ], 'guest');
    }
    
    /**
     * Обработка регистрации
     */
    public function register()
    {
        $data = $this->getPostData();
        
        $validator = $this->validate($data, [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'password_confirm' => 'required|match:password',
            'last_name' => 'required|alpha_space|min:2|max:50',
            'first_name' => 'required|alpha_space|min:2|max:50',
            'middle_name' => 'alpha_space|max:50',
            'birth_date' => 'required|date',
            'account_type' => 'in:amateur,agency'
        ]);
        
        if (!$validator->validate()) {
            if ($this->isAjax()) {
                $this->error($validator->getFirstError(), 400, $validator->getErrors());
            }
            Session::flash('error', $validator->getFirstError());
            $this->redirect('/register');
            return;
        }
        
        $upload = new FileUpload($_FILES['avatar'] ?? null);
        $avatarPath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarPath = $upload->saveImage('avatars', 400, 400);
            
            if (!$avatarPath) {
                if ($this->isAjax()) {
                    $this->error('Ошибка загрузки аватара: ' . implode(', ', $upload->getErrors()));
                }
                Session::flash('error', 'Ошибка загрузки аватара');
                $this->redirect('/register');
                return;
            }
        }
        
        try {
            $userId = $this->userModel->create([
                'email' => $data['email'],
                'password' => $data['password'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'birth_date' => $data['birth_date'],
                'avatar' => $avatarPath,
                'account_type' => $data['account_type'] ?? 'amateur'
            ]);
        } catch (PDOException $e) {
            if ($this->isAjax()) {
                $this->error('Ошибка регистрации', 500);
            }
            Session::flash('error', 'Ошибка регистрации');
            $this->redirect('/register');
            return;
        }
        
        Session::setUser($userId);
        
        $pendingInvite = Session::get('pending_invite');
        $redirectUrl = '/dashboard';
        
        if ($pendingInvite) {
            Session::remove('pending_invite');
            $redirectUrl = '/invite/' . $pendingInvite . '/join';
        }
        
        if ($this->isAjax()) {
            $this->success(['redirect' => $redirectUrl], 'Регистрация успешна!');
        }
        
        Session::flash('success', 'Добро пожаловать в TESA Tour!');
        $this->redirect($redirectUrl);
    }
    
    /**
     * Выход
     */
    public function logout()
    {
        Session::logout();
        $this->redirect('/');
    }
}
