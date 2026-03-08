<div class="auth-container">
    <div class="auth-header">
        <div class="auth-logo">
            <i data-lucide="compass"></i>
        </div>
        <h1 class="auth-title">Вход</h1>
        <p class="auth-subtitle">Войдите в свой аккаунт</p>
    </div>
    
    <form class="auth-form" method="POST" action="/login" data-ajax>
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-input" 
                placeholder="example@mail.ru"
                required
                autocomplete="email"
            >
        </div>
        
        <div class="form-group">
            <label class="form-label" for="password">Пароль</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-input" 
                placeholder="Введите пароль"
                required
                autocomplete="current-password"
            >
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg btn-block mt-lg">
            Войти
        </button>
    </form>
    
    <div class="auth-footer">
        <p class="text-secondary">
            Ещё нет аккаунта? 
            <a href="/register">Зарегистрироваться</a>
        </p>
    </div>
</div>
