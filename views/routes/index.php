<?php 
$userModel = new User();
?>

<section class="section">
    <a href="/groups/<?= $group['id'] ?>" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        Назад к группе
    </a>
    
    <div class="flex items-center justify-between mb-lg">
        <h1 class="text-2xl font-bold">Маршруты</h1>
        <?php if ($canCreate && $group['status'] === 'active'): ?>
        <a href="/groups/<?= $group['id'] ?>/routes/create" class="btn btn-primary btn-sm">
            <i data-lucide="plus"></i>
            Создать
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($routes)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i data-lucide="route"></i>
        </div>
        <h4 class="empty-state-title">Маршрутов нет</h4>
        <p class="empty-state-text">Создайте план-лист для вашего похода</p>
        <?php if ($canCreate && $group['status'] === 'active'): ?>
        <a href="/groups/<?= $group['id'] ?>/routes/create" class="btn btn-primary">
            <i data-lucide="plus"></i>
            Создать маршрут
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    
    <div class="flex flex-col gap-sm">
        <?php foreach ($routes as $route): ?>
        <a href="/routes/<?= $route['id'] ?>" class="list-item">
            <div class="list-item-icon" style="background: <?= $route['is_active'] ? 'var(--color-warning)' : 'var(--color-text-secondary)' ?>;">
                <i data-lucide="route"></i>
            </div>
            <div class="list-item-content">
                <div class="list-item-title">
                    <?= e($route['title']) ?>
                    <?php if ($route['is_active']): ?>
                    <span class="badge badge-warning ml-sm">Активный</span>
                    <?php endif; ?>
                </div>
                <div class="list-item-subtitle">
                    <?= $route['points_count'] ?> <?= pluralize($route['points_count'], 'точка', 'точки', 'точек') ?>
                    &bull;
                    <?= e($route['creator_first_name'] . ' ' . $route['creator_last_name']) ?>
                </div>
            </div>
            <i data-lucide="chevron-right" class="list-item-arrow"></i>
        </a>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
</section>