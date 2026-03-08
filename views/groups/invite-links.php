<?php 
$groupModel = new Group();
?>

<section class="section">
    <a href="/groups/<?= $group['id'] ?>" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        Назад
    </a>
    
    <h1 class="text-xl font-bold mb-lg"><?= e($group['name']) ?> - Приглашения</h1>
    
    <!-- Пригласительная ссылка -->
    <section class="section">
        <h3 class="section-title">Пригласительная ссылка</h3>
        <div class="card mb-md">
            <p class="text-sm text-secondary mb-md">Поделитесь этой ссылкой для приглашения новых участников</p>
            <div class="flex gap-sm">
                <input 
                    type="text" 
                    class="form-input flex-1" 
                    value="<?= e($inviteUrl) ?>" 
                    readonly
                    id="inviteUrl"
                >
                <button type="button" class="btn btn-primary" onclick="TesaTour.copyToClipboard('<?= e($inviteUrl) ?>')">
                    <i data-lucide="copy"></i>
                </button>
            </div>
        </div>
    </section>
    
    <!-- Настройки принятия заявок -->
    <section class="section">
        <h3 class="section-title">Настройки приглашений</h3>
        <form method="POST" action="/groups/<?= $group['id'] ?>/invites/toggle" data-ajax data-reload>
            <label class="toggle-label">
                <div class="toggle-info">
                    <div class="toggle-title">Принимать заявки</div>
                    <div class="toggle-description">
                        Пользователи смогут вступить в группу только после одобрения администратора
                    </div>
                </div>
                <label class="toggle">
                    <input 
                        type="checkbox" 
                        name="require_approval" 
                        value="1"
                        <?= $group['require_approval'] ? 'checked' : '' ?>
                        onchange="this.form.submit()"
                    >
                    <span class="toggle-slider"></span>
                </label>
            </label>
        </form>
    </section>
    
    <!-- Ожидающие заявки -->
    <?php if ($group['require_approval'] && !empty($pendingRequests)): ?>
    <section class="section">
        <h3 class="section-title">Ожидающие заявки (<?= count($pendingRequests) ?>)</h3>
        <?php foreach ($pendingRequests as $request): ?>
        <div class="request-card">
            <div class="request-header">
                <div class="avatar">
                    <?php if ($request['avatar']): ?>
                        <img src="<?= uploads($request['avatar']) ?>" alt="<?= e($request['first_name']) ?>">
                    <?php else: ?>
                        <i data-lucide="user"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <div class="font-semibold"><?= e($request['last_name'] . ' ' . $request['first_name']) ?></div>
                    <div class="text-sm text-secondary"><?= timeAgo($request['created_at']) ?></div>
                </div>
            </div>
            <div class="request-actions">
                <form method="POST" action="/groups/<?= $group['id'] ?>/invites/approve/<?= $request['id'] ?>" data-ajax data-reload style="flex: 1;">
                    <button type="submit" class="btn btn-success btn-sm btn-block">
                        <i data-lucide="check"></i>
                        Одобрить
                    </button>
                </form>
                <form method="POST" action="/groups/<?= $group['id'] ?>/invites/reject/<?= $request['id'] ?>" data-ajax data-reload style="flex: 1;">
                    <button type="submit" class="btn btn-secondary btn-sm btn-block">
                        <i data-lucide="x"></i>
                        Отклонить
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </section>
    <?php elseif ($group['require_approval']): ?>
    <section class="section">
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-lucide="inbox"></i>
            </div>
            <div class="empty-state-title">Нет заявок</div>
            <div class="empty-state-text">Пока никто не подал заявку на вступление</div>
        </div>
    </section>
    <?php endif; ?>
</section>
