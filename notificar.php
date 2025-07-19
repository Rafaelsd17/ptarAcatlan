
<?php
require 'vendor/autoload.php';
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

$subscriptionData = json_decode(file_get_contents("subs.json"), true);
$subscription = Subscription::create([
  'endpoint' => $subscriptionData['endpoint'],
  'keys' => [
    'p256dh' => $subscriptionData['keys']['p256dh'],
    'auth' => $subscriptionData['keys']['auth']
  ]
]);

$auth = [
  'VAPID' => [
    'subject' => 'mailto:admin@planta.com',
    'publicKey' => 'BPPIEQBVS67DFxmB85889GTN3au_1HEBeg6gNfMo_bU7vfvpgLO4ApVgP8lYs3AYECL05BbsRsKjeIy7p-oZsjc',
    'privateKey' => '-oeaN0gAo_Af8GcwY615eGsByGBaEHhqRIz7445FnIU',
  ]
];

$webPush = new WebPush($auth);
$webPush->queueNotification($subscription, json_encode([
  'title' => 'Mantenimiento Programado',
  'body' => 'Tienes un mantenimiento el día 10 de mayo.'
]));

foreach ($webPush->flush() as $report) {
  echo $report->isSuccess() ? 'Enviado' : 'Error';
}
?>
