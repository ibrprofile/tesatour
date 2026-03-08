<?php
/**
 * Контроллер статических страниц
 */
class PageController extends BaseController
{
    /**
     * Главная страница (лендинг)
     */
    public function home()
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->render('pages/home', [
            'pageTitle' => 'Добро пожаловать'
        ], 'guest');
    }
    
    /**
     * Пользовательское соглашение
     */
    public function terms()
    {
        $this->render('legal/terms', [
            'pageTitle' => 'Пользовательское соглашение'
        ], Session::isLoggedIn() ? 'main' : 'guest');
    }
    
    /**
     * Политика конфиденциальности
     */
    public function privacy()
    {
        $this->render('legal/privacy', [
            'pageTitle' => 'Политика конфиденциальности'
        ], Session::isLoggedIn() ? 'main' : 'guest');
    }
    
    /**
     * Публичная оферта
     */
    public function offer()
    {
        $this->render('legal/offer', [
            'pageTitle' => 'Публичная оферта'
        ], Session::isLoggedIn() ? 'main' : 'guest');
    }
}
