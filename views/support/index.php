<div class="chat-container" style="height:calc(100vh - var(--header-height, 56px));display:flex;flex-direction:column;">
    <div class="chat-header" style="padding:var(--spacing-md);background:var(--color-surface);border-bottom:1px solid var(--color-border);display:flex;align-items:center;gap:var(--spacing-md);">
        <a href="/dashboard" style="color:var(--color-primary);display:flex;align-items:center;">
            <i data-lucide="arrow-left" style="width:20px;height:20px;"></i>
        </a>
        <div style="width:40px;height:40px;border-radius:50%;background:rgba(0,122,255,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="headphones" style="color:var(--color-primary);width:20px;height:20px;"></i>
        </div>
        <div style="flex:1;">
            <div class="font-semibold">Техническая поддержка</div>
            <div class="text-sm text-secondary">TESA Tour</div>
        </div>
    </div>

    <div id="messagesContainer" style="flex:1;overflow-y:auto;padding:var(--spacing-md);display:flex;flex-direction:column;gap:var(--spacing-sm);background:var(--color-bg);">
        <?php if (empty($messages)): ?>
        <div style="text-align:center;padding:var(--spacing-xl) var(--spacing-md);">
            <div style="width:64px;height:64px;border-radius:50%;background:rgba(0,122,255,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto var(--spacing-md);">
                <i data-lucide="message-circle" style="color:var(--color-primary);width:28px;height:28px;"></i>
            </div>
            <div class="font-semibold" style="margin-bottom:4px;">Чат с поддержкой</div>
            <div class="text-sm text-secondary">Напишите нам, и мы ответим в ближайшее время</div>
        </div>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
            <div class="chat-message <?= $msg['is_from_admin'] ? '' : 'chat-message-own' ?>" style="display:flex;gap:var(--spacing-sm);max-width:85%;<?= $msg['is_from_admin'] ? '' : 'margin-left:auto;flex-direction:row-reverse;' ?>">
                <div style="flex-shrink:0;">
                    <?php if ($msg['is_from_admin']): ?>
                    <div class="avatar" style="background:rgba(0,122,255,0.1);color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-weight:600;width:32px;height:32px;border-radius:50%;">
                        <i data-lucide="headphones" style="width:16px;height:16px;"></i>
                    </div>
                    <?php else: ?>
                    <div class="avatar" style="background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;width:32px;height:32px;border-radius:50%;font-size:0.75rem;">
                        <?= strtoupper(mb_substr($currentUser['first_name'] ?? '', 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="background:<?= $msg['is_from_admin'] ? 'var(--color-surface)' : 'var(--color-primary)' ?>;color:<?= $msg['is_from_admin'] ? 'var(--color-text)' : '#fff' ?>;border-radius:var(--radius-lg);padding:var(--spacing-sm) var(--spacing-md);box-shadow:var(--shadow-sm);">
                    <?php if ($msg['is_from_admin']): ?>
                    <div style="font-size:0.75rem;font-weight:600;color:var(--color-primary);margin-bottom:2px;">Поддержка</div>
                    <?php endif; ?>
                    <div style="font-size:0.9375rem;line-height:1.5;"><?= nl2br(e($msg['message_text'])) ?></div>
                    <div style="font-size:0.6875rem;color:<?= $msg['is_from_admin'] ? 'var(--color-text-secondary)' : 'rgba(255,255,255,0.7)' ?>;margin-top:4px;"><?= date('d.m H:i', strtotime($msg['created_at'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div style="padding:var(--spacing-md);background:var(--color-surface);border-top:1px solid var(--color-border);">
        <form id="supportForm" onsubmit="sendSupportMessage(event)" style="display:flex;gap:var(--spacing-sm);align-items:flex-end;">
            <textarea id="supportInput" name="message_text" placeholder="Написать сообщение..." rows="1" style="flex:1;padding:10px 16px;border:1px solid var(--color-border);border-radius:var(--radius-full);font-size:1rem;font-family:inherit;outline:none;background:var(--color-surface-secondary);resize:none;min-height:40px;max-height:120px;" onfocus="this.style.borderColor='var(--color-primary)';this.style.background='var(--color-surface)'" onblur="this.style.borderColor='var(--color-border)';this.style.background='var(--color-surface-secondary)'"></textarea>
            <button type="submit" class="btn btn-primary" style="padding:10px 16px;flex-shrink:0;border-radius:var(--radius-full);">
                <i data-lucide="send" style="width:18px;height:18px;"></i>
            </button>
        </form>
    </div>
</div>

<script>
var supportInput = document.getElementById('supportInput');
supportInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});
supportInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('supportForm').dispatchEvent(new Event('submit'));
    }
});

function sendSupportMessage(e) {
    e.preventDefault();
    var input = document.getElementById('supportInput');
    var text = input.value.trim();
    if (!text) return;
    
    fetch('/support/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ message_text: text })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            input.value = '';
            input.style.height = 'auto';
            location.reload();
        } else {
            TesaTour.showToast('error', data.message || 'Ошибка отправки');
        }
    })
    .catch(function() {
        TesaTour.showToast('error', 'Ошибка сети');
    });
}

var mc = document.getElementById('messagesContainer');
if (mc) mc.scrollTop = mc.scrollHeight;
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
