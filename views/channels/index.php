<section class="section">
    <a href="/groups/<?= $group['id'] ?>" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i> Назад
    </a>
    
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--spacing-md);">
        <h1 class="text-xl font-bold">Каналы</h1>
        <?php if (in_array($userRole, ['owner', 'admin'])): ?>
        <button class="btn btn-primary btn-sm" onclick="showCreateChannelModal()">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Создать
        </button>
        <?php endif; ?>
    </div>

    <?php if (empty($channels)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="radio"></i></div>
        <h3 class="empty-state-title">Нет каналов</h3>
        <p class="empty-state-text">В этой группе пока нет каналов</p>
        <?php if (in_array($userRole, ['owner', 'admin'])): ?>
        <button class="btn btn-primary" onclick="showCreateChannelModal()">Создать первый канал</button>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:var(--spacing-sm);">
        <?php foreach ($channels as $channel): ?>
        <a href="/channels/<?= $channel['id'] ?>" class="list-item">
            <div class="list-item-icon" style="background:#5856D6;">
                <i data-lucide="radio"></i>
            </div>
            <div class="list-item-content">
                <div class="list-item-title"><?= e($channel['name']) ?></div>
                <div class="list-item-subtitle">
                    <?php if ($channel['description']): ?>
                        <?= e(mb_substr($channel['description'], 0, 60)) ?><?= mb_strlen($channel['description']) > 60 ? '...' : '' ?>
                    <?php else: ?>
                        <?= e($channel['first_name']) ?> <?= e($channel['last_name']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <i data-lucide="chevron-right" class="list-item-arrow"></i>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- Create Channel Modal -->
<div id="createChannelModal" class="modal">
    <div class="modal-backdrop" onclick="hideCreateChannelModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Создать канал</h3>
            <button class="modal-close" onclick="hideCreateChannelModal()"><i data-lucide="x"></i></button>
        </div>
        <form id="createChannelForm" onsubmit="createChannel(event)">
            <div class="form-group">
                <label class="form-label">Название канала</label>
                <input type="text" name="name" class="form-input" placeholder="Объявления" required>
            </div>
            <div class="form-group">
                <label class="form-label">Описание</label>
                <textarea name="description" class="form-input" rows="3" placeholder="Описание канала..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Создать</button>
        </form>
    </div>
</div>

<script>
function showCreateChannelModal() {
    document.getElementById('createChannelModal').style.display = 'flex';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
function hideCreateChannelModal() {
    document.getElementById('createChannelModal').style.display = 'none';
    document.getElementById('createChannelForm').reset();
}

function createChannel(e) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData(form);
    
    fetch('/groups/<?= $group['id'] ?>/channels/create', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams(formData)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            TesaTour.showToast('success', 'Канал создан');
            setTimeout(function() { location.reload(); }, 800);
        } else {
            TesaTour.showToast('error', data.message || 'Ошибка создания канала');
        }
    })
    .catch(function() { TesaTour.showToast('error', 'Ошибка сети'); });
}
</script>
