<?php $userModel = new User(); ?>

<!-- Аватар -->
<section class="section">
    <div class="card">
        <div class="flex flex-col items-center gap-md">
            <div class="avatar-preview avatar-xl" id="avatarPreview">
                <?php if ($user['avatar']): ?>
                    <img src="<?= e(uploads($user['avatar'])) ?>" alt="Аватар">
                <?php else: ?>
                    <i data-lucide="user"></i>
                <?php endif; ?>
            </div>
            <div class="text-center">
                <h2 class="text-xl font-semibold"><?= e($userModel->getFullName($user)) ?></h2>
                <p class="text-sm text-secondary"><?= e($user['email']) ?></p>
            </div>
            <form method="POST" action="/profile/avatar" enctype="multipart/form-data" id="avatarForm">
                <label class="btn btn-secondary btn-sm">
                    <i data-lucide="camera"></i>
                    Изменить фото
                    <input 
                        type="file" 
                        name="avatar" 
                        accept="image/*"
                        style="display: none;"
                        onchange="document.getElementById('avatarForm').submit();"
                    >
                </label>
            </form>
        </div>
    </div>
</section>

<!-- Редактирование профиля -->
<section class="section">
    <h3 class="section-title">Личные данные</h3>
    <div class="card">
        <form method="POST" action="/profile/update" data-ajax>
            <div class="form-group">
                <label class="form-label" for="last_name">Фамилия</label>
                <input 
                    type="text" 
                    id="last_name" 
                    name="last_name" 
                    class="form-input" 
                    value="<?= e($user['last_name']) ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="first_name">Имя</label>
                <input 
                    type="text" 
                    id="first_name" 
                    name="first_name" 
                    class="form-input" 
                    value="<?= e($user['first_name']) ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="middle_name">Отчество</label>
                <input 
                    type="text" 
                    id="middle_name" 
                    name="middle_name" 
                    class="form-input" 
                    value="<?= e($user['middle_name'] ?? '') ?>"
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="birth_date">Дата рождения</label>
                <input 
                    type="date" 
                    id="birth_date" 
                    name="birth_date" 
                    class="form-input" 
                    value="<?= e($user['birth_date']) ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input" 
                    value="<?= e($user['email']) ?>"
                    required
                >
            </div>
            
            <button type="submit" class="btn btn-primary btn-block mt-md">
                Сохранить изменения
            </button>
        </form>
    </div>
</section>

<!-- Информация -->
<section class="section">
    <h3 class="section-title">Информация</h3>
    <div class="card">
        <div class="flex justify-between items-center mb-md">
            <span class="text-secondary">Дата регистрации</span>
            <span class="font-medium"><?= formatDate($user['created_at']) ?></span>
        </div>
        <div class="flex justify-between items-center mb-md">
            <span class="text-secondary">Геолокация</span>
            <span class="font-medium">
                <?php if ($user['geolocation_enabled']): ?>
                    <span class="text-success">Включена</span>
                <?php else: ?>
                    <span class="text-warning">Отключена</span>
                <?php endif; ?>
            </span>
        </div>
        <?php if ($user['last_location_update']): ?>
        <div class="flex justify-between items-center">
            <span class="text-secondary">Последнее обновление</span>
            <span class="font-medium"><?= timeAgo($user['last_location_update']) ?></span>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Выход -->
<section class="section">
    <a href="/logout" class="btn btn-danger btn-block" onclick="return confirm('Вы уверены, что хотите выйти?');">
        <i data-lucide="log-out"></i>
        Выйти из аккаунта
    </a>
</section>
