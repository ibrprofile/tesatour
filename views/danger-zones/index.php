<section class="section">
    <a href="/groups/<?= $group['id'] ?>" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i> Назад
    </a>
    
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--spacing-md);">
        <h1 class="text-xl font-bold">Опасные зоны</h1>
        <?php if (in_array($userRole, ['owner', 'admin'])): ?>
        <button class="btn btn-primary btn-sm" onclick="showAddZoneModal()">
            <i data-lucide="alert-triangle" style="width:14px;height:14px;"></i> Добавить
        </button>
        <?php endif; ?>
    </div>

    <!-- Map -->
    <div class="card mb-lg">
        <div id="dangerZonesMap" style="height:300px;border-radius:var(--radius-md);"></div>
    </div>

    <?php if (empty($dangerZones)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="alert-triangle"></i></div>
        <h3 class="empty-state-title">Нет опасных зон</h3>
        <p class="empty-state-text">В этой группе пока не отмечено опасных зон</p>
        <?php if (in_array($userRole, ['owner', 'admin'])): ?>
        <button class="btn btn-primary" onclick="showAddZoneModal()">Добавить первую зону</button>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:var(--spacing-sm);">
        <?php foreach ($dangerZones as $zone): ?>
        <div class="danger-zone-card">
            <div class="danger-zone-header">
                <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,59,48,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="alert-triangle" style="color:var(--color-danger);width:18px;height:18px;"></i>
                </div>
                <div style="flex:1;">
                    <div class="danger-zone-title"><?= e($zone['title']) ?></div>
                    <div class="text-sm text-secondary"><?= e($zone['first_name']) ?> <?= e($zone['last_name']) ?></div>
                </div>
                <span class="badge badge-danger">Опасно</span>
            </div>
            
            <?php if ($zone['description']): ?>
            <p class="danger-zone-description"><?= e($zone['description']) ?></p>
            <?php endif; ?>
            
            <?php if (in_array($userRole, ['owner', 'admin'])): ?>
            <div class="danger-zone-actions">
                <button class="btn btn-sm btn-secondary" onclick="editZone(<?= $zone['id'] ?>, '<?= e($zone['title']) ?>', '<?= e($zone['description'] ?? '') ?>', <?= $zone['latitude'] ?>, <?= $zone['longitude'] ?>)">
                    <i data-lucide="edit-2" style="width:14px;height:14px;"></i> Изменить
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteZone(<?= $zone['id'] ?>)">
                    <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- Add/Edit Zone Modal -->
<div id="addZoneModal" class="modal" style="display:none;">
    <div class="modal-backdrop" onclick="hideAddZoneModal()"></div>
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Добавить опасную зону</h3>
            <button class="modal-close" onclick="hideAddZoneModal()"><i data-lucide="x"></i></button>
        </div>
        <form id="zoneForm" onsubmit="saveZone(event)">
            <input type="hidden" id="zoneId" name="zone_id">
            
            <div class="form-group">
                <label class="form-label">Заголовок</label>
                <input type="text" id="zoneTitle" name="title" class="form-input" placeholder="Крутой спуск" required>
            </div>
            <div class="form-group">
                <label class="form-label">Описание</label>
                <textarea id="zoneDescription" name="description" class="form-input" rows="3" placeholder="Опишите опасность..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Выберите точку на карте</label>
                <div id="zonePickerMap" style="height:300px;border-radius:var(--radius-md);border:1px solid var(--color-border);"></div>
                <input type="hidden" id="zoneLatitude" name="latitude" required>
                <input type="hidden" id="zoneLongitude" name="longitude" required>
                <p class="form-hint">Кликните на карте, чтобы выбрать точку</p>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Сохранить</button>
        </form>
    </div>
</div>

<script>
var map, pickerMap;
var zones = <?= json_encode($dangerZones) ?>;
var selectedMarker = null;
var editingZoneId = null;

ymaps.ready(initMap);

function initMap() {
    var container = document.getElementById('dangerZonesMap');
    if (!container) return;
    
    map = new ymaps.Map('dangerZonesMap', {
        center: <?= defined('MAP_DEFAULT_CENTER') ? MAP_DEFAULT_CENTER : '[55.7558, 37.6173]' ?>,
        zoom: <?= defined('MAP_DEFAULT_ZOOM') ? MAP_DEFAULT_ZOOM : '12' ?>,
        controls: ['zoomControl', 'fullscreenControl']
    });
    
    zones.forEach(function(zone) {
        var pm = new ymaps.Placemark([zone.latitude, zone.longitude], {
            balloonContentHeader: zone.title,
            balloonContentBody: zone.description || '',
            balloonContentFooter: 'Добавил: ' + zone.first_name + ' ' + zone.last_name,
            hintContent: zone.title
        }, {
            preset: 'islands#redIcon'
        });
        map.geoObjects.add(pm);
    });
    
    if (zones.length > 0) {
        map.setBounds(map.geoObjects.getBounds(), { checkZoomRange: true, zoomMargin: 40 });
    }
}

function showAddZoneModal() {
    editingZoneId = null;
    document.getElementById('modalTitle').textContent = 'Добавить опасную зону';
    document.getElementById('zoneForm').reset();
    document.getElementById('zoneId').value = '';
    document.getElementById('addZoneModal').style.display = 'flex';
    setTimeout(function() { initPickerMap(); if (typeof lucide !== 'undefined') lucide.createIcons(); }, 150);
}

function hideAddZoneModal() {
    document.getElementById('addZoneModal').style.display = 'none';
    if (pickerMap) { pickerMap.destroy(); pickerMap = null; }
}

function editZone(zoneId, title, description, lat, lon) {
    editingZoneId = zoneId;
    document.getElementById('modalTitle').textContent = 'Редактировать зону';
    document.getElementById('zoneId').value = zoneId;
    document.getElementById('zoneTitle').value = title;
    document.getElementById('zoneDescription').value = description;
    document.getElementById('zoneLatitude').value = lat;
    document.getElementById('zoneLongitude').value = lon;
    document.getElementById('addZoneModal').style.display = 'flex';
    setTimeout(function() { initPickerMap(lat, lon); if (typeof lucide !== 'undefined') lucide.createIcons(); }, 150);
}

function initPickerMap(lat, lon) {
    if (pickerMap) { pickerMap.destroy(); pickerMap = null; }
    
    var center = (lat && lon) ? [lat, lon] : <?= defined('MAP_DEFAULT_CENTER') ? MAP_DEFAULT_CENTER : '[55.7558, 37.6173]' ?>;
    
    pickerMap = new ymaps.Map('zonePickerMap', {
        center: center,
        zoom: 14,
        controls: ['zoomControl']
    });
    
    if (lat && lon) {
        selectedMarker = new ymaps.Placemark([lat, lon], {}, { preset: 'islands#redDotIcon', draggable: true });
        selectedMarker.events.add('dragend', function() {
            var coords = selectedMarker.geometry.getCoordinates();
            document.getElementById('zoneLatitude').value = coords[0];
            document.getElementById('zoneLongitude').value = coords[1];
        });
        pickerMap.geoObjects.add(selectedMarker);
    }
    
    pickerMap.events.add('click', function(e) {
        var coords = e.get('coords');
        if (selectedMarker) pickerMap.geoObjects.remove(selectedMarker);
        selectedMarker = new ymaps.Placemark(coords, {}, { preset: 'islands#redDotIcon', draggable: true });
        selectedMarker.events.add('dragend', function() {
            var c = selectedMarker.geometry.getCoordinates();
            document.getElementById('zoneLatitude').value = c[0];
            document.getElementById('zoneLongitude').value = c[1];
        });
        pickerMap.geoObjects.add(selectedMarker);
        document.getElementById('zoneLatitude').value = coords[0];
        document.getElementById('zoneLongitude').value = coords[1];
    });
}

function saveZone(e) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData(form);
    
    if (!formData.get('latitude') || !formData.get('longitude')) {
        TesaTour.showToast('error', 'Выберите точку на карте');
        return;
    }
    
    var url = editingZoneId ? '/danger-zones/update/' + editingZoneId : '/danger-zones/create/<?= $group['id'] ?>';
    
    fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams(formData)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            TesaTour.showToast('success', editingZoneId ? 'Зона обновлена' : 'Зона добавлена');
            setTimeout(function() { location.reload(); }, 800);
        } else {
            TesaTour.showToast('error', data.message || 'Ошибка сохранения');
        }
    })
    .catch(function() { TesaTour.showToast('error', 'Ошибка сети'); });
}

function deleteZone(zoneId) {
    if (!confirm('Удалить опасную зону?')) return;
    fetch('/danger-zones/delete/' + zoneId, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { TesaTour.showToast('success', 'Удалено'); setTimeout(function() { location.reload(); }, 800); }
        else { TesaTour.showToast('error', data.message || 'Ошибка'); }
    })
    .catch(function() { TesaTour.showToast('error', 'Ошибка сети'); });
}
</script>
