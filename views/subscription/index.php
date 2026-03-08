<?php
$pageTitle = 'Управление подпиской';
$user = $user ?? [];
$subscription = $subscription ?? null;

$isActive = $subscription && $subscription['status'] === 'active' && strtotime($subscription['end_date']) > time();
?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Управление подпиской</h1>
    </div>

    <?php if (($user['account_type'] ?? 'amateur') !== 'agency'): ?>
        <div class="card">
            <div class="card-body text-center" style="padding: 3rem 1.5rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🎯</div>
                <h2 style="margin-bottom: 1rem;">Подписка для турагентств</h2>
                <p style="color: #666; margin-bottom: 2rem;">
                    Подписка доступна только для аккаунтов с типом "Турагентство".<br>
                    Обратитесь в службу поддержки для изменения типа аккаунта.
                </p>
                <a href="/settings" class="btn btn-primary">Настройки аккаунта</a>
            </div>
        </div>
    <?php else: ?>
        <div class="subscription-container">
            <?php if ($isActive): ?>
                <div class="card subscription-active">
                    <div class="card-body">
                        <div class="subscription-status">
                            <div class="status-badge status-active">Активна</div>
                            <div class="subscription-info">
                                <h3>Подписка турагентства</h3>
                                <p class="subscription-price">499 ₽/мес</p>
                            </div>
                        </div>
                        
                        <div class="subscription-details">
                            <div class="detail-item">
                                <span class="detail-label">Начало:</span>
                                <span class="detail-value"><?= date('d.m.Y', strtotime($subscription['start_date'])) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Окончание:</span>
                                <span class="detail-value"><?= date('d.m.Y', strtotime($subscription['end_date'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="subscription-features">
                            <h4>Преимущества подписки:</h4>
                            <ul class="features-list">
                                <li>Неограниченное количество групп</li>
                                <li>Все функции каналов и чатов</li>
                                <li>Опасные зоны на маршрутах</li>
                                <li>Приоритетная поддержка</li>
                            </ul>
                        </div>
                        
                        <div class="subscription-actions">
                            <button onclick="renewSubscription()" class="btn btn-primary">
                                Продлить подписку
                            </button>
                            <button onclick="cancelSubscription()" class="btn btn-outline">
                                Отменить автопродление
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card subscription-inactive">
                    <div class="card-body">
                        <div class="subscription-status">
                            <div class="status-badge status-inactive">Неактивна</div>
                            <div class="subscription-info">
                                <h3>Подписка турагентства</h3>
                                <p class="subscription-price">499 ₽/мес</p>
                            </div>
                        </div>
                        
                        <div class="subscription-features">
                            <h4>Что вы получите:</h4>
                            <ul class="features-list">
                                <li>Неограниченное количество групп</li>
                                <li>Все функции каналов и чатов</li>
                                <li>Опасные зоны на маршрутах</li>
                                <li>Приоритетная поддержка</li>
                                <li>Статистика и аналитика</li>
                            </ul>
                        </div>
                        
                        <div class="subscription-actions">
                            <button onclick="activateSubscription()" class="btn btn-primary btn-lg">
                                Оформить подписку
                            </button>
                        </div>
                        
                        <p class="subscription-note">
                            Оформляя подписку, вы соглашаетесь с 
                            <a href="/legal/terms">пользовательским соглашением</a>, 
                            <a href="/legal/privacy">политикой конфиденциальности</a> и 
                            <a href="/legal/offer">офертой</a>.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function activateSubscription() {
    if (!confirm('Оформить подписку за 499 рублей в месяц?')) {
        return;
    }
    
    fetch('/subscription/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.confirmation_url;
        } else {
            alert(data.message || 'Ошибка создания платежа');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка');
    });
}

function renewSubscription() {
    activateSubscription();
}

function cancelSubscription() {
    if (!confirm('Вы уверены, что хотите отменить автопродление подписки?')) {
        return;
    }
    
    fetch('/subscription/cancel', {
        method: 'POST'
    })
    .then(response => {
        if (response.ok) {
            window.location.reload();
        } else {
            alert('Ошибка отмены подписки');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка');
    });
}
</script>

<style>
.subscription-container {
    max-width: 600px;
    margin: 0 auto;
}

.subscription-active,
.subscription-inactive {
    margin-bottom: 2rem;
}

.subscription-status {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-active {
    background: #dcfce7;
    color: #16a34a;
}

.status-inactive {
    background: #fee2e2;
    color: #dc2626;
}

.subscription-info h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1.25rem;
}

.subscription-price {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #007AFF;
}

.subscription-details {
    display: grid;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
}

.detail-label {
    color: #666;
}

.detail-value {
    font-weight: 600;
}

.subscription-features {
    margin-bottom: 2rem;
}

.subscription-features h4 {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    font-weight: 600;
}

.features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.features-list li {
    padding: 0.75rem 0;
    padding-left: 2rem;
    position: relative;
}

.features-list li:before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #16a34a;
    font-weight: 700;
    font-size: 1.25rem;
}

.subscription-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.subscription-actions .btn {
    flex: 1;
}

.subscription-note {
    text-align: center;
    font-size: 0.875rem;
    color: #666;
    margin: 1rem 0 0;
}

.subscription-note a {
    color: #007AFF;
    text-decoration: none;
}

.subscription-note a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .subscription-actions {
        flex-direction: column;
    }
    
    .subscription-status {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
