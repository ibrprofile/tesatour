<div class="page-header">
    <a href="/groups/<?= $group['id'] ?>" class="btn-back">
        <i data-lucide="arrow-left"></i>
    </a>
    <h1 class="page-title">Настройки группы</h1>
</div>

<section class="section">
    <div class="card">
        <form method="POST" action="/groups/<?= $group['id'] ?>/settings/update" data-ajax data-reload>
            <div class="form-group">
                <label class="form-label">Название группы</label>
                <input type="text" name="name" class="form-input" value="<?= e($group['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Описание</label>
                <textarea name="description" class="form-input" rows="4"><?= e($group['description'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Сохранить</button>
        </form>
    </div>
</section>

<?php if ($group['status'] === 'active'): ?>
<section class="section">
    <h3 class="section-title text-danger">Опасная зона</h3>
    <div class="card">
        <p class="text-secondary mb-md">Закрытие группы необратимо. Все данные будут сохранены, но новые изменения будут невозможны.</p>
        <form method="POST" action="/groups/<?= $group['id'] ?>/close" data-ajax data-reload onsubmit="return confirm('Вы уверены? Это действие нельзя отменить!')">
            <button type="submit" class="btn btn-danger btn-block">Закрыть группу</button>
        </form>
    </div>
</section>
<?php endif; ?>
