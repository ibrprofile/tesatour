<?php 
$userModel = new User();
?>

<section class="section">
    <a href="/groups/<?= $group['id'] ?>" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        Назад к группе
    </a>
    
    <h1 class="text-2xl font-bold mb-lg">История SOS-вызовов</h1>
    
    <?php if (empty($alerts)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i data-lucide="shield-check"></i>
        </div>
        <h4 class="empty-state-title">SOS-вызовов нет</h4>
        <p class="empty-state-text">В этой группе пока не было экстренных вызовов</p>
    </div>
    <?php else: ?>
    
    <div class="flex flex-col gap-md">
        <?php foreach ($alerts as $alert): ?>
        <a href="/sos/<?= $alert['id'] ?>" class="card" style="text-decoration: none; color: inherit;">
            <div class="flex items-start gap-md">
                <div class="avatar">
                    <?php if ($alert['avatar']): ?>
                        <img src="<?= e(uploads($alert['avatar'])) ?>" alt="Аватар">
                    <?php else: ?>
                        <i data-lucide="user"></i>
                    <?php endif; ?>
                </div>
                
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-xs">
                        <span class="font-semibold"><?= e($alert['last_name'] . ' ' . $alert['first_name']) ?></span>
                        <span class="badge <?= $alert['status'] === 'active' ? 'badge-danger' : 'status-resolved' ?>">
                            <?= $alert['status'] === 'active' ? 'Активен' : 'Закрыт' ?>
                        </span>
                    </div>
                    
                    <?php if ($alert['comment']): ?>
                    <p class="text-sm text-secondary mb-sm"><?= e($alert['comment']) ?></p>
                    <?php endif; ?>
                    
                    <div class="flex items-center gap-md text-xs text-secondary">
                        <span>
                            <i data-lucide="calendar" style="font-size: 12px;"></i>
                            <?= formatDateTime($alert['created_at']) ?>
                        </span>
                        <?php if ($alert['resolved_at']): ?>
                        <span>
                            <i data-lucide="check-circle" style="font-size: 12px;"></i>
                            Закрыт: <?= e($alert['resolved_by_last_name'] . ' ' . $alert['resolved_by_first_name']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <i data-lucide="chevron-right" class="text-secondary"></i>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
</section>