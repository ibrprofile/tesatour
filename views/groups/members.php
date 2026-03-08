<?php 
$groupModel = new Group();
$userModel = new User();
?>

<section class="section">
    <a href="/groups/<?= $group['id'] ?>" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        Назад к группе
    </a>
    
    <h1 class="text-2xl font-bold mb-lg">Участники группы</h1>
    
    <!-- Карта локаций (если группа активна) -->
    <?php if ($group['status'] === 'active'): ?>
    <div class="card mb-lg">
        <h3 class="card-title mb-md">Локации участников</h3>
        <div class="map-container" id="membersMap"></div>
    </div>
    <?php endif; ?>
    
    <!-- Список участников -->
    <h3 class="section-title"><?= count($members) ?> <?= pluralize(count($members), 'участник', 'участника', 'участников') ?></h3>
    
    <div class="flex flex-col gap-sm">
        <?php foreach ($members as $member): ?>
        <div class="card">
            <div class="flex items-center gap-md">
                <div class="avatar avatar-lg">
                    <?php if ($member['avatar']): ?>
                        <img src="<?= e(uploads($member['avatar'])) ?>" alt="Аватар">
                    <?php else: ?>
                        <i data-lucide="user"></i>
                    <?php endif; ?>
                </div>
                
                <div class="flex-1">
                    <div class="font-semibold">
                        <?= e($member['last_name'] . ' ' . $member['first_name']) ?>
                        <?php if ($member['id'] == $currentUser['id']): ?>
                            <span class="text-secondary text-sm">(вы)</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-sm mt-xs">
                        <span class="badge role-<?= $member['role'] ?>"><?= $groupModel->getRoleName($member['role']) ?></span>
                        <?php if ($member['last_location_update']): ?>
                            <span class="text-xs text-secondary">
                                <i data-lucide="map-pin" style="font-size: 12px;"></i>
                                <?= timeAgo($member['last_location_update']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Действия -->
                <?php if ($member['id'] != $currentUser['id']): ?>
                <div class="flex gap-xs">
                    <!-- Просмотр локации -->
                    <a href="/groups/<?= $group['id'] ?>/members/<?= $member['id'] ?>/location" 
                       class="btn btn-icon btn-sm btn-ghost" 
                       title="Посмотреть локацию">
                        <i data-lucide="map-pin"></i>
                    </a>
                    
                    <?php if ($canManage && $member['role'] === ROLE_MEMBER): ?>
                    <!-- Исключить с выбором -->
                    <button type="button" 
                            class="btn btn-icon btn-sm btn-ghost text-danger" 
                            title="Исключить"
                            onclick="openKickModal(<?= $member['id'] ?>, '<?= e($member['first_name'] . ' ' . $member['last_name']) ?>')">
                        <i data-lucide="user-x"></i>
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($canChangeRoles && $member['role'] !== ROLE_OWNER): ?>
                    <!-- Изменение роли -->
                    <button type="button" 
                            class="btn btn-icon btn-sm btn-ghost" 
                            title="Изменить роль"
                            onclick="openRoleModal(<?= $member['id'] ?>, '<?= e($member['first_name']) ?>', '<?= $member['role'] ?>')">
                        <i data-lucide="shield"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Модальное окно исключения -->
<div id="kickModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeKickModal()"></div>
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeKickModal()">
            <i data-lucide="x"></i>
        </button>
        <div class="modal-icon" style="background: rgba(255, 59, 48, 0.15);">
            <i data-lucide="user-x" style="color: var(--color-danger);"></i>
        </div>
        <h3>Исключить участника</h3>
        <p id="kickModalText">Выберите действие</p>
        
        <form id="kickForm" method="POST" class="mt-lg" data-ajax data-reload>
            <div class="form-group">
                <label class="form-label">Причина (необязательно)</label>
                <textarea 
                    name="reason" 
                    class="form-input" 
                    rows="3"
                    placeholder="Укажите причину исключения..."
                ></textarea>
            </div>
            
            <div class="form-group">
                <label class="toggle-label" style="margin: 0;">
                    <div class="toggle-info">
                        <div class="toggle-title">Добавить в черный список</div>
                        <div class="toggle-description">
                            Пользователь не сможет вступить в группу повторно
                        </div>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" name="add_to_blacklist" value="1">
                        <span class="toggle-slider"></span>
                    </label>
                </label>
            </div>
            
            <div class="modal-actions mt-lg">
                <button type="button" class="btn btn-secondary" onclick="closeKickModal()">Отмена</button>
                <button type="submit" class="btn btn-danger">Исключить</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно изменения роли -->
<div id="roleModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeRoleModal()"></div>
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeRoleModal()">
            <i data-lucide="x"></i>
        </button>
        <h3>Изменить роль</h3>
        <p id="roleModalText">Выберите роль для участника</p>
        
        <form id="roleForm" method="POST" class="mt-lg" data-ajax data-reload>
            <div class="flex flex-col gap-sm">
                <label class="list-item" style="cursor: pointer;">
                    <input type="radio" name="role" value="admin" style="display: none;">
                    <div class="list-item-icon" style="background: var(--color-primary);">
                        <i data-lucide="shield"></i>
                    </div>
                    <div class="list-item-content">
                        <div class="list-item-title">Администратор</div>
                        <div class="list-item-subtitle">Может управлять участниками</div>
                    </div>
                </label>
                
                <label class="list-item" style="cursor: pointer;">
                    <input type="radio" name="role" value="member" style="display: none;">
                    <div class="list-item-icon" style="background: var(--color-text-secondary);">
                        <i data-lucide="user"></i>
                    </div>
                    <div class="list-item-content">
                        <div class="list-item-title">Участник</div>
                        <div class="list-item-subtitle">Базовые права</div>
                    </div>
                </label>
            </div>
            
            <div class="modal-actions mt-lg">
                <button type="button" class="btn btn-secondary" onclick="closeRoleModal()">Отмена</button>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
// Инициализация карты
<?php if ($group['status'] === 'active'): ?>
document.addEventListener('DOMContentLoaded', function() {
    TesaTour.initMap('membersMap').then(function(map) {
        // Загружаем локации участников
        TesaTour.fetchJSON('/api/groups/<?= $group['id'] ?>/members/locations').then(function(response) {
            if (response.success && response.data.locations) {
                response.data.locations.forEach(function(loc) {
                    if (loc.last_latitude && loc.last_longitude) {
                        TesaTour.addPlacemark(
                            [parseFloat(loc.last_latitude), parseFloat(loc.last_longitude)],
                            {
                                content: '<strong>' + loc.first_name + ' ' + loc.last_name + '</strong><br>Обновлено: ' + new Date(loc.last_location_update).toLocaleString('ru'),
                                hint: loc.first_name + ' ' + loc.last_name
                            }
                        );
                    }
                });
                TesaTour.fitMapToPlacemarks();
            }
        });
    });
});
<?php endif; ?>

// Модальное окно исключения
function openKickModal(memberId, name) {
    document.getElementById('kickModalText').textContent = 'Вы уверены, что хотите исключить ' + name + '?';
    document.getElementById('kickForm').action = '/groups/<?= $group['id'] ?>/members/' + memberId + '/kick';
    document.getElementById('kickModal').style.display = 'flex';
}

function closeKickModal() {
    document.getElementById('kickModal').style.display = 'none';
    document.getElementById('kickForm').reset();
}

// Модальное окно роли
let currentMemberId = null;

function openRoleModal(memberId, name, currentRole) {
    currentMemberId = memberId;
    document.getElementById('roleModalText').textContent = 'Выберите роль для ' + name;
    document.getElementById('roleForm').action = '/groups/<?= $group['id'] ?>/members/' + memberId + '/role';
    
    // Отмечаем текущую роль
    document.querySelectorAll('#roleForm input[name="role"]').forEach(function(input) {
        input.checked = input.value === currentRole;
        input.closest('.list-item').style.background = input.value === currentRole ? 'var(--color-surface-secondary)' : '';
    });
    
    document.getElementById('roleModal').style.display = 'flex';
}

function closeRoleModal() {
    document.getElementById('roleModal').style.display = 'none';
}

// Подсветка выбранной роли
document.querySelectorAll('#roleForm input[name="role"]').forEach(function(input) {
    input.addEventListener('change', function() {
        document.querySelectorAll('#roleForm .list-item').forEach(function(item) {
            item.style.background = '';
        });
        this.closest('.list-item').style.background = 'var(--color-surface-secondary)';
    });
});
</script>
