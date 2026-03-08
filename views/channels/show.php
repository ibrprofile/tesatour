<?php
function formatFileSizeHelper($bytes) {
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' МБ';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' КБ';
    return $bytes . ' Б';
}
?>
<div class="channel-container">
    <div class="channel-header" style="display:flex;align-items:center;gap:var(--spacing-md);">
        <a href="/groups/<?= $channel['group_id'] ?>/channels" style="color:var(--color-primary);display:flex;align-items:center;flex-shrink:0;">
            <i data-lucide="arrow-left" style="width:20px;height:20px;"></i>
        </a>
        <div style="flex:1;min-width:0;">
            <div class="font-semibold" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($channel['name']) ?></div>
            <?php if ($channel['description']): ?>
            <div class="text-sm text-secondary"><?= e($channel['description']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="channel-messages" id="messagesContainer">
        <?php if (empty($messages)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i data-lucide="message-circle"></i></div>
            <h3 class="empty-state-title">Нет сообщений</h3>
            <p class="empty-state-text">В этом канале пока нет сообщений</p>
        </div>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
            <div class="message <?= $message['is_pinned'] ? 'message-pinned' : '' ?>" id="message-<?= $message['id'] ?>">
                <div class="message-avatar" style="flex-shrink:0;">
                    <?php if ($message['avatar']): ?>
                    <img src="<?= e($message['avatar']) ?>" alt="" class="avatar">
                    <?php else: ?>
                    <div class="avatar" style="background:var(--color-primary);color:#fff;font-weight:600;">
                        <?= strtoupper(mb_substr($message['first_name'], 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="message-content">
                    <div class="message-header">
                        <span class="message-author">
                            <?= e($message['first_name']) ?> <?= e($message['last_name']) ?>
                            <?php if ($message['user_role'] === 'owner'): ?>
                            <span class="badge role-owner" style="font-size:0.625rem;padding:1px 6px;">Владелец</span>
                            <?php elseif ($message['user_role'] === 'admin'): ?>
                            <span class="badge role-admin" style="font-size:0.625rem;padding:1px 6px;">Админ</span>
                            <?php endif; ?>
                        </span>
                        <span class="message-time"><?= date('d.m.Y H:i', strtotime($message['created_at'])) ?></span>
                        
                        <?php if (in_array($userRole, ['owner', 'admin']) || $message['user_id'] == $userId): ?>
                        <div class="message-actions">
                            <?php if (in_array($userRole, ['owner', 'admin'])): ?>
                            <button class="btn-icon-sm" onclick="togglePin(<?= $message['id'] ?>, <?= $message['is_pinned'] ? 0 : 1 ?>)" title="<?= $message['is_pinned'] ? 'Открепить' : 'Закрепить' ?>">
                                <i data-lucide="pin"></i>
                            </button>
                            <?php endif; ?>
                            <button class="btn-icon-sm" onclick="editMessage(<?= $message['id'] ?>)" title="Редактировать">
                                <i data-lucide="edit-2"></i>
                            </button>
                            <button class="btn-icon-sm" onclick="deleteMessage(<?= $message['id'] ?>)" title="Удалить">
                                <i data-lucide="trash-2"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($message['is_pinned']): ?>
                    <div class="message-pinned-badge"><i data-lucide="pin" style="width:10px;height:10px;"></i> Закреплено</div>
                    <?php endif; ?>
                    
                    <div class="message-text"><?= nl2br(e($message['message_text'])) ?>
                        <?php if ($message['is_edited']): ?><span class="message-edited">(изменено)</span><?php endif; ?>
                    </div>
                    
                    <?php if (!empty($message['files'])): ?>
                    <div class="message-files">
                        <?php foreach ($message['files'] as $file): ?>
                            <?php if (strpos($file['file_type'], 'image') !== false): ?>
                            <div class="file-preview" onclick="openImageFullscreen('<?= e($file['file_path']) ?>')">
                                <img src="<?= e($file['file_path']) ?>" alt="<?= e($file['file_name']) ?>">
                            </div>
                            <?php elseif (strpos($file['file_type'], 'video') !== false): ?>
                            <div class="file-preview">
                                <video controls><source src="<?= e($file['file_path']) ?>" type="<?= e($file['file_type']) ?>"></video>
                            </div>
                            <?php else: ?>
                            <a href="<?= e($file['file_path']) ?>" class="file-download" download>
                                <i data-lucide="file" style="width:16px;height:16px;"></i>
                                <?= e($file['file_name']) ?>
                                <span class="text-sm text-secondary">(<?= formatFileSizeHelper($file['file_size']) ?>)</span>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="message-reactions">
                        <?php if (!empty($message['reactions'])): ?>
                            <?php foreach ($message['reactions'] as $reaction): ?>
                            <button class="reaction-btn" onclick="toggleReaction(<?= $message['id'] ?>, '<?= $reaction['reaction'] ?>')" title="<?= e($reaction['users']) ?>">
                                <?= $reaction['reaction'] ?> <?= $reaction['count'] ?>
                            </button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <button class="btn-icon-sm" onclick="showReactionPicker(<?= $message['id'] ?>)">
                            <i data-lucide="smile"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (in_array($userRole, ['owner', 'admin'])): ?>
    <div class="channel-input">
        <form id="messageForm" onsubmit="sendMessage(event)" enctype="multipart/form-data">
            <div style="display:flex;gap:var(--spacing-sm);align-items:flex-end;">
                <textarea id="messageInput" name="message_text" placeholder="Написать сообщение..." rows="1"></textarea>
                <label style="display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:var(--color-surface-secondary);cursor:pointer;flex-shrink:0;">
                    <i data-lucide="paperclip" style="width:18px;height:18px;color:var(--color-text-secondary);"></i>
                    <input type="file" id="fileInput" name="files[]" multiple accept="image/*,video/*,application/pdf" style="display:none;">
                </label>
                <button type="submit" class="btn btn-primary" style="padding:10px 16px;flex-shrink:0;border-radius:var(--radius-full);">
                    <i data-lucide="send" style="width:18px;height:18px;"></i>
                </button>
            </div>
            <div id="filePreview" class="file-preview-list"></div>
        </form>
    </div>
    <?php endif; ?>
</div>

<div id="reactionPicker" class="reaction-picker" style="display:none;">
    <div class="reaction-picker-content">
        <?php
        $reactions = ['👍', '👎', '💩', '🤡', '💀', '😈', '😍', '😇', '😂', '🥶', '😳', '😭', '🥳', '🤯', '🤬', '🫣', '❤️'];
        foreach ($reactions as $reaction):
        ?>
        <button class="reaction-item" onclick="selectReaction('<?= $reaction ?>')"><?= $reaction ?></button>
        <?php endforeach; ?>
    </div>
</div>

<div id="imageFullscreen" class="image-fullscreen" style="display:none;" onclick="closeImageFullscreen()">
    <img src="" alt="">
</div>

<script>
var currentMessageId = null;
var messageInput = document.getElementById('messageInput');

if (messageInput) {
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
}

function sendMessage(e) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData(form);
    
    fetch('/channels/post/<?= $channel['id'] ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            form.reset();
            document.getElementById('filePreview').innerHTML = '';
            location.reload();
        } else {
            TesaTour.showToast('error', data.message || 'Ошибка отправки');
        }
    })
    .catch(function() { TesaTour.showToast('error', 'Ошибка сети'); });
}

function showReactionPicker(messageId) {
    currentMessageId = messageId;
    var picker = document.getElementById('reactionPicker');
    picker.style.display = 'block';
    var msgEl = document.getElementById('message-' + messageId);
    if (msgEl) {
        var rect = msgEl.getBoundingClientRect();
        picker.style.top = Math.max(10, rect.bottom + 5) + 'px';
        picker.style.left = Math.min(window.innerWidth - 220, rect.left) + 'px';
    }
}

function selectReaction(reaction) {
    if (!currentMessageId) return;
    toggleReaction(currentMessageId, reaction);
    document.getElementById('reactionPicker').style.display = 'none';
    currentMessageId = null;
}

function toggleReaction(messageId, reaction) {
    fetch('/channels/reaction/' + messageId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'reaction=' + encodeURIComponent(reaction)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { if (data.success) location.reload(); })
    .catch(function() { TesaTour.showToast('error', 'Ошибка'); });
}

function togglePin(messageId, pin) {
    fetch('/channels/pin/' + messageId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'pin=' + pin
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { TesaTour.showToast('success', pin ? 'Закреплено' : 'Откреплено'); setTimeout(function() { location.reload(); }, 800); }
    })
    .catch(function() { TesaTour.showToast('error', 'Ошибка'); });
}

function editMessage(messageId) {
    var msgEl = document.getElementById('message-' + messageId);
    var text = msgEl.querySelector('.message-text').textContent.trim().replace('(изменено)', '').trim();
    var newText = prompt('Редактировать сообщение:', text);
    if (!newText || newText === text) return;
    
    fetch('/channels/edit/' + messageId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'message_text=' + encodeURIComponent(newText)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { TesaTour.showToast('success', 'Изменено'); setTimeout(function() { location.reload(); }, 800); }
    })
    .catch(function() { TesaTour.showToast('error', 'Ошибка'); });
}

function deleteMessage(messageId) {
    if (!confirm('Удалить сообщение?')) return;
    fetch('/channels/delete/' + messageId, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { TesaTour.showToast('success', 'Удалено'); setTimeout(function() { location.reload(); }, 800); }
    })
    .catch(function() { TesaTour.showToast('error', 'Ошибка'); });
}

function openImageFullscreen(src) {
    var fs = document.getElementById('imageFullscreen');
    fs.querySelector('img').src = src;
    fs.style.display = 'flex';
}

function closeImageFullscreen() {
    document.getElementById('imageFullscreen').style.display = 'none';
}

var fileInputEl = document.getElementById('fileInput');
if (fileInputEl) {
    fileInputEl.addEventListener('change', function(e) {
        var preview = document.getElementById('filePreview');
        preview.innerHTML = '';
        for (var i = 0; i < e.target.files.length; i++) {
            var div = document.createElement('div');
            div.className = 'badge';
            div.textContent = e.target.files[i].name;
            preview.appendChild(div);
        }
    });
}

document.addEventListener('click', function(e) {
    var picker = document.getElementById('reactionPicker');
    if (picker && !picker.contains(e.target) && !e.target.closest('.btn-icon-sm')) {
        picker.style.display = 'none';
    }
});

var mc = document.getElementById('messagesContainer');
if (mc) mc.scrollTop = mc.scrollHeight;
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
