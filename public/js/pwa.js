let deferredPrompt = null;
let swRegistration = null;

if ('serviceWorker' in navigator) {
  window.addEventListener('load', async () => {
    try {
      swRegistration = await navigator.serviceWorker.register('/public/sw.js', {
        scope: '/'
      });
      
      swRegistration.addEventListener('updatefound', () => {
        const newWorker = swRegistration.installing;
        newWorker.addEventListener('statechange', () => {
          if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
            showUpdateNotification();
          }
        });
      });
      
      await requestNotificationPermission();
    } catch (error) {
      console.error('Service Worker registration failed:', error);
    }
  });
}

window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  showInstallPrompt();
});

function showInstallPrompt() {
  const installBanner = document.createElement('div');
  installBanner.className = 'pwa-install-banner';
  installBanner.innerHTML = `
    <div class="pwa-install-content">
      <div class="pwa-install-icon">
        <img src="/public/icons/icon-96x96.png" alt="TESA Tour">
      </div>
      <div class="pwa-install-text">
        <strong>Установить TESA Tour</strong>
        <p>Установите приложение для быстрого доступа</p>
      </div>
      <button class="btn-install" onclick="installPWA()">Установить</button>
      <button class="btn-close" onclick="closeInstallBanner()">&times;</button>
    </div>
  `;
  document.body.appendChild(installBanner);
}

async function installPWA() {
  if (!deferredPrompt) return;
  
  deferredPrompt.prompt();
  const { outcome } = await deferredPrompt.userChoice;
  
  if (outcome === 'accepted') {
    console.log('PWA installed successfully');
  }
  
  deferredPrompt = null;
  closeInstallBanner();
}

function closeInstallBanner() {
  const banner = document.querySelector('.pwa-install-banner');
  if (banner) {
    banner.remove();
  }
}

async function requestNotificationPermission() {
  if (!('Notification' in window)) {
    console.log('This browser does not support notifications');
    return false;
  }
  
  if (Notification.permission === 'granted') {
    await subscribeToPushNotifications();
    return true;
  }
  
  if (Notification.permission !== 'denied') {
    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
      await subscribeToPushNotifications();
      return true;
    }
  }
  
  return false;
}

async function subscribeToPushNotifications() {
  if (!swRegistration) return;
  
  try {
    const subscription = await swRegistration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(getVapidPublicKey())
    });
    
    await fetch('/api/notifications/subscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ subscription })
    });
    
    startNotificationPolling();
  } catch (error) {
    console.error('Failed to subscribe to push notifications:', error);
  }
}

async function checkForNotifications() {
  try {
    const response = await fetch('/api/notifications/unread');
    const data = await response.json();
    
    if (data.success && data.notifications && data.notifications.length > 0) {
      for (const notification of data.notifications) {
        displayNotification(notification);
      }
    }
  } catch (error) {
    console.error('Failed to check notifications:', error);
  }
}

function displayNotification(notification) {
  if (Notification.permission === 'granted' && swRegistration) {
    const notificationData = JSON.parse(notification.data || '{}');
    
    swRegistration.showNotification(notification.title, {
      body: notification.body,
      icon: '/public/icons/icon-192x192.png',
      badge: '/public/icons/icon-72x72.png',
      vibrate: [200, 100, 200],
      data: {
        ...notificationData,
        notificationId: notification.id
      },
      tag: 'tesa-' + notification.id,
      requireInteraction: notificationData.type === 'sos'
    });
    
    markNotificationAsRead(notification.id);
  }
}

async function markNotificationAsRead(notificationId) {
  try {
    await fetch('/api/notifications/mark-read', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ notification_id: notificationId })
    });
  } catch (error) {
    console.error('Failed to mark notification as read:', error);
  }
}

function startNotificationPolling() {
  checkForNotifications();
  setInterval(checkForNotifications, 30000);
}

function getVapidPublicKey() {
  return 'BEl62iUYgUivxIkv69yViEuiBIa-Ib37J8xYjEB7Y-v8Qa9tNlP-HlGPxmYvNT9Kq7Kzt8Jd6K8FLcP5tF5TmVE';
}

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');
  
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

function showUpdateNotification() {
  const updateBanner = document.createElement('div');
  updateBanner.className = 'pwa-update-banner';
  updateBanner.innerHTML = `
    <div class="pwa-update-content">
      <p>Доступно обновление приложения</p>
      <button class="btn-update" onclick="updatePWA()">Обновить</button>
    </div>
  `;
  document.body.appendChild(updateBanner);
}

function updatePWA() {
  if (swRegistration && swRegistration.waiting) {
    swRegistration.waiting.postMessage({ type: 'SKIP_WAITING' });
  }
  window.location.reload();
}

function showNotification(title, body, data = {}) {
  if (Notification.permission === 'granted' && swRegistration) {
    swRegistration.showNotification(title, {
      body: body,
      icon: '/public/icons/icon-192x192.png',
      badge: '/public/icons/icon-72x72.png',
      vibrate: [200, 100, 200],
      data: data,
      tag: 'tesa-notification'
    });
  }
}

async function syncData() {
  if ('serviceWorker' in navigator && 'sync' in swRegistration) {
    try {
      await swRegistration.sync.register('sync-data');
    } catch (error) {
      console.error('Background sync registration failed:', error);
    }
  }
}

window.addEventListener('online', () => {
  syncData();
  showToast('Соединение восстановлено', 'success');
});

window.addEventListener('offline', () => {
  showToast('Нет подключения к интернету', 'warning');
});
