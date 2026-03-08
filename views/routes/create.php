<section class="section">
    <a href="/groups/<?= $group['id'] ?>/routes" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        Назад к маршрутам
    </a>
    
    <h1 class="text-2xl font-bold mb-lg">Новый маршрут</h1>
    
    <div class="card">
        <form method="POST" action="/groups/<?= $group['id'] ?>/routes/create" data-ajax>
            <div class="form-group">
                <label class="form-label" for="title">Название маршрута *</label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-input" 
                    placeholder="Например: Маршрут на гору Эльбрус"
                    required
                    minlength="3"
                    maxlength="100"
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="description">Описание</label>
                <textarea 
                    id="description" 
                    name="description" 
                    class="form-input" 
                    placeholder="Опишите маршрут, особенности, рекомендации..."
                    rows="4"
                    maxlength="500"
                ></textarea>
                <p class="form-hint">Максимум 500 символов</p>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                <i data-lucide="plus"></i>
                Создать маршрут
            </button>
        </form>
    </div>
    
    <div class="card mt-lg" style="background: rgba(0, 122, 255, 0.1); border: 1px dashed var(--color-primary);">
        <div class="flex items-start gap-md">
            <i class="lucide-info text-primary" style="font-size: 1.25rem; flex-shrink: 0;"></i>
            <div>
                <div class="font-semibold mb-xs">Как добавить точки?</div>
                <p class="text-sm text-secondary">
                    После создания маршрута вы сможете добавить точки на карте. 
                    Участники группы увидят ваш маршрут и смогут ориентироваться по нему во время похода.
                </p>
            </div>
        </div>
    </div>
</section>
