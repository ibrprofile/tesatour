<?php $userModel = new User(); ?>

<!-- PWA Install Banner -->
<div id="pwaInstallBanner" class="card" style="display:none;margin-bottom:16px;background:linear-gradient(135deg,#007AFF,#0056b3);color:#fff;padding:14px 16px;">
    <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="download" style="width:20px;height:20px;color:#fff;"></i>
        </div>
        <div style="flex:1;">
            <div style="font-weight:600;font-size:0.875rem;">Установите приложение</div>
            <div style="font-size:0.75rem;opacity:0.85;">Быстрый доступ с экрана</div>
        </div>
        <button onclick="installPWA()" style="background:#fff;color:#007AFF;font-weight:600;font-size:0.8125rem;padding:7px 14px;border:none;border-radius:8px;cursor:pointer;">Установить</button>
    </div>
</div>

<!-- Welcome -->
<section class="section">
    <div style="margin-bottom:20px;">
        <h1 style="font-size:1.5rem;font-weight:700;">Привет, <?= e($currentUser['first_name']) ?>!</h1>
        <p class="text-sm text-secondary">
            <?= ($currentUser['account_type'] ?? 'amateur') === 'agency' ? 'Турагентство' : 'Любитель' ?>
            <?php if(($currentUser['account_type'] ?? 'amateur') === 'agency'): ?>
                <span style="display:inline-block;background:rgba(0,122,255,0.1);color:var(--color-primary);padding:2px 8px;border-radius:10px;font-size:0.6875rem;font-weight:600;margin-left:4px;">PRO</span>
            <?php endif; ?>
            &mdash; <?= $groupsCount ?> <?= pluralize($groupsCount, 'группа', 'группы', 'групп') ?>
        </p>
    </div>

    <!-- Quick Grid -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
        <a href="/groups" class="card" style="text-decoration:none;color:inherit;padding:14px;text-align:center;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(0,122,255,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
                <i data-lucide="users" style="color:#007AFF;width:20px;height:20px;"></i>
            </div>
            <div class="font-semibold" style="font-size:0.8125rem;">Группы</div>
        </a>
        <a href="/profile" class="card" style="text-decoration:none;color:inherit;padding:14px;text-align:center;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(52,199,89,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
                <i data-lucide="user" style="color:#34C759;width:20px;height:20px;"></i>
            </div>
            <div class="font-semibold" style="font-size:0.8125rem;">Профиль</div>
        </a>
        <a href="/settings" class="card" style="text-decoration:none;color:inherit;padding:14px;text-align:center;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(88,86,214,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
                <i data-lucide="settings" style="color:#5856D6;width:20px;height:20px;"></i>
            </div>
            <div class="font-semibold" style="font-size:0.8125rem;">Настройки</div>
        </a>
        <a href="/support" class="card" style="text-decoration:none;color:inherit;padding:14px;text-align:center;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,149,0,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
                <i data-lucide="headphones" style="color:#FF9500;width:20px;height:20px;"></i>
            </div>
            <div class="font-semibold" style="font-size:0.8125rem;">Поддержка</div>
        </a>
    </div>
</section>

<!-- Weather Widget -->
<section class="section">
    <h3 class="section-title">Погода</h3>
    <div id="weatherWidget" class="card" style="padding:16px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(0,122,255,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="cloud-sun" style="color:#007AFF;width:22px;height:22px;"></i>
            </div>
            <div style="flex:1;">
                <div class="font-semibold" id="weatherCity">Загрузка...</div>
                <div class="text-sm text-secondary" id="weatherDesc">Определение местоположения</div>
            </div>
            <div style="text-align:right;">
                <div class="font-bold" style="font-size:1.5rem;color:var(--color-primary);" id="weatherTemp">--</div>
                <div class="text-sm text-secondary" id="weatherExtra"></div>
            </div>
        </div>
        <div id="weatherForecast" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid var(--color-border);">
            <div style="display:flex;justify-content:space-between;gap:8px;overflow-x:auto;" id="forecastItems"></div>
        </div>
    </div>
</section>

<!-- Active SOS -->
<?php if (!empty($activeAlerts)): ?>
<section class="section">
    <h3 class="section-title" style="color:var(--color-danger);">Активные SOS</h3>
    <?php foreach ($activeAlerts as $alert): ?>
    <a href="/sos/<?= $alert['id'] ?>" class="card" style="display:block;text-decoration:none;color:inherit;padding:12px;margin-bottom:8px;border-left:4px solid var(--color-danger);">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,59,48,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="alert-triangle" style="color:var(--color-danger);width:18px;height:18px;"></i>
            </div>
            <div style="flex:1;">
                <div class="font-semibold text-sm"><?= e($alert['last_name'] . ' ' . $alert['first_name']) ?></div>
                <div class="text-secondary" style="font-size:0.75rem;"><?= e($alert['group_name'] ?? '') ?> &mdash; <?= timeAgo($alert['created_at']) ?></div>
            </div>
            <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);"></i>
        </div>
    </a>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- My Groups -->
<section class="section">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
        <h3 class="section-title" style="margin-bottom:0;">Мои группы</h3>
        <a href="/groups/create" class="btn btn-primary btn-sm"><i data-lucide="plus" style="width:14px;height:14px;"></i> Создать</a>
    </div>

    <?php if (empty($groups)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="users"></i></div>
        <h4 class="empty-state-title">Нет групп</h4>
        <p class="empty-state-text">Создайте группу или присоединитесь по приглашению</p>
        <a href="/groups/create" class="btn btn-primary"><i data-lucide="plus"></i> Создать группу</a>
    </div>
    <?php else: ?>
        <?php foreach ($groups as $group): ?>
        <a href="/groups/<?= $group['id'] ?>" class="card" style="display:block;text-decoration:none;color:inherit;padding:12px;margin-bottom:8px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:10px;background:<?= $group['status']==='active' ? 'rgba(52,199,89,0.1)' : 'rgba(142,142,147,0.1)' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="compass" style="color:<?= $group['status']==='active' ? '#34C759' : '#8E8E93' ?>;width:20px;height:20px;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="font-semibold text-sm" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($group['name']) ?></div>
                    <div class="text-secondary" style="font-size:0.75rem;">
                        <?= $group['members_count'] ?> участн. &mdash;
                        <span style="color:<?= $group['status']==='active' ? '#34C759' : '#8E8E93' ?>;"><?= $group['status']==='active' ? 'Активна' : 'Закрыта' ?></span>
                    </div>
                </div>
                <i data-lucide="chevron-right" style="width:16px;height:16px;color:var(--color-text-secondary);flex-shrink:0;"></i>
            </div>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<script>
var deferredPrompt;
window.addEventListener('beforeinstallprompt', function(e) {
    e.preventDefault();
    deferredPrompt = e;
    var banner = document.getElementById('pwaInstallBanner');
    if (banner) banner.style.display = 'block';
});
function installPWA() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function() {
            var banner = document.getElementById('pwaInstallBanner');
            if (banner) banner.style.display = 'none';
            deferredPrompt = null;
        });
    }
}

// Weather via Open-Meteo (free, no API key)
function loadWeather() {
    if (!navigator.geolocation) {
        document.getElementById('weatherCity').textContent = 'Геолокация недоступна';
        document.getElementById('weatherDesc').textContent = '';
        return;
    }
    navigator.geolocation.getCurrentPosition(function(pos) {
        var lat = pos.coords.latitude.toFixed(4);
        var lon = pos.coords.longitude.toFixed(4);
        
        // Current weather
        fetch('https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lon + '&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m&hourly=temperature_2m,weather_code&forecast_days=1&timezone=auto')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.current) {
                var temp = Math.round(data.current.temperature_2m);
                var code = data.current.weather_code;
                var wind = data.current.wind_speed_10m;
                var humidity = data.current.relative_humidity_2m;
                
                document.getElementById('weatherTemp').textContent = (temp > 0 ? '+' : '') + temp + '\u00B0';
                document.getElementById('weatherDesc').textContent = weatherCodeToText(code);
                document.getElementById('weatherExtra').textContent = '\uD83D\uDCA8 ' + wind + ' м/с  \uD83D\uDCA7 ' + humidity + '%';
                
                // City name via reverse geocoding (nominatim)
                fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lon + '&format=json&accept-language=ru')
                .then(function(r) { return r.json(); })
                .then(function(geo) {
                    var city = geo.address.city || geo.address.town || geo.address.village || geo.address.state || 'Ваше место';
                    document.getElementById('weatherCity').textContent = city;
                })
                .catch(function() {
                    document.getElementById('weatherCity').textContent = 'Текущее место';
                });
                
                // Hourly forecast
                if (data.hourly) {
                    var forecastEl = document.getElementById('forecastItems');
                    var now = new Date();
                    var currentHour = now.getHours();
                    var html = '';
                    for (var i = currentHour + 1; i < Math.min(currentHour + 7, data.hourly.time.length); i++) {
                        var h = new Date(data.hourly.time[i]);
                        var t = Math.round(data.hourly.temperature_2m[i]);
                        html += '<div style="text-align:center;min-width:50px;">';
                        html += '<div class="text-sm text-secondary">' + h.getHours() + ':00</div>';
                        html += '<div style="font-size:1.25rem;margin:4px 0;">' + weatherCodeToIcon(data.hourly.weather_code[i]) + '</div>';
                        html += '<div class="font-semibold text-sm">' + (t > 0 ? '+' : '') + t + '\u00B0</div>';
                        html += '</div>';
                    }
                    forecastEl.innerHTML = html;
                    document.getElementById('weatherForecast').style.display = 'block';
                }
            }
        })
        .catch(function() {
            document.getElementById('weatherCity').textContent = 'Не удалось загрузить';
            document.getElementById('weatherDesc').textContent = '';
        });
    }, function() {
        document.getElementById('weatherCity').textContent = 'Нет доступа к геолокации';
        document.getElementById('weatherDesc').textContent = 'Разрешите доступ в настройках';
    }, { timeout: 10000 });
}

function weatherCodeToText(code) {
    var map = {
        0: 'Ясно', 1: 'Преимущественно ясно', 2: 'Переменная облачность', 3: 'Пасмурно',
        45: 'Туман', 48: 'Изморозь', 51: 'Морось', 53: 'Морось', 55: 'Сильная морось',
        61: 'Небольшой дождь', 63: 'Дождь', 65: 'Сильный дождь',
        71: 'Небольшой снег', 73: 'Снег', 75: 'Сильный снег', 77: 'Снежные зерна',
        80: 'Ливень', 81: 'Сильный ливень', 82: 'Очень сильный ливень',
        85: 'Снегопад', 86: 'Сильный снегопад', 95: 'Гроза', 96: 'Гроза с градом', 99: 'Сильная гроза с градом'
    };
    return map[code] || 'Облачно';
}

function weatherCodeToIcon(code) {
    if (code === 0 || code === 1) return '\u2600\uFE0F';
    if (code === 2) return '\u26C5';
    if (code === 3) return '\u2601\uFE0F';
    if (code >= 45 && code <= 48) return '\uD83C\uDF2B\uFE0F';
    if (code >= 51 && code <= 55) return '\uD83C\uDF26\uFE0F';
    if (code >= 61 && code <= 65) return '\uD83C\uDF27\uFE0F';
    if (code >= 71 && code <= 77) return '\uD83C\uDF28\uFE0F';
    if (code >= 80 && code <= 82) return '\uD83C\uDF27\uFE0F';
    if (code >= 85 && code <= 86) return '\u2744\uFE0F';
    if (code >= 95) return '\u26C8\uFE0F';
    return '\u2601\uFE0F';
}

loadWeather();
</script>
