<section class="section">
    <a href="/groups" class="btn btn-ghost mb-md">
        <i data-lucide="arrow-left"></i>
        Назад
    </a>
    
    <h1 class="text-2xl font-bold mb-lg">Новая группа</h1>
    
    <div class="card">
        <form method="POST" action="/groups/create" data-ajax>
            <div class="form-group">
                <label class="form-label" for="name">Название группы *</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-input" 
                    placeholder="Например: Поход на Эльбрус"
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
                    placeholder="Краткое описание похода..."
                    maxlength="500"
                    rows="4"
                ></textarea>
                <div class="form-hint">Не более 500 символов</div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block mt-lg">
                <i data-lucide="plus"></i>
                Создать группу
            </button>
        </form>
    </div>
</section>