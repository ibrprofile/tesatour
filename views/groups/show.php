<?php 
$groupModel = new Group();
$userModel = new User();
?>

<section class="section">
    <a href="/groups" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i> Назад
    </a>
    
    <!-- Group Header -->
    <div class="card mb-lg">
        <div style="display:flex;align-items:center;gap:var(--spacing-md);margin-bottom:var(--spacing-md);">
            <div style="width:56px;height:56px;border-radius:16px;background:<?= $group['status'] === 'active' ? 'rgba(52,199,89,0.1)' : 'rgba(142,142,147,0.1)' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="compass" style="color:<?= $group['status'] === 'active' ? '#34C759' : '#8E8E93' ?>;width:28px;height:28px;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <h1 style="font-size:1.25rem;font-weight:700;margin-bottom:2px;"><?= e($group['name']) ?></h1>
                <div class="text-sm text-secondary">
                    <?= count($members) ?> <?= pluralize(count($members), 'участник', 'участника', 'участников') ?>
                    &mdash;
                    <span style="color:<?= $group['status'] === 'active' ? '#34C759' : '#8E8E93' ?>;"><?= $group['status'] === 'active' ? 'Активна' : 'Закрыта' ?></span>
                </div>
            </div>
        </div>
        
        <?php if ($group['description']): ?>
        <p class="text-secondary" style="margin-bottom:var(--spacing-md);"><?= e($group['description']) ?></p>
        <?php endif; ?>
        
        <div style="display:flex;align-items:center;gap:var(--spacing-sm);">
            <span class="badge role-<?= $userRole ?>"><?= $groupModel->getRoleName($userRole) ?></span>
            <span class="text-sm text-secondary">
                Владелец: <?= e($group['owner_first_name'] . ' ' . $group['owner_last_name']) ?>
            </span>
        </div>
    </div>
    
    <!-- Active SOS Alerts -->
    <?php if (!empty($activeAlerts)): ?>
    <section class="section">
        <h3 class="section-title" style="color:var(--color-danger);">Активные SOS</h3>
        <?php foreach ($activeAlerts as $alert): ?>
        <a href="/sos/<?= $alert['id'] ?>" class="sos-alert-card mb-sm" style="display:block;text-decoration:none;color:inherit;">
            <div class="sos-alert-header">
                <div class="sos-alert-icon"><i data-lucide="alert-triangle"></i></div>
                <div style="flex:1;">
                    <div class="font-semibold text-danger">SOS!</div>
                    <div class="text-sm"><?= e($alert['last_name'] . ' ' . $alert['first_name']) ?></div>
                </div>
                <div class="text-sm text-secondary"><?= timeAgo($alert['created_at']) ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
    
    <!-- Active Route -->
    <?php if ($activeRoute): ?>
    <section class="section">
        <h3 class="section-title">Активный маршрут</h3>
        <a href="/routes/<?= $activeRoute['id'] ?>" class="list-item">
            <div class="list-item-icon" style="background:var(--color-warning);"><i data-lucide="route"></i></div>
            <div class="list-item-content">
                <div class="list-item-title"><?= e($activeRoute['title']) ?></div>
                <div class="list-item-subtitle"><?= e($activeRoute['creator_first_name'] . ' ' . $activeRoute['creator_last_name']) ?></div>
            </div>
            <i data-lucide="chevron-right" class="list-item-arrow"></i>
        </a>
    </section>
    <?php endif; ?>
    
    <!-- Menu Items (Telegram Settings Style) -->
    <section class="section">
        <h3 class="section-title">Управление</h3>
        <div class="card" style="padding:0;overflow:hidden;">
            
            <?php if ($group['status'] === 'active'): ?>
            <button type="button" onclick="openSosModal()" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);width:100%;text-align:left;background:none;border:none;border-bottom:1px solid var(--color-border);cursor:pointer;font:inherit;color:inherit;">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(255,59,48,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="phone-call" style="width:16px;height:16px;color:var(--color-danger);"></i>
                </div>
                <div style="flex:1;"><div class="font-semibold" style="color:var(--color-danger);font-size:0.9375rem;">Отправить SOS</div></div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
            </button>
            <?php endif; ?>
            
            <a href="/groups/<?= $group['id'] ?>/chat" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);text-decoration:none;color:inherit;border-bottom:1px solid var(--color-border);">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(52,199,89,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="message-square" style="width:16px;height:16px;color:#34C759;"></i>
                </div>
                <div style="flex:1;"><div style="font-size:0.9375rem;">Общий чат</div></div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
            </a>
            
            <a href="/groups/<?= $group['id'] ?>/channels" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);text-decoration:none;color:inherit;border-bottom:1px solid var(--color-border);">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(88,86,214,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="radio" style="width:16px;height:16px;color:#5856D6;"></i>
                </div>
                <div style="flex:1;"><div style="font-size:0.9375rem;">Каналы</div></div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
            </a>
            
            <a href="/groups/<?= $group['id'] ?>/members" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);text-decoration:none;color:inherit;border-bottom:1px solid var(--color-border);">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(0,122,255,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="users" style="width:16px;height:16px;color:#007AFF;"></i>
                </div>
                <div style="flex:1;"><div style="font-size:0.9375rem;">Участники</div><div class="text-sm text-secondary"><?= count($members) ?> чел.</div></div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
            </a>
            
            <a href="/groups/<?= $group['id'] ?>/routes" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);text-decoration:none;color:inherit;border-bottom:1px solid var(--color-border);">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(255,149,0,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="route" style="width:16px;height:16px;color:#FF9500;"></i>
                </div>
                <div style="flex:1;"><div style="font-size:0.9375rem;">Маршруты</div></div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
            </a>
            
            <a href="/groups/<?= $group['id'] ?>/danger-zones" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);text-decoration:none;color:inherit;border-bottom:1px solid var(--color-border);">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(255,59,48,0.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="alert-triangle" style="width:16px;height:16px;color:#FF9500;"></i>
                </div>
                <div style="flex:1;"><div style="font-size:0.9375rem;">Опасные зоны</div></div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
            </a>
            
            <a href="/groups/<?= $group['id'] ?>/sos" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);text-decoration:none;color:inherit;<?= $canEdit ? 'border-bottom:1px solid var(--color-border);' : '' ?>">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(255,59,48,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="siren" style="width:16px;height:16px;color:#FF3B30;"></i>
                </div>
                <div style="flex:1;"><div style="font-size:0.9375rem;">История SOS</div></div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
            </a>
            
            <?php if ($canEdit): ?>
            <a href="/groups/<?= $group['id'] ?>/invites" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);text-decoration:none;color:inherit;border-bottom:1px solid var(--color-border);">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(52,199,89,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="link" style="width:16px;height:16px;color:#34C759;"></i>
                </div>
                <div style="flex:1;"><div style="font-size:0.9375rem;">Приглашения</div></div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
            </a>
            
            <a href="/groups/<?= $group['id'] ?>/blacklist" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);text-decoration:none;color:inherit;border-bottom:1px solid var(--color-border);">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(142,142,147,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="shield-off" style="width:16px;height:16px;color:#8E8E93;"></i>
                </div>
                <div style="flex:1;"><div style="font-size:0.9375rem;">Черный список</div></div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
            </a>
            
            <a href="/groups/<?= $group['id'] ?>/settings" style="display:flex;align-items:center;gap:var(--spacing-md);padding:var(--spacing-md);text-decoration:none;color:inherit;">
                <div style="width:30px;height:30px;border-radius:8px;background:rgba(88,86,214,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="settings" style="width:16px;height:16px;color:#5856D6;"></i>
                </div>
                <div style="flex:1;"><div style="font-size:0.9375rem;">Настройки</div></div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
            </a>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Actions -->
    <section class="section">
        <?php if ($userRole !== ROLE_OWNER && $group['status'] === 'active'): ?>
        <form method="POST" action="/groups/<?= $group['id'] ?>/leave" data-ajax>
            <button type="submit" class="btn btn-outline btn-block mb-sm" onclick="return confirm('Покинуть группу?');">
                <i data-lucide="log-out"></i> Покинуть группу
            </button>
        </form>
        <?php endif; ?>
        
        <?php if ($canClose && $group['status'] === 'active'): ?>
        <form method="POST" action="/groups/<?= $group['id'] ?>/close" data-ajax>
            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Закрыть группу? Это необратимо.');">
                <i data-lucide="x-circle"></i> Закрыть группу
            </button>
        </form>
        <?php endif; ?>
    </section>
</section>

<!-- SOS Modal -->
<div id="sosModal" class="modal" style="display:none;">
    <div class="modal-backdrop" onclick="closeSosModal()"></div>
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeSosModal()"><i data-lucide="x"></i></button>
        <div class="modal-icon" style="background:rgba(255,59,48,0.15);">
            <i data-lucide="alert-triangle" style="color:var(--color-danger);"></i>
        </div>
        <h3 style="margin-bottom:var(--spacing-sm);">Отправить SOS</h3>
        <p class="text-secondary text-sm">Все участники получат уведомление о вашем местоположении.</p>
        
        <div class="form-group mt-md">
            <label class="form-label">Комментарий (необязательно)</label>
            <textarea id="sosComment" class="form-input" placeholder="Опишите ситуацию..." rows="3"></textarea>
        </div>
        
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeSosModal()">Отмена</button>
            <button type="button" class="btn btn-danger" onclick="confirmSendSOS()">
                <i data-lucide="phone-call"></i> Отправить
            </button>
        </div>
    </div>
</div>

<script>
function openSosModal() { document.getElementById('sosModal').style.display = 'flex'; if (typeof lucide !== 'undefined') lucide.createIcons(); }
function closeSosModal() { document.getElementById('sosModal').style.display = 'none'; }
function confirmSendSOS() {
    var comment = document.getElementById('sosComment').value;
    TesaTour.sendSOS(<?= $group['id'] ?>, comment);
    closeSosModal();
}
</script>
