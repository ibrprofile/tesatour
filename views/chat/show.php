<div class="chat-container">
    <div class="chat-header">
        <a href="/groups/<?= $group['id'] ?>" style="color:var(--color-primary);display:flex;align-items:center;flex-shrink:0;">
            <i data-lucide="arrow-left" style="width:20px;height:20px;"></i>
        </a>
        <div style="flex:1;min-width:0;">
            <div class="font-semibold" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Чат: <?= e($group['name']) ?></div>
            <div class="text-sm text-secondary"><?= count($messages) ?> сообщений</div>
        </div>
    </div>

    <div class="chat-messages" id="messagesContainer">
        <?php if (empty($messages)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i data-lucide="message-square"></i></div>
            <h3 class="empty-state-title">Нет сообщений</h3>
            <p class="empty-state-text">Станьте первым, кто напишет в этом чате</p>
        </div>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
            <div class="chat-message <?= $message['user_id'] == $userId ? 'chat-message-own' : '' ?> <?= $message['is_pinned'] ? 'chat-message-pinned' : '' ?>" id="message-<?= $message['id'] ?>">
                <div class="chat-message-avatar">
                    <?php if ($message['avatar']): ?>
                    <img src="<?= e($message['avatar']) ?>" alt="" class="avatar" style="width:32px;height:32px;">
                    <?php else: ?>
                    <div class="avatar" style="width:32px;height:32px;background:var(--color-primary);color:#fff;font-weight:600;font-size:0.75rem;">
                        <?= strtoupper(mb_substr($message['first_name'], 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="chat-message-content">
                    <div class="chat-message-header">
                        <span class="chat-message-author">
                            <?= e($message['first_name']) ?> <?= e($message['last_name']) ?>
                            <?php if ($message['user_role'] === 'owner'): ?>
                            <span class="badge role-owner" style="font-size:0.625rem;padding:1px 6px;">Владелец</span>
                            <?php elseif ($message['user_role'] === 'admin'): ?>
                            <span class="badge role-admin" style="font-size:0.625rem;padding:1px 6px;">Админ</span>
                            <?php endif; ?>
                        </span>
                        <span class="chat-message-time"><?= date('H:i', strtotime($message['created_at'])) ?></span>
                    </div>
                    
                    <?php if ($message['is_pinned']): ?>
                    <div class="message-pinned-badge"><i data-lucide="pin" style="width:10px;height:10px;"></i> Закреплено</div>
                    <?php endif; ?>
                    
                    <?php if ($message['reply_to_message_id']): ?>
                    <div class="chat-message-reply">
                        <div class="reply-line"></div>
                        <div>
                            <div class="reply-author"><?= e($message['reply_to_first_name'] ?? '') ?> <?= e($message['reply_to_last_name'] ?? '') ?></div>
                            <div class="reply-text"><?= e(mb_substr($message['reply_to_text'] ?? '', 0, 50)) ?><?= mb_strlen($message['reply_to_text'] ?? '') > 50 ? '...' : '' ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="chat-message-text">
                        <?= nl2br(e($message['message_text'])) ?>
                        <?php if ($message['is_edited']): ?>
                        <span class="message-edited">(изменено)</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="chat-message-footer">
                        <div class="chat-message-reactions">
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
                        
                        <div class="chat-message-actions">
                            <button class="btn-icon-sm" onclick="replyToMessage(<?= $message['id'] ?>, '<?= e($message['first_name']) ?> <?= e($message['last_name']) ?>')">
                                <i data-lucide="corner-up-left"></i>
                            </button>
                            <?php if ($message['user_id'] == $userId): ?>
                            <button class="btn-icon-sm" onclick="editMessage(<?= $message['id'] ?>)">
                                <i data-lucide="edit-2"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (in_array($userRole, ['owner', 'admin'])): ?>
                            <button class="btn-icon-sm" onclick="togglePin(<?= $message['id'] ?>, <?= $message['is_pinned'] ? 0 : 1 ?>)">
                                <i data-lucide="pin"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($message['user_id'] == $userId || in_array($userRole, ['owner', 'admin'])): ?>
                            <button class="btn-icon-sm" onclick="deleteMessage(<?= $message['id'] ?>)">
                                <i data-lucide="trash-2"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="chat-input">
        <div id="replyBanner" class="reply-banner" style="display:none;">
            <div class="reply-banner-content">
                <i data-lucide="corner-up-left" style="width:14px;height:14px;color:var(--color-primary);"></i>
                <div class="reply-banner-author"></div>
            </div>
            <button class="btn-icon-sm" onclick="cancelReply()"><i data-lucide="x"></i></button>
        </div>
        
        <form id="messageForm" onsubmit="sendMessage(event)">
            <input type="hidden" id="replyToId" name="reply_to_message_id">
            <div class="input-wrapper" style="display:flex;gap:var(--spacing-sm);align-items:flex-end;">
                <textarea id="messageInput" name="message_text" placeholder="Написать сообщение..." rows="1"></textarea>
                <button type="submit" class="btn btn-primary" style="padding:10px 16px;flex-shrink:0;border-radius:var(--radius-full);">
                    <i data-lucide="send" style="width:18px;height:18px;"></i>
                </button>
            </div>
        </form>
    </div>
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

<script>
var currentMessageId = null;
var replyingToId = null;
var messageInput = document.getElementById('messageInput');

messageInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

messageInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('messageForm').dispatchEvent(new Event('submit'));
    }
});

function sendMessage(e) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData(form);
    var messageText = formData.get('message_text').trim();
    if (!messageText) return;
    
    fetch('/groups/<?= $group['id'] ?>/chat/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams(formData).toString()
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            form.reset();
            messageInput.style.height = 'auto';
            cancelReply();
            location.reload();
        } else {
            TesaTour.showToast('error', data.message || 'Ошибка отправки');
        }
    })
    .catch(function() { TesaTour.showToast('error', 'Ошибка сети'); });
}

function replyToMessage(messageId, authorName) {
    replyingToId = messageId;
    document.getElementById('replyToId').value = messageId;
    document.getElementById('replyBanner').style.display = 'flex';
    document.querySelector('.reply-banner-author').textContent = authorName;
    messageInput.focus();
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function cancelReply() {
    replyingToId = null;
    document.getElementById('replyToId').value = '';
    document.getElementById('replyBanner').style.display = 'none';
}

function showReactionPicker(messageId) {
    currentMessageId = messageId;
    var picker = document.getElementById('reactionPicker');
    picker.style.display = 'block';
    var msgEl = document.getElementById('message-' + messageId);
    if (msgEl) {
        var rect = msgEl.getBoundingClientRect();
        picker.style.top = Math.max(10, rect.top - picker.offsetHeight - 10) + 'px';
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
    fetch('/chat/messages/' + messageId + '/react', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'reaction=' + encodeURIComponent(reaction)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { if (data.success) location.reload(); })
    .catch(function() { TesaTour.showToast('error', 'Ошибка'); });
}

function togglePin(messageId, pin) {
    fetch('/chat/messages/' + messageId + '/pin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'pin=' + pin
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            TesaTour.showToast('success', pin ? 'Закреплено' : 'Откреплено');
            setTimeout(function() { location.reload(); }, 800);
        }
    })
    .catch(function() { TesaTour.showToast('error', 'Ошибка'); });
}

function editMessage(messageId) {
    var msgEl = document.getElementById('message-' + messageId);
    var textEl = msgEl.querySelector('.chat-message-text');
    var text = textEl.textContent.trim().replace('(изменено)', '').trim();
    var newText = prompt('Редактировать сообщение:', text);
    if (!newText || newText === text) return;
    
    fetch('/chat/messages/' + messageId + '/update', {
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
    fetch('/chat/messages/' + messageId + '/delete', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { TesaTour.showToast('success', 'Удалено'); setTimeout(function() { location.reload(); }, 800); }
    })
    .catch(function() { TesaTour.showToast('error', 'Ошибка'); });
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
