
self.addEventListener('push', function(event) {
  const data = event.data.json();
  const title = data.title || 'Notificación';
  const options = {
    body: data.body,
    icon: 'https://cdn-icons-png.flaticon.com/512/190/190411.png'
  };
  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});
