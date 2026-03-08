<?php 
$userModel = new User();
?>

<section class="section">
    <a href="/groups/<?= $group['id'] ?>/members" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        Назад к участникам
    </a>
    
    <!-- Информация об участнике -->
    <div class="card mb-lg">
        <div class="flex items-center gap-md">
            <div class="avatar avatar-lg">
                <?php if ($member['avatar']): ?>
                    <img src="<?= e(uploads($member['avatar'])) ?>" alt="Аватар">
                <?php else: ?>
                    <i data-lucide="user"></i>
                <?php endif; ?>
            </div>
            <div>
                <h1 class="text-lg font-semibold"><?= e($userModel->getFullName($member)) ?></h1>
                <p class="text-sm text-secondary">
                    <?php if ($lastLocation): ?>
                        Последняя активность: <?= timeAgo($lastLocation['updated_at']) ?>
                    <?php else: ?>
                        Геолокация недоступна
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Карта -->
    <div class="card mb-lg">
        <h3 class="card-title mb-md">
            <?php if ($lastLocation): ?>
                Последнее местоположение
            <?php else: ?>
                Местоположение неизвестно
            <?php endif; ?>
        </h3>
        <div class="map-container map-container-full" id="locationMap"></div>
        
        <?php if ($lastLocation): ?>
        <div class="mt-md">
            <div class="flex justify-between text-sm">
                <span class="text-secondary">Координаты:</span>
                <span class="font-medium"><?= round($lastLocation['latitude'], 6) ?>, <?= round($lastLocation['longitude'], 6) ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- История локаций -->
    <h3 class="section-title">История перемещений</h3>
    
    <?php if (empty($locationHistory)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i data-lucide="map-pin-off"></i>
        </div>
        <h4 class="empty-state-title">Нет данных</h4>
        <p class="empty-state-text">История геолокации пока недоступна</p>
    </div>
    <?php else: ?>
    <div class="flex flex-col gap-sm">
        <?php 
        $currentDate = null;
        foreach ($locationHistory as $location): 
            $locationDate = date('Y-m-d', strtotime($location['recorded_at']));
            if ($currentDate !== $locationDate):
                $currentDate = $locationDate;
        ?>
        <div class="text-sm text-secondary font-medium mt-md mb-xs">
            <?= date('d.m.Y', strtotime($location['recorded_at'])) ?>
        </div>
        <?php endif; ?>
        
        <div class="list-item location-item" 
             data-lat="<?= $location['latitude'] ?>" 
             data-lng="<?= $location['longitude'] ?>"
             onclick="showOnMap(<?= $location['latitude'] ?>, <?= $location['longitude'] ?>)">
            <div class="list-item-icon" style="background: var(--color-primary); width: 32px; height: 32px;">
                <i data-lucide="map-pin" style="font-size: 14px;"></i>
            </div>
            <div class="list-item-content">
                <div class="list-item-title text-sm">
                    <?= date('H:i:s', strtotime($location['recorded_at'])) ?>
                </div>
                <div class="list-item-subtitle text-xs">
                    <?= round($location['latitude'], 6) ?>, <?= round($location['longitude'], 6) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<script>
let map = null;
let currentPlacemark = null;

document.addEventListener('DOMContentLoaded', function() {
    <?php if ($lastLocation): ?>
    TesaTour.initMap('locationMap', [<?= $lastLocation['latitude'] ?>, <?= $lastLocation['longitude'] ?>], 15).then(function(m) {
        map = m;
        
        // Показываем последнюю локацию
        TesaTour.addPlacemark(
            [<?= $lastLocation['latitude'] ?>, <?= $lastLocation['longitude'] ?>],
            {
                content: '<strong>Последняя локация</strong><br><?= formatDateTime($lastLocation['updated_at']) ?>',
                hint: 'Последняя локация',
                preset: 'islands#redCircleDotIcon'
            }
        );
        
        // Добавляем все точки истории на карту
        <?php foreach ($locationHistory as $i => $loc): ?>
        <?php if ($i > 0): // Пропускаем первую, она уже отмечена красным ?>
        TesaTour.addPlacemark(
            [<?= $loc['latitude'] ?>, <?= $loc['longitude'] ?>],
            {
                content: '<?= formatDateTime($loc['recorded_at']) ?>',
                preset: 'islands#blueCircleDotIcon'
            }
        );
        <?php endif; ?>
        <?php endforeach; ?>
    });
    <?php else: ?>
    TesaTour.initMap('locationMap').then(function(m) {
        map = m;
    });
    <?php endif; ?>
});

function showOnMap(lat, lng) {
    if (map) {
        map.setCenter([lat, lng], 17, { duration: 300 });
    }
}
</script>
