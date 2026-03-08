<div class="auth-container">
    <div class="auth-header">
        <div class="auth-logo"><i data-lucide="mountain"></i></div>
        <h1 class="auth-title">TESA Tour</h1>
        <p class="auth-subtitle">Создание аккаунта</p>
    </div>

    <form class="auth-form" method="POST" action="/register" enctype="multipart/form-data" data-ajax>
        <div class="form-group">
            <label class="form-label">Тип аккаунта</label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <label id="type-amateur" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;background:var(--color-surface-secondary);border-radius:12px;cursor:pointer;border:2px solid var(--color-primary);text-align:center;" onclick="selectType('amateur')">
                    <input type="radio" name="account_type" value="amateur" checked style="display:none;">
                    <div style="width:40px;height:40px;border-radius:50%;background:rgba(0,122,255,0.1);display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="user" style="color:var(--color-primary);width:20px;height:20px;"></i>
                    </div>
                    <div style="font-weight:600;font-size:0.875rem;">Любитель</div>
                    <div style="font-size:0.6875rem;color:var(--color-text-secondary);">Бесплатно, 1 группа</div>
                </label>
                <label id="type-agency" style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;background:var(--color-surface-secondary);border-radius:12px;cursor:pointer;border:2px solid transparent;text-align:center;" onclick="selectType('agency')">
                    <input type="radio" name="account_type" value="agency" style="display:none;">
                    <div style="width:40px;height:40px;border-radius:50%;background:rgba(255,149,0,0.1);display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="briefcase" style="color:#FF9500;width:20px;height:20px;"></i>
                    </div>
                    <div style="font-weight:600;font-size:0.875rem;">Турагентство</div>
                    <div style="font-size:0.6875rem;color:var(--color-text-secondary);">499 руб/мес</div>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="last_name">Фамилия</label>
            <input type="text" id="last_name" name="last_name" class="form-input" placeholder="Иванов" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="first_name">Имя</label>
            <input type="text" id="first_name" name="first_name" class="form-input" placeholder="Иван" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="middle_name">Отчество</label>
            <input type="text" id="middle_name" name="middle_name" class="form-input" placeholder="Иванович">
        </div>
        <div class="form-group">
            <label class="form-label" for="birth_date">Дата рождения</label>
            <input type="date" id="birth_date" name="birth_date" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Пароль</label>
            <input type="password" id="password" name="password" class="form-input" placeholder="Минимум 6 символов" minlength="6" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="password_confirm">Подтверждение пароля</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="Повторите пароль" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;">Зарегистрироваться</button>
    </form>

    <div class="auth-footer">
        Уже есть аккаунт? <a href="/login">Войти</a>
    </div>
</div>

<script>
function selectType(type) {
    document.getElementById('type-amateur').style.borderColor = type==='amateur' ? 'var(--color-primary)' : 'transparent';
    document.getElementById('type-agency').style.borderColor = type==='agency' ? 'var(--color-primary)' : 'transparent';
    document.querySelector('input[name="account_type"][value="'+type+'"]').checked = true;
}
<?php if(isset($_GET['type']) && $_GET['type']==='agency'): ?>
document.addEventListener('DOMContentLoaded', function(){ selectType('agency'); });
<?php endif; ?>
</script>
