<?php 
$userModel = new User();
$groupModel = new Group();
$group = $groupModel->findById($route['group_id']);
?>

<section class="section">
    <a href="/groups/<?= $route['group_id'] ?>/routes" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        Назад к маршрутам
    </a>
    
    <!-- Заголовок -->
    <div class="card mb-lg">
        <div class="flex items-start justify-between mb-md">
            <div>
                <h1 class="text-xl font-bold"><?= e($route['title']) ?></h1>
                <p class="text-sm text-secondary mt-xs">
                    Создал: <?= e($route['creator_first_name'] . ' ' . $route['creator_last_name']) ?>
                </p>
            </div>
            <span class="badge <?= $route['is_active'] ? 'badge-warning' : 'status-resolved' ?>">
                <?= $route['is_active'] ? 'Активный' : 'Неактивный' ?>
            </span>
        </div>
        
        <?php if ($route['description']): ?>
        <p class="text-secondary"><?= e($route['description']) ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Карта с точками -->
    <div class="card mb-lg">
        <h3 class="card-title mb-md">
            <i data-lucide="map" class="text-primary"></i>
            Карта маршрута
        </h3>
        <div class="map-container map-container-large" id="routeMap"></div>
    </div>
    
    <!-- Точки маршрута -->
    <section class="section">
        <div class="flex items-center justify-between mb-sm">
            <h3 class="section-title" style="margin-bottom: 0;">
                Точки маршрута (<?= count($points) ?>)
            </h3>
            <?php if ($canEdit): ?>
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddPointModal()">
                <i data-lucide="plus"></i>
                Добавить
            </button>
            <?php endif; ?>
        </div>
        
        <?php if (empty($points)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-lucide="map-pin"></i>
            </div>
            <h4 class="empty-state-title">Точек пока нет</h4>
            <p class="empty-state-text">Добавьте точки маршрута на карте</p>
            <?php if ($canEdit): ?>
            <button type="button" class="btn btn-primary" onclick="openAddPointModal()">
                <i data-lucide="plus"></i>
                Добавить точку
            </button>
            <?php endif; ?>
        </div>
        <?php else: ?>
        
        <div class="flex flex-col gap-sm">
            <?php foreach ($points as $index => $point): ?>
            <div class="route-point" 
                 data-lat="<?= $point['latitude'] ?>" 
                 data-lng="<?= $point['longitude'] ?>"
                 onclick="showPointOnMap(<?= $point['latitude'] ?>, <?= $point['longitude'] ?>)">
                <div class="route-point-number"><?= $index + 1 ?></div>
                <div class="route-point-content">
                    <div class="route-point-title"><?= e($point['title']) ?></div>
                    <?php if ($point['description']): ?>
                    <div class="route-point-subtitle"><?= e($point['description']) ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($canEdit): ?>
                <div class="route-point-actions">
                    <form method="POST" action="/routes/points/<?= $point['id'] ?>/delete" style="display: inline;">
                        <button type="submit" 
                                class="btn btn-icon btn-sm btn-ghost text-danger" 
                                title="Удалить"
                                onclick="event.stopPropagation(); return confirm('Удалить точку?');">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php endif; ?>
    </section>
    
    <!-- Редактирование маршрута -->
    <?php if ($canEdit): ?>
    <section class="section">
        <h3 class="section-title">Настройки маршрута</h3>
        <div class="card">
            <form method="POST" action="/routes/<?= $route['id'] ?>/update" data-ajax data-reload>
                <div class="form-group">
                    <label class="form-label" for="edit_title">Название</label>
                    <input 
                        type="text" 
                        id="edit_title" 
                        name="title" 
                        class="form-input" 
                        value="<?= e($route['title']) ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="edit_description">Описание</label>
                    <textarea 
                        id="edit_description" 
                        name="description" 
                        class="form-input" 
                        rows="3"
                    ><?= e($route['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="flex items-center gap-md" style="cursor: pointer;">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            value="1"
                            <?= $route['is_active'] ? 'checked' : '' ?>
                            style="width: 20px; height: 20px;"
                        >
                        <span>Активный маршрут (виден всем участникам)</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Сохранить изменения
                </button>
            </form>
        </div>
    </section>
    
    <section class="section">
        <form method="POST" action="/routes/<?= $route['id'] ?>/delete" data-ajax>
            <button type="submit" class="btn btn-outline btn-block text-danger" onclick="return confirm('Удалить маршрут и все его точки?');">
                <i class="lucide-trash-2"></i>
                Удалить маршрут
            </button>
        </form>
    </section>
    <?php endif; ?>
</section>

<!-- Модальное окно добавления точки -->
<?php if ($canEdit): ?>
<div id="addPointModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeAddPointModal()"></div>
    <div class="modal-content" style="max-width: 500px;">
        <button type="button" class="modal-close" onclick="closeAddPointModal()">
            <i class="lucide-x"></i>
        </button>
        <h3>Добавить точку</h3>
        <p class="text-secondary mb-md">Кликните на карту, чтобы выбрать местоположение</p>
        
        <div class="map-container mb-md" id="selectPointMap"></div>
        
        <form id="addPointForm" method="POST" action="/routes/<?= $route['id'] ?>/points">
            <input type="hidden" name="latitude" id="pointLatitude">
            <input type="hidden" name="longitude" id="pointLongitude">
            
            <div class="form-group">
                <label class="form-label" for="pointTitle">Название точки *</label>
                <input 
                    type="text" 
                    id="pointTitle" 
                    name="title" 
                    class="form-input" 
                    placeholder="Например: Базовый лагерь"
                    required
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="pointDescription">Описание</label>
                <textarea 
                    id="pointDescription" 
                    name="description" 
                    class="form-input" 
                    placeholder="Дополнительная информация..."
                    rows="2"
                ></textarea>
            </div>
            
            <div id="selectedCoords" class="text-sm text-secondary mb-md" style="display: none;">
                Выбраны координаты: <span id="coordsDisplay"></span>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeAddPointModal()">Отмена</button>
                <button type="submit" class="btn btn-primary" id="addPointBtn" disabled>
                    <i data-lucide="plus"></i>
                    Добавить
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
let routeMap = null;
let selectMap = null;
let selectedPlacemark = null;

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация основной карты маршрута
    let firstPoint;
    <?php if (!empty($points)): ?>
    firstPoint = [<?= $points[0]['latitude'] ?>, <?= $points[0]['longitude'] ?>];
    <?php else: ?>
    firstPoint = <?= MAP_DEFAULT_CENTER ?>;
    <?php endif; ?>
    
    TesaTour.initMap('routeMap', firstPoint, 12).then(function(map) {
        routeMap = map;
        
        <?php foreach ($points as $index => $point): ?>
        TesaTour.addPlacemark(
            [<?= $point['latitude'] ?>, <?= $point['longitude'] ?>],
            {
                content: '<strong><?= $index + 1 ?>. <?= e($point['title']) ?></strong><?= $point['description'] ? '<br>' . e($point['description']) : '' ?>',
                hint: '<?= e($point['title']) ?>',
                preset: 'islands#blueCircleIcon'
            }
        );
        <?php endforeach; ?>
        
        <?php if (!empty($points)): ?>
        TesaTour.fitMapToPlacemarks();
        <?php endif; ?>
    });
});

function showPointOnMap(lat, lng) {
    if (routeMap) {
        routeMap.setCenter([lat, lng], 15, { duration: 300 });
    }
}

<?php if ($canEdit): ?>
function openAddPointModal() {
    document.getElementById('addPointModal').style.display = 'flex';
    
    // Инициализация карты для выбора точки
    setTimeout(function() {
        if (!selectMap) {
            ymaps.ready(function() {
                selectMap = new ymaps.Map('selectPointMap', {
                    center: TesaTour.config.mapDefaultCenter,
                    zoom: 10,
                    controls: ['zoomControl', 'geolocationControl', 'searchControl']
                });
                
                // Клик по карте для выбора точки
                selectMap.events.add('click', function(e) {
                    const coords = e.get('coords');
                    setSelectedPoint(coords);
                });
            });
        }
    }, 100);
}

function closeAddPointModal() {
    document.getElementById('addPointModal').style.display = 'none';
    document.getElementById('addPointForm').reset();
    document.getElementById('selectedCoords').style.display = 'none';
    document.getElementById('addPointBtn').disabled = true;
    
    if (selectedPlacemark && selectMap) {
        selectMap.geoObjects.remove(selectedPlacemark);
        selectedPlacemark = null;
    }
}

function setSelectedPoint(coords) {
    document.getElementById('pointLatitude').value = coords[0];
    document.getElementById('pointLongitude').value = coords[1];
    document.getElementById('coordsDisplay').textContent = coords[0].toFixed(6) + ', ' + coords[1].toFixed(6);
    document.getElementById('selectedCoords').style.display = 'block';
    document.getElementById('addPointBtn').disabled = false;
    
    // Обновляем маркер на карте
    if (selectedPlacemark) {
        selectMap.geoObjects.remove(selectedPlacemark);
    }
    
    selectedPlacemark = new ymaps.Placemark(coords, {}, {
        preset: 'islands#redCircleDotIcon'
    });
    selectMap.geoObjects.add(selectedPlacemark);
}

// AJAX отправка формы добавления точки
document.getElementById('addPointForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => data[key] = value);
    
    try {
        const response = await TesaTour.fetchJSON(form.action, {
            method: 'POST',
            body: JSON.stringify(data)
        });
        
        if (response.success) {
            TesaTour.showToast('success', 'Точка добавлена');
            window.location.reload();
        } else {
            TesaTour.showToast('error', response.message || 'Ошибка добавления точки');
        }
    } catch (error) {
        TesaTour.showToast('error', 'Ошибка соединения');
    }
});
<?php endif; ?>
</script>
