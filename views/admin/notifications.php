<div class="container-sm mt-lg">
    <div class="flex-between mb-lg">
        <h1 class="text-xl font-bold">Отправка уведомлений</h1>
        <a href="/admin" class="btn btn-secondary">
            <i data-lucide="arrow-left"></i>
            Назад
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Новое уведомление</h3>
        </div>
        <div class="card-body">
            <form id="notificationForm" onsubmit="sendNotification(event)">
                <div class="form-group">
                    <label class="form-label">Заголовок</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Сообщение</label>
                    <textarea name="message" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Кому отправить</label>
                    <select name="target_type" class="form-control" onchange="toggleTargetId(this.value)">
                        <option value="all">Всем пользователям</option>
                        <option value="user">Конкретному пользователю</option>
                        <option value="group">Участникам группы</option>
                    </select>
                </div>

                <div class="form-group" id="targetIdGroup" style="display: none;">
                    <label class="form-label">ID цели</label>
                    <input type="number" name="target_id" class="form-control" placeholder="Введите ID пользователя или группы">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i data-lucide="send"></i>
                    Отправить уведомление
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleTargetId(targetType) {
    const targetIdGroup = document.getElementById('targetIdGroup');
    if (targetType === 'all') {
        targetIdGroup.style.display = 'none';
        targetIdGroup.querySelector('input').required = false;
    } else {
        targetIdGroup.style.display = 'block';
        targetIdGroup.querySelector('input').required = true;
    }
}

async function sendNotification(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    if (!confirm('Вы уверены, что хотите отправить это уведомление?')) {
        return;
    }
    
    try {
        const response = await fetch('/admin/send-notification', {
            method: 'POST',
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Уведомление отправлено', 'success');
            form.reset();
            toggleTargetId('all');
        } else {
            showToast(data.message || 'Ошибка отправки', 'error');
        }
    } catch (error) {
        showToast('Ошибка выполнения запроса', 'error');
    }
}
</script>
