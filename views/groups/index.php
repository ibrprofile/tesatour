<?php $groupModel = new Group(); ?>

<section class="section">
    <div class="flex items-center justify-between mb-md">
        <h1 class="text-2xl font-bold">Мои группы</h1>
        <a href="/groups/create" class="btn btn-primary">
            <i data-lucide="plus"></i>
            Создать
        </a>
    </div>
    
    <?php if (empty($groups)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
            <i data-lucide="users"></i>
            </div>
        <h4 class="empty-state-title">Нет групп</h4>
        <p class="empty-state-text">Создайте свою первую туристическую группу или присоединитесь по приглашению</p>
        <a href="/groups/create" class="btn btn-primary">
            <i data-lucide="plus"></i>
            Создать группу
        </a>
    </div>
    <?php else: ?>
    
    <?php
    $activeGroups = array_filter($groups, function ($g) {
        return $g['status'] === 'active';
    });
    $closedGroups = array_filter($groups, function ($g) {
        return $g['status'] === 'closed';
    });
    ?>
    
    <?php if (!empty($activeGroups)): ?>
    <h3 class="section-title">Активные</h3>
    <div class="flex flex-col gap-sm mb-lg">
        <?php foreach ($activeGroups as $group): ?>
        <a href="/groups/<?= $group['id'] ?>" class="list-item">
            <div class="list-item-icon">
                <i data-lucide="users"></i>
            </div>
            <div class="list-item-content">
                <div class="list-item-title"><?= e($group['name']) ?></div>
                <div class="list-item-subtitle">
                    <?= $group['members_count'] ?> <?= pluralize($group['members_count'], 'участник', 'участника', 'участников') ?>
                </div>
            </div>
            <span class="badge role-<?= $group['role'] ?>"><?= $groupModel->getRoleName($group['role']) ?></span>
            <i data-lucide="chevron-right" class="list-item-arrow"></i>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($closedGroups)): ?>
    <h3 class="section-title">Завершённые</h3>
    <div class="flex flex-col gap-sm">
        <?php foreach ($closedGroups as $group): ?>
        <a href="/groups/<?= $group['id'] ?>" class="list-item" style="opacity: 0.7;">
            <div class="list-item-icon" style="background: var(--color-text-secondary);">
                <i data-lucide="users"></i>
            </div>
            <div class="list-item-content">
                <div class="list-item-title"><?= e($group['name']) ?></div>
                <div class="list-item-subtitle">
                    <?= $group['members_count'] ?> <?= pluralize($group['members_count'], 'участник', 'участника', 'участников') ?>
                    <span class="badge status-closed ml-sm">Закрыта</span>
                </div>
            </div>
            <i data-lucide="chevron-right" class="list-item-arrow"></i>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
</section>
