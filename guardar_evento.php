
<?php
$data = json_decode(file_get_contents("php://input"), true);
if (isset($data['title'], $data['start'])) {
  // Aquí iría la lógica real para guardar en base de datos.
  echo json_encode(['status' => 'ok']);
} else {
  echo json_encode(['status' => 'error']);
}
?>
