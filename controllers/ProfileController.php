<?php
/**
 * Контроллер профиля
 */
class ProfileController extends BaseController
{
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Страница профиля
     */
    public function index()
    {
        $this->render('profile/index', [
            'pageTitle' => 'Профиль',
            'user' => $this->currentUser
        ]);
    }
    
    /**
     * Обновление профиля
     */
    public function update()
    {
        $data = $this->getPostData();
        $userId = Session::getUserId();
        
        $validator = $this->validate($data, [
            'last_name' => 'required|alpha_space|min:2|max:50',
            'first_name' => 'required|alpha_space|min:2|max:50',
            'middle_name' => 'alpha_space|max:50',
            'birth_date' => 'required|date',
            'email' => 'required|email|unique:users,email,' . $userId
        ]);
        
        if (!$validator->validate()) {
            if ($this->isAjax()) {
                $this->error($validator->getFirstError(), 400, $validator->getErrors());
            }
            Session::flash('error', $validator->getFirstError());
            $this->redirect('/profile');
            return;
        }
        
        $this->userModel->update($userId, [
            'last_name' => $data['last_name'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'birth_date' => $data['birth_date'],
            'email' => $data['email']
        ]);
        
        if ($this->isAjax()) {
            $this->success(null, 'Профиль обновлен');
        }
        
        Session::flash('success', 'Профиль успешно обновлен');
        $this->redirect('/profile');
    }
    
    /**
     * Обновление аватара
     */
    public function updateAvatar()
    {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            if ($this->isAjax()) {
                $this->error('Выберите изображение для загрузки');
            }
            Session::flash('error', 'Выберите изображение для загрузки');
            $this->redirect('/profile');
            return;
        }
        
        $upload = new FileUpload($_FILES['avatar']);
        $avatarPath = $upload->saveImage('avatars', 400, 400);
        
        if (!$avatarPath) {
            $errorMessage = implode(', ', $upload->getErrors());
            if ($this->isAjax()) {
                $this->error($errorMessage);
            }
            Session::flash('error', $errorMessage);
            $this->redirect('/profile');
            return;
        }
        
        // Удаляем старый аватар
        if ($this->currentUser['avatar']) {
            FileUpload::delete($this->currentUser['avatar']);
        }
        
        $this->userModel->updateAvatar(Session::getUserId(), $avatarPath);
        
        if ($this->isAjax()) {
            $this->success(['avatar' => uploads($avatarPath)], 'Аватар обновлен');
        }
        
        Session::flash('success', 'Аватар успешно обновлен');
        $this->redirect('/profile');
    }
}
