
<?php
$data = json_decode(file_get_contents("php://input"), true);
file_put_contents("subs.json", json_encode($data));
?>
