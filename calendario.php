
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Calendario de Mantenimientos</title>
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
</head>
<body>
  <h2>Calendario de Mantenimientos</h2>
  <div id='calendar'></div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var calendarEl = document.getElementById('calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        dateClick: function (info) {
          const title = prompt("¿Título del mantenimiento?");
          if (title) {
            fetch('guardar_evento.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({ title: title, start: info.dateStr })
            }).then(res => res.json()).then(data => {
              if (data.status === 'ok') {
                calendar.addEvent({ title: title, start: info.dateStr });

                if (Notification.permission === "granted") {
                  new Notification("Mantenimiento Agregado", {
                    body: title + " el " + info.dateStr,
                    icon: 'https://cdn-icons-png.flaticon.com/512/190/190411.png'
                  });
                }

                fetch('notificar.php'); // Enviar notificación push
              }
            });
          }
        }
      });
      calendar.render();
    });

    // Registro del service worker y suscripción
    const publicVapidKey = 'BPPIEQBVS67DFxmB85889GTN3au_1HEBeg6gNfMo_bU7vfvpgLO4ApVgP8lYs3AYECL05BbsRsKjeIy7p-oZsjc';
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', async () => {
        const register = await navigator.serviceWorker.register('service-worker.js');
        const subscription = await register.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(publicVapidKey)
        });

        await fetch('subscribe.php', {
          method: 'POST',
          body: JSON.stringify(subscription),
          headers: { 'Content-Type': 'application/json' }
        });
      });
    }

    function urlBase64ToUint8Array(base64String) {
      const padding = '='.repeat((4 - base64String.length % 4) % 4);
      const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
      const rawData = atob(base64);
      return new Uint8Array([...rawData].map(char => char.charCodeAt(0)));
    }
  </script>
</body>
</html>
