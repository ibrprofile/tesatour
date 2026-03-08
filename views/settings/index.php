<?php 
$userModel = new User();
$accountType = $user['account_type'] ?? 'amateur';
$subscription = null;
try {
    $db = Database::getInstance()->getConnection();
    $subStmt = $db->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' AND end_date > NOW() ORDER BY end_date DESC LIMIT 1");
    $subStmt->execute([$user['id']]);
    $subscription = $subStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $subscription = null;
}
?>

<section class="section">
    <h1 class="text-2xl font-bold mb-lg">Настройки</h1>

    <!-- Подписка и аккаунт -->
    <section class="section">
        <h3 class="section-title">Подписка и аккаунт</h3>
        <div class="card">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:44px;height:44px;border-radius:12px;background:<?= $accountType==='agency' ? 'rgba(0,122,255,0.1)' : 'rgba(52,199,89,0.1)' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="<?= $accountType==='agency' ? 'briefcase' : 'user' ?>" style="color:<?= $accountType==='agency' ? '#007AFF' : '#34C759' ?>;width:22px;height:22px;"></i>
                </div>
                <div style="flex:1;">
                    <div class="font-semibold"><?= $accountType==='agency' ? 'Турагентство' : 'Любитель' ?>
                        <?php if($accountType==='agency' && $subscription): ?>
                            <span style="display:inline-block;background:rgba(52,199,89,0.1);color:#34C759;padding:2px 8px;border-radius:10px;font-size:0.6875rem;font-weight:600;margin-left:4px;">Активна</span>
                        <?php elseif($accountType==='agency'): ?>
                            <span style="display:inline-block;background:rgba(255,59,48,0.1);color:#FF3B30;padding:2px 8px;border-radius:10px;font-size:0.6875rem;font-weight:600;margin-left:4px;">Не оплачена</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-sm text-secondary">
                        <?php if($subscription): ?>
                            Действует до <?= date('d.m.Y', strtotime($subscription['end_date'])) ?>
                        <?php elseif($accountType==='agency'): ?>
                            Подписка не активна
                        <?php else: ?>
                            1 активная группа бесплатно
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if($accountType==='agency'): ?>
                <?php if($subscription): ?>
                    <a href="/subscription" class="btn btn-primary btn-block">Продлить подписку</a>
                <?php else: ?>
                    <a href="/subscription" class="btn btn-primary btn-block">Оплатить подписку — 499 руб/мес</a>
                <?php endif; ?>
            <?php else: ?>
                <div style="padding:12px;background:rgba(0,122,255,0.05);border-radius:12px;margin-bottom:12px;">
                    <div class="text-sm" style="margin-bottom:4px;font-weight:600;">Перейти на Турагентство?</div>
                    <div class="text-sm text-secondary">Безлимитные группы, приоритетная поддержка — 499 руб/мес</div>
                </div>
                <form method="POST" action="/settings/upgrade" data-ajax>
                    <button type="submit" class="btn btn-primary btn-block">Переключить на Турагентство</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Telegram интеграция -->
    <section class="section">
        <h3 class="section-title">Telegram уведомления</h3>
        <div class="card">
            <?php if ($user['telegram_id']): ?>
            <div class="telegram-linked mb-md">
                <div class="telegram-icon">
                    <i data-lucide="send"></i>
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-success">Telegram привязан</div>
                    <div class="text-sm text-secondary">
                        <?php if ($user['telegram_username']): ?>
                            @<?= e($user['telegram_username']) ?>
                        <?php else: ?>
                            ID: <?= e($user['telegram_id']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <p class="text-sm text-secondary mb-md">
                Вы будете получать уведомления о SOS-вызовах через Telegram бота.
            </p>
            
            <form method="POST" action="/settings/telegram/unlink" data-ajax data-reload>
                <button type="submit" class="btn btn-outline btn-block" onclick="return confirm('Отвязать Telegram аккаунт?');">
                    <i data-lucide="unlink"></i>
                    Отвязать Telegram
                </button>
            </form>
            
            <?php else: ?>
            <div class="flex items-center gap-md mb-md">
                <div class="telegram-icon">
                    <i data-lucide="send"></i>
                </div>
                <div>
                    <div class="font-semibold">Telegram не привязан</div>
                    <div class="text-sm text-secondary">Привяжите для получения уведомлений</div>
                </div>
            </div>
            
            <div class="telegram-widget">
                <p class="text-sm text-secondary mb-sm">Нажмите кнопку для авторизации через Telegram:</p>
                <script async src="https://telegram.org/js/telegram-widget.js?22" 
                        data-telegram-login="<?= e($telegramBotUsername) ?>" 
                        data-size="large" 
                        data-onauth="onTelegramAuth(user)" 
                        data-request-access="write"></script>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Геолокация -->
    <section class="section">
        <h3 class="section-title">Геолокация</h3>
        <div class="card">
            <div class="flex items-center justify-between mb-md">
                <div class="flex items-center gap-md">
                    <div class="list-item-icon" style="background: <?= $user['geolocation_enabled'] ? 'var(--color-success)' : 'var(--color-warning)' ?>;">
                        <i data-lucide="map-pin"></i>
                    </div>
                    <div>
                        <div class="font-semibold">Отслеживание локации</div>
                        <div class="text-sm text-secondary">
                            <?= $user['geolocation_enabled'] ? 'Включено' : 'Выключено' ?>
                        </div>
                    </div>
                </div>
                <?php if (!$user['geolocation_enabled']): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="requestGeolocation()">
                    Включить
                </button>
                <?php endif; ?>
            </div>
            
            <?php if ($user['last_location_update']): ?>
            <div class="text-sm text-secondary">
                Последнее обновление: <?= timeAgo($user['last_location_update']) ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Безопасность -->
    <section class="section">
        <h3 class="section-title">Безопасность</h3>
        <div class="card">
            <form method="POST" action="/settings/password" data-ajax data-reload>
                <div class="form-group">
                    <label class="form-label" for="current_password">Текущий пароль</label>
                    <input type="password" id="current_password" name="current_password" class="form-input" placeholder="Введите текущий пароль">
                </div>
                <div class="form-group">
                    <label class="form-label" for="new_password">Новый пароль</label>
                    <input type="password" id="new_password" name="new_password" class="form-input" placeholder="Минимум 6 символов" minlength="6">
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Подтвердите пароль</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Повторите новый пароль">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Изменить пароль</button>
            </form>
        </div>
    </section>
    
    <!-- О приложении -->
    <section class="section">
        <h3 class="section-title">О приложении</h3>
        <div class="card">
            <div class="flex justify-between items-center mb-sm">
                <span class="text-secondary">Версия</span>
                <span class="font-medium"><?= APP_VERSION ?></span>
            </div>
            <div class="flex justify-between items-center mb-sm">
                <span class="text-secondary">Telegram бот</span>
                <a href="https://t.me/<?= e($telegramBotUsername) ?>" target="_blank" class="font-medium text-primary">
                    @<?= e($telegramBotUsername) ?>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Выход -->
    <section class="section">
        <a href="/logout" class="btn btn-danger btn-block" onclick="return confirm('Вы уверены, что хотите выйти?');">
            <i data-lucide="log-out"></i>
            Выйти из аккаунта
        </a>
    </section>
</section>

<script>
function onTelegramAuth(user) {
    fetch('/settings/telegram', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(user)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            TesaTour.showToast('success', 'Telegram успешно привязан!');
            setTimeout(function() { window.location.reload(); }, 1000);
        } else {
            TesaTour.showToast('error', data.message || 'Ошибка привязки Telegram');
        }
    })
    .catch(function() {
        TesaTour.showToast('error', 'Ошибка соединения');
    });
}
</script>
