<div class="container mt-lg">
    <div class="flex-between mb-lg">
        <h1 class="text-xl font-bold">Панель администратора</h1>
        <button class="btn btn-primary" onclick="toggleAdminMode()">
            <i data-lucide="shield"></i>
            <span id="adminModeText">Режим администратора</span>
        </button>
    </div>

    <div class="grid grid-4 mb-lg">
        <div class="card">
            <div class="card-body">
                <div class="flex-between mb">
                    <span class="text-muted">Пользователей</span>
                    <i data-lucide="users" style="color: var(--primary)"></i>
                </div>
                <div class="text-xl font-bold"><?= e($usersCount) ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex-between mb">
                    <span class="text-muted">Активных групп</span>
                    <i data-lucide="users-2" style="color: var(--success)"></i>
                </div>
                <div class="text-xl font-bold"><?= e($groupsCount) ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex-between mb">
                    <span class="text-muted">Активных SOS</span>
                    <i data-lucide="alert-triangle" style="color: var(--danger)"></i>
                </div>
                <div class="text-xl font-bold"><?= e($sosCount) ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <a href="/admin/notifications" class="btn btn-primary btn-block">
                    <i data-lucide="bell"></i>
                    Отправить уведомление
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-2 gap-lg">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Последние пользователи</h3>
                <a href="/admin/users" class="btn btn-sm btn-secondary">Все пользователи</a>
            </div>
            <div class="card-body">
                <div class="list">
                    <?php foreach ($recentUsers as $user): ?>
                    <div class="list-item">
                        <?php if ($user['avatar']): ?>
                        <img src="<?= e($user['avatar']) ?>" alt="Avatar" class="avatar">
                        <?php else: ?>
                        <div class="avatar" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                            <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="list-item-content">
                            <div class="list-item-title">
                                <?= e($user['first_name']) ?> <?= e($user['last_name']) ?>
                                <?php if ($user['is_admin']): ?>
                                <span class="badge badge-danger">Админ</span>
                                <?php endif; ?>
                            </div>
                            <div class="list-item-subtitle"><?= e($user['email']) ?></div>
                        </div>
                        
                        <span class="badge badge-secondary">
                            <?= e($user['groups_owned']) ?> групп
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Последние группы</h3>
                <a href="/admin/groups" class="btn btn-sm btn-secondary">Все группы</a>
            </div>
            <div class="card-body">
                <div class="list">
                    <?php foreach ($recentGroups as $group): ?>
                    <div class="list-item">
                        <div class="list-item-content">
                            <div class="list-item-title"><?= e($group['name']) ?></div>
                            <div class="list-item-subtitle">
                                Владелец: <?= e($group['first_name']) ?> <?= e($group['last_name']) ?>
                            </div>
                        </div>
                        
                        <div class="flex gap-sm">
                            <span class="badge badge-primary">
                                <?= e($group['members_count']) ?> чел.
                            </span>
                            <span class="badge badge-<?= $group['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= $group['status'] === 'active' ? 'Активна' : 'Закрыта' ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-lg">
        <div class="card-header">
            <h3 class="card-title">Быстрые действия</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-3">
                <a href="/admin/users" class="btn btn-outline">
                    <i data-lucide="users"></i>
                    Управление пользователями
                </a>
                <a href="/admin/groups" class="btn btn-outline">
                    <i data-lucide="users-2"></i>
                    Управление группами
                </a>
                <a href="/admin/logs" class="btn btn-outline">
                    <i data-lucide="file-text"></i>
                    Логи действий
                </a>
            </div>
        </div>
    </div>
</div>

<script>
let adminMode = <?= Session::get('admin_mode', 0) ?>;

async function toggleAdminMode() {
    try {
        const response = await fetch('/admin/toggle-mode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `mode=${adminMode}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            adminMode = data.admin_mode;
            document.getElementById('adminModeText').textContent = 
                adminMode ? 'Выключить режим админа' : 'Включить режим админа';
            showToast(
                adminMode ? 'Режим администратора включен' : 'Режим администратора выключен',
                'success'
            );
        }
    } catch (error) {
        showToast('Ошибка переключения режима', 'error');
    }
}
</script>
