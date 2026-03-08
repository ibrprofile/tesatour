<?php 
$userModel = new User();
?>

<section class="section">
    <a href="/groups/<?= $group['id'] ?>" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        Назад
    </a>
    
    <h1 class="text-xl font-bold mb-lg"><?= e($group['name']) ?> - Черный список</h1>
    
    <?php if (!empty($blacklist)): ?>
    <section class="section">
        <h3 class="section-title">Заблокированные пользователи (<?= count($blacklist) ?>)</h3>
        <?php foreach ($blacklist as $item): ?>
        <div class="blacklist-item">
            <div class="blacklist-info">
                <div class="avatar">
                    <?php if ($item['avatar']): ?>
                        <img src="<?= uploads($item['avatar']) ?>" alt="<?= e($item['first_name']) ?>">
                    <?php else: ?>
                        <i data-lucide="user"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <div class="font-semibold"><?= e($item['last_name'] . ' ' . $item['first_name']) ?></div>
                    <?php if ($item['reason']): ?>
                        <div class="blacklist-reason"><?= e($item['reason']) ?></div>
                    <?php endif; ?>
                    <div class="text-sm text-secondary">
                        Заблокировал: <?= e($item['banned_by_last_name'] . ' ' . $item['banned_by_first_name']) ?>
                        • <?= timeAgo($item['banned_at']) ?>
                    </div>
                </div>
            </div>
            <form method="POST" action="/groups/<?= $group['id'] ?>/blacklist/remove/<?= $item['user_id'] ?>" data-ajax data-reload>
                <button type="submit" class="btn btn-sm btn-outline" title="Удалить из черного списка">
                    <i data-lucide="trash-2"></i>
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </section>
    <?php else: ?>
    <section class="section">
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-lucide="shield-off"></i>
            </div>
            <div class="empty-state-title">Черный список пуст</div>
            <div class="empty-state-text">Здесь будут отображаться заблокированные пользователи</div>
        </div>
    </section>
    <?php endif; ?>
</section>
