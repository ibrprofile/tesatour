<?php 
$userModel = new User();
?>

<section class="section">
    <?php if (Session::isLoggedIn()): ?>
    <a href="/groups/<?= $sos['group_id'] ?>" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        Назад к группе
    </a>
    <?php else: ?>
    <a href="/" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        На главную
    </a>
    <?php endif; ?>
    
    <!-- Заголовок -->
    <div class="<?= $sos['status'] === 'active' ? 'sos-alert-card' : 'card' ?> mb-lg">
        <div class="flex items-center gap-md mb-md">
            <?php if ($sos['status'] === 'active'): ?>
            <div class="sos-alert-icon">
                <i data-lucide="alert-triangle"></i>
            </div>
            <?php else: ?>
            <div class="avatar avatar-lg" style="background: var(--color-success);">
                <i data-lucide="check" style="color: white;"></i>
            </div>
            <?php endif; ?>
            
            <div class="flex-1">
                <h1 class="text-xl font-bold <?= $sos['status'] === 'active' ? 'text-danger' : '' ?>">
                    <?= $sos['status'] === 'active' ? 'Активный SOS-вызов!' : 'SOS-вызов закрыт' ?>
                </h1>
                <p class="text-sm text-secondary">
                    Группа: <?= e($sos['group_name']) ?>
                </p>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Информация об авторе -->
        <div class="flex items-center gap-md mb-md">
            <div class="avatar avatar-lg">
                <?php if ($sos['avatar']): ?>
                    <img src="<?= e(uploads($sos['avatar'])) ?>" alt="Аватар">
                <?php else: ?>
                    <i data-lucide="user"></i>
                <?php endif; ?>
            </div>
            <div>
                <div class="font-semibold"><?= e($sos['last_name'] . ' ' . $sos['first_name']) ?></div>
                <div class="text-sm text-secondary">
                    <i data-lucide="clock" style="font-size: 12px;"></i>
                    <?= formatDateTime($sos['created_at']) ?>
                </div>
            </div>
        </div>
        
        <?php if ($sos['comment']): ?>
        <div class="card" style="background: var(--color-surface-secondary);">
            <div class="text-sm text-secondary mb-xs">Комментарий:</div>
            <p><?= e($sos['comment']) ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Карта -->
    <div class="card mb-lg">
        <h3 class="card-title mb-md">
            <i data-lucide="map-pin" class="text-danger"></i>
            Местоположение
        </h3>
        <div class="map-container map-container-large" id="sosMap"></div>
        <div class="mt-md">
            <div class="flex justify-between text-sm">
                <span class="text-secondary">Координаты:</span>
                <span class="font-medium"><?= round($sos['latitude'], 6) ?>, <?= round($sos['longitude'], 6) ?></span>
            </div>
        </div>
    </div>
    
    <!-- Действия -->
    <?php if ($canResolve): ?>
    <section class="section">
        <form method="POST" action="/sos/<?= $sos['id'] ?>/resolve" data-ajax>
            <button type="submit" class="btn btn-success btn-block btn-lg" onclick="return confirm('Подтвердить закрытие SOS-вызова?');">
                <i data-lucide="check-circle"></i>
                Закрыть SOS-вызов
            </button>
        </form>
        <p class="text-center text-sm text-secondary mt-sm">
            Закройте вызов, когда человек найден и в безопасности
        </p>
    </section>
    <?php endif; ?>
    
    <!-- Информация о закрытии -->
    <?php if ($sos['status'] === 'resolved' && $sos['resolved_at']): ?>
    <div class="card">
        <div class="flex items-center gap-md">
            <div class="avatar" style="background: var(--color-success);">
                <i data-lucide="check" style="color: white;"></i>
            </div>
            <div>
                <div class="font-semibold text-success">Вызов закрыт</div>
                <div class="text-sm text-secondary"><?= formatDateTime($sos['resolved_at']) ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    TesaTour.initMap('sosMap', [<?= $sos['latitude'] ?>, <?= $sos['longitude'] ?>], 15).then(function(map) {
        if (map) {
            TesaTour.addPlacemark(
                [<?= $sos['latitude'] ?>, <?= $sos['longitude'] ?>],
                {
                    content: '<strong>SOS-вызов</strong><br><?= e($sos['last_name'] . ' ' . $sos['first_name']) ?><br><?= formatDateTime($sos['created_at']) ?>',
                    hint: 'SOS-вызов',
                    preset: 'islands#redCircleDotIcon'
                }
            );
        }
    });
});
</script>