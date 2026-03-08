<div class="auth-container">
    <div class="auth-header">
        <div class="auth-logo">
            <i data-lucide="users"></i>
        </div>
        <h1 class="auth-title">Приглашение</h1>
        <p class="auth-subtitle">Вас приглашают в группу</p>
    </div>
    
    <div class="card mb-lg">
        <div class="text-center">
            <h2 class="text-xl font-bold mb-xs"><?= e($group['name']) ?></h2>
            <?php if ($group['description']): ?>
            <p class="text-secondary"><?= e($group['description']) ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (Session::isLoggedIn()): ?>
    <!-- Для авторизованных -->
    <form method="POST" action="/invite/<?= e($inviteCode) ?>" data-ajax>
        <button type="submit" class="btn btn-primary btn-lg btn-block">
            <i data-lucide="user-plus"></i>
            Присоединиться к группе
        </button>
    </form>
    
    <div class="text-center mt-md">
        <a href="/dashboard" class="text-secondary">Вернуться на главную</a>
    </div>
    <?php else: ?>
    <!-- Для гостей -->
    <div class="landing-actions">
        <a href="/register" class="btn btn-primary btn-lg btn-block">
            Зарегистрироваться
        </a>
        <a href="/login" class="btn btn-outline btn-lg btn-block">
            Войти
        </a>
    </div>
    <p class="text-center text-sm text-secondary mt-md">
        После регистрации или входа вы автоматически присоединитесь к группе
    </p>
    <?php endif; ?>
</div>
