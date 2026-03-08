/**
 * TESA Tour App
 */

var ymaps = window.ymaps;
var TesaTour = {
    config: {
        geoUpdateInterval: 30000,
        mapDefaultCenter: [55.7558, 37.6173],
        mapDefaultZoom: 12,
        toastDuration: 4000
    },

    map: null,
    placemarks: [],
    geoWatchId: null,
    lastPosition: null,
    toastContainer: null,

    init: function() {
        this.initGeolocation();
        this.initForms();
        this.initToasts();
        this.initAvatarPreview();

        if (this.isLoggedIn()) {
            this.startLocationTracking();
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    },

    isLoggedIn: function() {
        return !document.body.classList.contains('guest-body');
    },

    /* ---- Геолокация ---- */

    initGeolocation: function() {
        if (!navigator.geolocation) return;

        var self = this;
        if (navigator.permissions) {
            navigator.permissions.query({ name: 'geolocation' }).then(function(result) {
                if (result.state === 'granted') {
                    self.hideGeoModal();
                    self.getCurrentPosition();
                } else if (result.state === 'prompt' && self.isLoggedIn()) {
                    setTimeout(function() { self.showGeoModal(); }, 2000);
                } else if (result.state === 'denied') {
                    self.hideGeoModal();
                }
            });
        }
    },

    showGeoModal: function() {
        var modal = document.getElementById('geoModal');
        if (modal) modal.style.display = 'flex';
    },

    hideGeoModal: function() {
        var modal = document.getElementById('geoModal');
        if (modal) modal.style.display = 'none';
    },

    startLocationTracking: function() {
        if (!navigator.geolocation) return;
        var self = this;
        this.getCurrentPosition();
        setInterval(function() { self.getCurrentPosition(); }, this.config.geoUpdateInterval);
    },

    getCurrentPosition: function() {
        var self = this;
        navigator.geolocation.getCurrentPosition(
            function(position) {
                self.lastPosition = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };
                self.hideGeoModal();
                self.sendLocationToServer(self.lastPosition);
            },
            function() {},
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
        );
    },

    sendLocationToServer: function(position) {
        try {
            fetch('/api/location/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    latitude: position.latitude,
                    longitude: position.longitude
                })
            });
        } catch (e) {}
    },

    requestCurrentPosition: function() {
        return new Promise(function(resolve, reject) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    TesaTour.lastPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    resolve(TesaTour.lastPosition);
                },
                function(error) { reject(error); },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    },

    /* ---- Яндекс Карты ---- */

    initMap: function(containerId, center, zoom) {
        var self = this;
        return new Promise(function(resolve) {
            ymaps.ready(function() {
                var container = document.getElementById(containerId);
                if (!container) { resolve(null); return; }

                self.map = new ymaps.Map(containerId, {
                    center: center || self.config.mapDefaultCenter,
                    zoom: zoom || self.config.mapDefaultZoom,
                    controls: ['zoomControl', 'geolocationControl']
                });
                self.placemarks = [];
                resolve(self.map);
            });
        });
    },

    addPlacemark: function(coords, options) {
        if (!this.map) return null;
        options = options || {};
        var placemark = new ymaps.Placemark(coords, {
            balloonContent: options.content || '',
            hintContent: options.hint || ''
        }, {
            preset: options.preset || 'islands#blueCircleDotIcon'
        });
        this.map.geoObjects.add(placemark);
        this.placemarks.push(placemark);
        return placemark;
    },

    clearPlacemarks: function() {
        if (!this.map) return;
        var self = this;
        this.placemarks.forEach(function(pm) {
            self.map.geoObjects.remove(pm);
        });
        this.placemarks = [];
    },

    fitMapToPlacemarks: function() {
        if (!this.map || this.placemarks.length === 0) return;
        this.map.setBounds(this.map.geoObjects.getBounds(), { checkZoomRange: true, zoomMargin: 40 });
    },

    setMapCenter: function(coords, zoom) {
        if (!this.map) return;
        this.map.setCenter(coords, zoom || 15, { duration: 300 });
    },

    /* ---- SOS ---- */

    sendSOS: function(groupId, comment) {
        var self = this;
        comment = comment || '';

        var doSend = function() {
            fetch('/groups/' + groupId + '/sos/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    latitude: self.lastPosition.latitude,
                    longitude: self.lastPosition.longitude,
                    comment: comment
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(response) {
                if (response.success) {
                    self.showToast('success', response.message || 'SOS-сигнал отправлен!');
                    if (response.data && response.data.redirect) {
                        window.location.href = response.data.redirect;
                    }
                } else {
                    self.showToast('error', response.message || 'Ошибка отправки SOS');
                }
            })
            .catch(function() {
                self.showToast('error', 'Ошибка отправки SOS');
            });
        };

        if (!self.lastPosition) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    self.lastPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    doSend();
                },
                function() {
                    self.showToast('error', 'Не удалось определить местоположение');
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        } else {
            doSend();
        }
    },

    /* ---- Формы ---- */

    initForms: function() {
        var self = this;
        var forms = document.querySelectorAll('form[data-ajax]');
        for (var i = 0; i < forms.length; i++) {
            (function(form) {
                form.addEventListener('submit', function(e) { self.handleFormSubmit(e); });
            })(forms[i]);
        }
    },

    handleFormSubmit: function(e) {
        e.preventDefault();
        var self = this;
        var form = e.target;
        var submitBtn = form.querySelector('[type="submit"]');
        var originalText = submitBtn ? submitBtn.innerHTML : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner" style="width:20px;height:20px;"></div>';
        }

        var formData = new FormData(form);
        var data = {};
        formData.forEach(function(value, key) { data[key] = value; });

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(response) {
            if (response.success) {
                self.showToast('success', response.message || 'Успешно!');
                if (response.data && response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else if (form.dataset.reload) {
                    window.location.reload();
                }
            } else {
                self.showToast('error', response.message || 'Произошла ошибка');
            }
        })
        .catch(function() {
            self.showToast('error', 'Ошибка соединения');
        })
        .finally(function() {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    },

    /* ---- Toasts ---- */

    initToasts: function() {
        this.toastContainer = document.createElement('div');
        this.toastContainer.style.cssText = 'position:fixed;top:16px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:8px;max-width:400px;width:calc(100% - 32px);';
        document.body.appendChild(this.toastContainer);
    },

    showToast: function(type, message) {
        if (!this.toastContainer) this.initToasts();

        var icons = { success: 'check-circle', error: 'x-circle', warning: 'alert-triangle' };
        var titles = { success: 'Успешно', error: 'Ошибка', warning: 'Внимание' };

        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.innerHTML =
            '<div class="toast-icon"><i data-lucide="' + (icons[type] || 'info') + '"></i></div>' +
            '<div style="flex:1"><div style="font-weight:600;margin-bottom:2px;">' + (titles[type] || '') + '</div>' +
            '<div style="font-size:14px;">' + message + '</div></div>' +
            '<button type="button" style="background:none;border:none;cursor:pointer;padding:4px;" onclick="this.parentElement.remove()">' +
            '<i data-lucide="x" style="width:16px;height:16px;"></i></button>';

        this.toastContainer.appendChild(toast);

        if (typeof lucide !== 'undefined') {
            lucide.createIcons({ root: toast });
        }

        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(function() { toast.remove(); }, 300);
        }, this.config.toastDuration);
    },

    /* ---- Утилиты ---- */

    fetchJSON: function(url, options) {
        options = options || {};
        options.headers = Object.assign(
            { 'X-Requested-With': 'XMLHttpRequest' },
            options.headers || {}
        );
        return fetch(url, options).then(function(r) { return r.json(); });
    },
 
    copyToClipboard: function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                TesaTour.showToast('success', 'Скопировано в буфер обмена');
            });
        } else {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.cssText = 'position:fixed;opacity:0;';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            TesaTour.showToast('success', 'Скопировано в буфер обмена');
        }
    },

    initAvatarPreview: function() {
        var inputs = document.querySelectorAll('input[type="file"][data-avatar-preview]');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].addEventListener('change', function(e) {
                var file = e.target.files[0];
                var previewId = e.target.dataset.avatarPreview;
                var preview = document.getElementById(previewId);
                if (file && preview) {
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        preview.innerHTML = '<img src="' + event.target.result + '" alt="Предпросмотр" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }
};

/* ---- Глобальные функции ---- */

function requestGeolocation() {
    navigator.geolocation.getCurrentPosition(
        function(position) {
            TesaTour.lastPosition = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };
            TesaTour.hideGeoModal();
            TesaTour.sendLocationToServer(TesaTour.lastPosition);
            TesaTour.showToast('success', 'Геолокация включена');
        },
        function() {
            TesaTour.showToast('error', 'Не удалось получить доступ к геолокации');
        },
        { enableHighAccuracy: true }
    );
}

function openSosModal() {
    var modal = document.getElementById('sosModal');
    if (modal) modal.style.display = 'flex';
}

function closeSosModal() {
    var modal = document.getElementById('sosModal');
    if (modal) modal.style.display = 'none';
}

function submitSos(groupId) {
    var comment = document.getElementById('sosComment');
    TesaTour.sendSOS(groupId, comment ? comment.value : '');
    closeSosModal();
}

function copyInviteLink(text) {
    TesaTour.copyToClipboard(text);
}

function showToast(message, type) {
    TesaTour.showToast(type || 'info', message);
}

function copyToClipboard(text) {
    TesaTour.copyToClipboard(text);
}

function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function confirmAction(message, callback) {
    if (confirm(message)) callback();
}

function toggleMobileMenu() {
    var menu = document.getElementById('mobileMenu');
    if (menu) menu.classList.toggle('show');
}

/* ---- Инициализация ---- */

document.addEventListener('DOMContentLoaded', function() {
    TesaTour.init();
});
