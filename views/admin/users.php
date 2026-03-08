<div class="container mt-lg">
    <div class="flex-between mb-lg">
        <h1 class="text-xl font-bold">Управление пользователями</h1>
        <a href="/admin" class="btn btn-secondary">
            <i data-lucide="arrow-left"></i>
            Назад
        </a>
    </div>

    <div class="card mb">
        <div class="card-body">
            <form method="GET" action="/admin/users" class="flex gap">
                <input type="text" name="search" class="form-control" placeholder="Поиск по email, имени, фамилии..." value="<?= e($search) ?>">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="search"></i>
                    Найти
                </button>
                <?php if ($search): ?>
                <a href="/admin/users" class="btn btn-secondary">Сбросить</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Всего пользователей: <?= e($total) ?></h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пользователь</th>
                            <th>Email</th>
                            <th>Статус</th>
                            <th>Групп</th>
                            <th>Регистрация</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= e($user['id']) ?></td>
                            <td>
                                <div class="flex gap-sm">
                                    <?php if ($user['avatar']): ?>
                                    <img src="<?= e($user['avatar']) ?>" alt="Avatar" class="avatar-sm">
                                    <?php else: ?>
                                    <div class="avatar-sm" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                        <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-semibold"><?= e($user['first_name']) ?> <?= e($user['last_name']) ?></div>
                                        <?php if ($user['telegram_username']): ?>
                                        <div class="text-sm text-muted">@<?= e($user['telegram_username']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($user['email']) ?></td>
                            <td>
                                <?php if ($user['is_admin']): ?>
                                <span class="badge badge-danger">Админ</span>
                                <?php endif; ?>
                                <?php if ($user['user_status'] === 'agency'): ?>
                                <span class="badge badge-primary">Турагенство</span>
                                <?php else: ?>
                                <span class="badge badge-secondary">Любитель</span>
                                <?php endif; ?>
                                <?php if ($user['subscription_active']): ?>
                                <span class="badge badge-success">Подписка</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-secondary"><?= e($user['groups_owned']) ?> владеет</span>
                                <span class="badge badge-secondary"><?= e($user['groups_joined']) ?> участвует</span>
                            </td>
                            <td class="text-sm text-muted"><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="flex gap-sm">
                                    <?php if (!$user['is_admin']): ?>
                                    <button class="btn btn-sm btn-primary" onclick="makeAdmin(<?= $user['id'] ?>)">
                                        Сделать админом
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" onclick="removeAdmin(<?= $user['id'] ?>)">
                                        Снять админа
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?= $user['id'] ?>)">
                                        Удалить
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="card-footer">
            <div class="flex-between">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn btn-secondary">
                    <i data-lucide="chevron-left"></i>
                    Предыдущая
                </a>
                <?php else: ?>
                <span></span>
                <?php endif; ?>
                
                <span class="text-muted">Страница <?= $page ?> из <?= $totalPages ?></span>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn btn-secondary">
                    Следующая
                    <i data-lucide="chevron-right"></i>
                </a>
                <?php else: ?>
                <span></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--bg-tertiary);
}

.table th {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 14px;
    text-transform: uppercase;
}

.table tbody tr:hover {
    background: var(--bg-secondary);
}
</style>

<script>
async function makeAdmin(userId) {
    if (!confirm('Вы уверены, что хотите назначить этого пользователя администратором?')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/make-admin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `user_id=${userId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Пользователь назначен администратором', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Ошибка', 'error');
        }
    } catch (error) {
        showToast('Ошибка выполнения запроса', 'error');
    }
}

async function removeAdmin(userId) {
    if (!confirm('Вы уверены, что хотите снять права администратора?')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/remove-admin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `user_id=${userId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Права администратора сняты', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Ошибка', 'error');
        }
    } catch (error) {
        showToast('Ошибка выполнения запроса', 'error');
    }
}

async function deleteUser(userId) {
    if (!confirm('Вы уверены, что хотите удалить этого пользователя? Это действие необратимо!')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/delete-user', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `user_id=${userId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Пользователь удален', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Ошибка', 'error');
        }
    } catch (error) {
        showToast('Ошибка выполнения запроса', 'error');
    }
}
</script>
