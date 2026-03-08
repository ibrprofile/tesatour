<div class="container mt-lg">
    <div class="flex-between mb-lg">
        <h1 class="text-xl font-bold">Управление группами</h1>
        <a href="/admin" class="btn btn-secondary">
            <i data-lucide="arrow-left"></i>
            Назад
        </a>
    </div>

    <div class="card mb">
        <div class="card-body">
            <form method="GET" action="/admin/groups" class="flex gap">
                <input type="text" name="search" class="form-control" placeholder="Поиск по названию группы..." value="<?= e($search) ?>">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="search"></i>
                    Найти
                </button>
                <?php if ($search): ?>
                <a href="/admin/groups" class="btn btn-secondary">Сбросить</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Всего групп: <?= e($total) ?></h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Владелец</th>
                            <th>Участников</th>
                            <th>Статус</th>
                            <th>Создана</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $group): ?>
                        <tr>
                            <td><?= e($group['id']) ?></td>
                            <td>
                                <div class="font-semibold"><?= e($group['name']) ?></div>
                                <?php if ($group['description']): ?>
                                <div class="text-sm text-muted"><?= e(mb_substr($group['description'], 0, 50)) ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= e($group['first_name']) ?> <?= e($group['last_name']) ?></div>
                                <div class="text-sm text-muted"><?= e($group['email']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-primary"><?= e($group['members_count']) ?> чел.</span>
                            </td>
                            <td>
                                <span class="badge badge-<?= $group['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= $group['status'] === 'active' ? 'Активна' : 'Закрыта' ?>
                                </span>
                            </td>
                            <td class="text-sm text-muted"><?= date('d.m.Y H:i', strtotime($group['created_at'])) ?></td>
                            <td>
                                <div class="flex gap-sm">
                                    <a href="/groups/<?= $group['id'] ?>" class="btn btn-sm btn-primary">
                                        Открыть
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="deleteGroup(<?= $group['id'] ?>)">
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

<script>
async function deleteGroup(groupId) {
    if (!confirm('Вы уверены, что хотите удалить эту группу? Это действие необратимо!')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/delete-group', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `group_id=${groupId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Группа удалена', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Ошибка', 'error');
        }
    } catch (error) {
        showToast('Ошибка выполнения запроса', 'error');
    }
}
</script>
