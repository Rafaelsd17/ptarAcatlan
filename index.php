<?php
// index.php - Gestión unificada de Datos de Riego y DQO

// Mostrar errores para depuración (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a BD
include 'conn.php';

// Obtener pestaña activa
$tab = $_GET['tab'] ?? 'view';

// Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DATOS DE RIEGO
    if (isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {
        $volumen = is_numeric($_POST['volumen']) ? floatval($_POST['volumen']) : 0;
        $stmt = null;
        if ($_POST['action'] === 'add') {
            $stmt = $conn->prepare("
                INSERT INTO irrigation_records
                (fecha, hora_inicio, hora_termino, zona_regar,
                 bomba_A, bomba_B, bomba_R,
                 tac_inicio, tac_fin, volumen,
                 observaciones, responsable)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'ssssdddiddss',
                $_POST['fecha'],
                $_POST['hora_inicio'],
                $_POST['hora_termino'],
                $_POST['zona_regar'],
                $_POST['bomba_A'],
                $_POST['bomba_B'],
                $_POST['bomba_R'],
                $_POST['tac_inicio'],
                $_POST['tac_fin'],
                $volumen,
                $_POST['observaciones'],
                $_POST['responsable']
            );
        } else {
            // edit
            $stmt = $conn->prepare("
                UPDATE irrigation_records SET
                    fecha=?, hora_inicio=?, hora_termino=?, zona_regar=?,
                    bomba_A=?, bomba_B=?, bomba_R=?,
                    tac_inicio=?, tac_fin=?, volumen=?,
                    observaciones=?, responsable=?
                WHERE id=?
            ");
            $stmt->bind_param(
                'ssssdddiddssi',
                $_POST['fecha'],
                $_POST['hora_inicio'],
                $_POST['hora_termino'],
                $_POST['zona_regar'],
                $_POST['bomba_A'],
                $_POST['bomba_B'],
                $_POST['bomba_R'],
                $_POST['tac_inicio'],
                $_POST['tac_fin'],
                $volumen,
                $_POST['observaciones'],
                $_POST['responsable'],
                $_POST['id']
            );
        }
        $stmt->execute();
        header('Location: index.php?tab=view');
        exit;
    }

    // DQO
    if (isset($_POST['action']) && ($_POST['action'] === 'dqo_add' || $_POST['action'] === 'dqo_edit')) {
        $temperatura = is_numeric($_POST['temperatura']) ? floatval($_POST['temperatura']) : null;
        $ph = is_numeric($_POST['ph']) ? floatval($_POST['ph']) : null;
        $stmt = null;
        if ($_POST['action'] === 'dqo_add') {
            $stmt = $conn->prepare("
                INSERT INTO dqo
                (fecha_muestreo, ubicacion, dqo_mg_l, temperatura, ph, observaciones)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'ssddds',
                $_POST['fecha_muestreo'],
                $_POST['ubicacion'],
                $_POST['dqo_mg_l'],
                $temperatura,
                $ph,
                $_POST['observaciones']
            );
        } else {
            $stmt = $conn->prepare("
                UPDATE dqo SET
                    fecha_muestreo=?, ubicacion=?, dqo_mg_l=?, temperatura=?, ph=?, observaciones=?
                WHERE id=?
            ");
            $stmt->bind_param(
                'ssdddsi',
                $_POST['fecha_muestreo'],
                $_POST['ubicacion'],
                $_POST['dqo_mg_l'],
                $temperatura,
                $ph,
                $_POST['observaciones'],
                $_POST['id']
            );
        }
        $stmt->execute();
        header('Location: index.php?tab=dqo_view');
        exit;
    }

    // DBO
    if (isset($_POST['action']) && ($_POST['action'] === 'dbo_add' || $_POST['action'] === 'dbo_edit')) {
        $stmt = null;
        if ($_POST['action'] === 'dbo_add') {
            $stmt = $conn->prepare("INSERT INTO dbo (fecha_muestreo, ubicacion, dbo5_mg_l, dbo20_mg_l, temperatura, ph, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "ssdddds",
                $_POST['fecha_muestreo'],
                $_POST['ubicacion'],
                $_POST['dbo5_mg_l'],
                $_POST['dbo20_mg_l'],
                $_POST['temperatura'],
                $_POST['ph'],
                $_POST['observaciones']
            );
        } else {
            $stmt = $conn->prepare("UPDATE dbo SET fecha_muestreo=?, ubicacion=?, dbo5_mg_l=?, dbo20_mg_l=?, temperatura=?, ph=?, observaciones=? WHERE id=?");
            $stmt->bind_param(
                "ssddddsi",
                $_POST['fecha_muestreo'],
                $_POST['ubicacion'],
                $_POST['dbo5_mg_l'],
                $_POST['dbo20_mg_l'],
                $_POST['temperatura'],
                $_POST['ph'],
                $_POST['observaciones'],
                $_POST['id']
            );
        }
        $stmt->execute();
        header("Location: index.php?tab=dbo_view");
        exit;
    }

    // SST
    if (isset($_POST['action']) && ($_POST['action'] === 'sst_add' || $_POST['action'] === 'sst_edit')) {
        $stmt = null;
        if ($_POST['action'] === 'sst_add') {
            $stmt = $conn->prepare("INSERT INTO sst (fecha_muestreo, ubicacion, sst_mg_l, temperatura, ph, observaciones) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "ssdddds",
                $_POST['fecha_muestreo'],
                $_POST['ubicacion'],
                $_POST['sst_mg_l'],
                $_POST['temperatura'],
                $_POST['ph'],
                $_POST['observaciones']
            );
        } else {
            $stmt = $conn->prepare("UPDATE sst SET fecha_muestreo=?, ubicacion=?, sst_mg_l=?, temperatura=?, ph=?, observaciones=? WHERE id=?");
            $stmt->bind_param(
                "ssddddsi",
                $_POST['fecha_muestreo'],
                $_POST['ubicacion'],
                $_POST['sst_mg_l'],
                $_POST['temperatura'],
                $_POST['ph'],
                $_POST['observaciones'],
                $_POST['id']
            );
        }
        $stmt->execute();
        header("Location: index.php?tab=sst_view");
        exit;
    }
}

// Variables para editar registro Riego
$editRecord = null;
if (isset($_GET['tab'], $_GET['id']) && $_GET['tab'] === 'edit') {
    $id = (int) $_GET['id'];
    $rs = $conn->prepare("SELECT * FROM irrigation_records WHERE id = ?");
    $rs->bind_param('i', $id);
    $rs->execute();
    $result = $rs->get_result();
    $editRecord = $result->fetch_assoc();
}

// Variables para editar registro DQO
$editDqo = null;
if (isset($_GET['tab'], $_GET['id']) && $_GET['tab'] === 'dqo_edit') {
    $id = (int) $_GET['id'];
    $rs = $conn->prepare("SELECT * FROM dqo WHERE id = ?");
    $rs->bind_param('i', $id);
    $rs->execute();
    $result = $rs->get_result();
    $editDqo = $result->fetch_assoc();
}

// Variables para editar registro DBO
$editDbo = null;
if (isset($_GET['tab'], $_GET['id']) && $_GET['tab'] === 'dbo_edit') {
    $id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM dbo WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editDbo = $result->fetch_assoc();
}

// Variables para editar registro SST
$editSst = null;
if (isset($_GET['tab'], $_GET['id']) && $_GET['tab'] === 'sst_edit') {
    $id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM sst WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editSst = $result->fetch_assoc();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta charset="UTF-8">
    <title>Sistema de Gestión</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        nav { margin-bottom: 20px; }
        nav a { margin-right: 10px; text-decoration: none; padding: 6px 10px; border: 1px solid #368b9d; border-radius: 4px; color: #368b9d; }
        nav a.active, nav a:hover { background: #368b9d; color: #fff; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #368b9d; color: #fff; }
        input, textarea { width: 100%; padding: 6px; margin: 4px 0 12px; box-sizing: border-box; }
        input[type="submit"] { background: #368b9d; color: white; padding: 8px 14px; border: none; border-radius: 4px; cursor: pointer; }
        .form-container { max-width: 600px; }
        .edit-btn {
            background-color: #368b9d;
            color: white !important;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
        }
        .edit-btn:hover {
            background-color: #2b6b7c;
        }
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropbtn {
            background-color: #368b9d;
            color: white;
            padding: 6px 10px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            border-radius: 0;
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .dropdown:hover .dropbtn {
            background-color: #2980b9;
        }
        .logout-button {
            background-color: #368b9d;
            color: white;
            padding: 6px 10px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<nav>
 <div class="menu" id="menu">
    <div class="dropdown">
        <button class="dropbtn">Datos de Riego</button>
        <div class="dropdown-content">
            <a href="?tab=view" class="<?= $tab==='view' ? 'active' : '' ?>">Ver Datos</a>
            <a href="?tab=add" class="<?= $tab==='add' ? 'active' : '' ?>">Agregar Datos</a>
        </div>
     
    </div>

    <div class="dropdown">
        <button class="dropbtn">Demanda Química de Oxígeno</button>
        <div class="dropdown-content">
            <a href="?tab=dqo_view" class="<?= $tab==='dqo_view' ? 'active' : '' ?>">Ver datos</a>
            <a href="?tab=dqo_add" class="<?= $tab==='dqo_add' ? 'active' : '' ?>">Agregar dato</a>
        </div>
    </div>
    <div class="dropdown">
        <button class="dropbtn">Demanda Bioquímica de Oxígeno</button>
        <div class="dropdown-content">
            <a href="?tab=dbo_view" class="<?= $tab==='dbo_view' ? 'active' : '' ?>">Ver datos</a>
            <a href="?tab=dbo_add" class="<?= $tab==='dbo_add' ? 'active' : '' ?>">Agregar dato</a>
        </div>
    </div>
    <div class="dropdown">
        <button class="dropbtn">Sólidos Suspendidos Totales</button>
        <div class="dropdown-content">
            <a href="?tab=sst_view" class="<?= $tab==='sst_view' ? 'active' : '' ?>">Ver datos</a>
            <a href="?tab=sst_add" class="<?= $tab==='sst_add' ? 'active' : '' ?>">Agregar dato</a>
        </div>
    </div>
    <div class="dropdown">
    <button class="dropbtn">Calendario</button>
    <div class="dropdown-content">
        <a href="?tab=calendario" class="<?= $tab==='calendario' ? 'active' : '' ?>">Calendario</a>

    </div>
</div>

    <button class="logout-button" onclick="logout()">Log Out</button>
    </div>
    
</nav>
<script>
function logout() {
            window.location.href = 'principal.html'; 
        }
</script>

<?php if ($tab === 'view'): ?>
    <h2>Registros de Irrigación</h2>
    


    <?php
    $filter_fecha_desde = $_GET['filter_fecha_desde'] ?? '';
    $filter_fecha_hasta = $_GET['filter_fecha_hasta'] ?? '';
    $filter_zona = $_GET['filter_zona'] ?? '';

    $query = "SELECT * FROM irrigation_records WHERE 1=1";
    $types = '';
    $params = [];

    if ($filter_fecha_desde && $filter_fecha_hasta) {
        $query .= " AND fecha BETWEEN ? AND ?";
        $types .= 'ss';
        $params[] = $filter_fecha_desde;
        $params[] = $filter_fecha_hasta;
    } elseif ($filter_fecha_desde) {
        $query .= " AND fecha >= ?";
        $types .= 's';
        $params[] = $filter_fecha_desde;
    } elseif ($filter_fecha_hasta) {
        $query .= " AND fecha <= ?";
        $types .= 's';
        $params[] = $filter_fecha_hasta;
    }
    if ($filter_zona) {
        $query .= " AND zona_regar LIKE ?";
        $types .= 's';
        $params[] = "%$filter_zona%";
    }
    $query .= " ORDER BY fecha DESC, hora_inicio DESC";

    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $records = [];
    while ($row = $res->fetch_assoc()) {
        $records[] = $row;
    }


$fechas = array_column($records, 'fecha');
$volumenes = array_map(function($r) {
    return $r['volumen'] ?? 0;
}, $records);
?>

<canvas id="graficaRiego" height="100" style="margin: 30px 0;"></canvas>
<script>
const ctx = document.getElementById('graficaRiego').getContext('2d');
const etiquetas = <?= json_encode(array_reverse($fechas)) ?>;
const volumenes = <?= json_encode(array_reverse($volumenes)) ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: etiquetas,
        datasets: [{
            label: 'Volumen de Riego (L)',
            data: volumenes,
            borderColor: '#007BA3',
            backgroundColor: 'rgba(0, 123, 163, 0.2)',
            tension: 0.2
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Volumen (L)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Fecha'
                }
            }
        }
    }
});
</script>

    

    <form method="GET" action="">
        <input type="hidden" name="tab" value="view" />
        <label>Fecha desde: <input type="date" name="filter_fecha_desde" value="<?= htmlspecialchars($filter_fecha_desde) ?>"></label>
        <label>Fecha hasta: <input type="date" name="filter_fecha_hasta" value="<?= htmlspecialchars($filter_fecha_hasta) ?>"></label>
        <label>Zona: <input type="text" name="filter_zona" placeholder="Zona a regar" value="<?= htmlspecialchars($filter_zona) ?>"></label>
        <input type="submit" value="Filtrar">
    </form>

    <table>
        <thead>
            <tr>
                <th>Fecha</th><th>Hora Inicio</th><th>Hora Final</th><th>Zona</th>
                <th>BCM-03A</th><th>BCM-03B</th><th>BCM-03R</th>
                <th>TAC Inicio</th><th>TAC Final</th><th>Volumen</th>
                <th>Observaciones</th><th>Responsable</th><th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($records): ?>
                <?php foreach ($records as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['fecha']) ?></td>
                    <td><?= htmlspecialchars($r['hora_inicio']) ?></td>
                    <td><?= htmlspecialchars($r['hora_termino']) ?></td>
                    <td><?= htmlspecialchars($r['zona_regar']) ?></td>
                    <td><?= htmlspecialchars($r['bomba_A']) ?></td>
                    <td><?= htmlspecialchars($r['bomba_B']) ?></td>
                    <td><?= htmlspecialchars($r['bomba_R']) ?></td>
                    <td><?= htmlspecialchars($r['tac_inicio']) ?></td>
                    <td><?= htmlspecialchars($r['tac_fin']) ?></td>
                    <td><?= htmlspecialchars($r['volumen']) ?></td>
                    <td><?= htmlspecialchars($r['observaciones']) ?></td>
                    <td><?= htmlspecialchars($r['responsable']) ?></td>
                    <td><a href="?tab=edit&id=<?= $r['id'] ?>" class="edit-btn">Editar</a></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="13">No hay registros que coincidan con el filtro.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
      

<?php elseif ($tab === 'add'): ?>

    <h2>Agregar Nuevo Registro</h2>
    <div class="form-container">
        <form method="post">
            <input type="hidden" name="action" value="add">

            <label>Fecha</label>
            <input type="date" name="fecha" required>

            <label>Hora Inicio</label>
            <input type="time" name="hora_inicio" required>

            <label>Hora Final</label>
            <input type="time" name="hora_termino" required>

            <label>Zona a Regar</label>
            <input type="text" name="zona_regar" required>

            <label>BCM-03A</label>
            <input type="number" name="bomba_A" step="0.0001" required>

            <label>BCM-03B</label>
            <input type="number" name="bomba_B" step="0.0001" required>

            <label>BCM-03R</label>
            <input type="number" name="bomba_R" step="0.0001" required>

            <label>TAC Inicio</label>
            <input type="number" name="tac_inicio" step="0.0001" required>

            <label>TAC Final</label>
            <input type="number" name="tac_fin" step="0.0001" required>

            <label>Volumen</label>
            <input type="number" name="volumen" step="0.01" required>

            <label>Observaciones</label>
            <textarea name="observaciones"></textarea>

            <label>Responsable</label>
            <input type="text" name="responsable" required>

            <input type="submit" value="Guardar">
        </form>
    </div>

<?php elseif ($tab === 'edit' && $editRecord): ?>

    <h2>Editar Registro #<?= $editRecord['id'] ?></h2>
    <div class="form-container">
        <form method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id"     value="<?= $editRecord['id'] ?>">

            <label>Fecha</label>
            <input type="date" name="fecha" value="<?= $editRecord['fecha'] ?>" required>

            <label>Hora Inicio</label>
            <input type="time" name="hora_inicio" value="<?= $editRecord['hora_inicio'] ?>" required>

            <label>Hora Final</label>
            <input type="time" name="hora_termino" value="<?= $editRecord['hora_termino'] ?>" required>

            <label>Zona a Regar</label>
            <input type="text" name="zona_regar" value="<?= htmlspecialchars($editRecord['zona_regar']) ?>" required>

            <label>BCM-03A</label>
            <input type="number" name="bomba_A" step="0.0001" value="<?= $editRecord['bomba_A'] ?>" required>

            <label>BCM-03B</label>
            <input type="number" name="bomba_B" step="0.0001" value="<?= $editRecord['bomba_B'] ?>" required>

            <label>BCM-03R</label>
            <input type="number" name="bomba_R" step="0.0001" value="<?= $editRecord['bomba_R'] ?>" required>

            <label>TAC Inicio</label>
            <input type="number" name="tac_inicio" step="0.0001" value="<?= $editRecord['tac_inicio'] ?>" required>

            <label>TAC Final</label>
            <input type="number" name="tac_fin" step="0.0001" value="<?= $editRecord['tac_fin'] ?>" required>

            <label>Volumen</label>
            <input type="number" name="volumen" step="0.01" value="<?= $editRecord['volumen'] ?>" required>

            <label>Observaciones</label>
            <textarea name="observaciones"><?= htmlspecialchars($editRecord['observaciones']) ?></textarea>

            <label>Responsable</label>
            <input type="text" name="responsable" value="<?= htmlspecialchars($editRecord['responsable']) ?>" required>

            <input type="submit" value="Actualizar">
        </form>
    </div>

<?php elseif ($tab === 'dqo_view' || $tab === 'dqo'): ?>

    <?php
    $res = $conn->query("SELECT * FROM dqo ORDER BY fecha_muestreo DESC");
    $dqo_records = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) $dqo_records[] = $row;
    }

    
$dqo_fechas = array_column($dqo_records, 'fecha_muestreo');
$dqo_valores = array_map(function($r) {
    return $r['dqo_mg_l'] ?? 0;
}, $dqo_records);
?>

<canvas id="graficaDQO" height="100" style="margin: 30px 0;"></canvas>
<script>
const ctxDQO = document.getElementById('graficaDQO').getContext('2d');
const etiquetasDQO = <?= json_encode(array_reverse($dqo_fechas)) ?>;
const valoresDQO = <?= json_encode(array_reverse($dqo_valores)) ?>;

new Chart(ctxDQO, {
    type: 'bar',
    data: {
        labels: etiquetasDQO,
        datasets: [{
            label: 'DQO (mg/L)',
            data: valoresDQO,
            backgroundColor: 'rgba(255, 99, 132, 0.5)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'DQO (mg/L)' }
            },
            x: {
                title: { display: true, text: 'Fecha de Muestreo' }
            }
        }
    }
});
</script>


    ?>

    <h2>Demanda Química de Oxígeno (DQO) </h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha de Muestreo</th>
                <th>Ubicación</th>
                <th>DQO (mg/L)</th>
                <th>Temperatura (°C)</th>
                <th>pH</th>
                <th>Observaciones</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($dqo_records): foreach ($dqo_records as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['fecha_muestreo']) ?></td>
                <td><?= htmlspecialchars($r['ubicacion']) ?></td>
                <td><?= $r['dqo_mg_l'] ?></td>
                <td><?= $r['temperatura'] ?></td>
                <td><?= $r['ph'] ?></td>
                <td><?= htmlspecialchars($r['observaciones']) ?></td>
                <td><a href="?tab=dqo_edit&id=<?= $r['id'] ?>" class="edit-btn">Editar</a></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="8">No hay registros de DQO.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

<?php elseif ($tab === 'dqo_add'): ?>

    <h2>Agregar Registro DQO</h2>
    <form method="post">
        <input type="hidden" name="action" value="dqo_add">

        <label>Fecha de Muestreo</label>
        <input type="date" name="fecha_muestreo" required>

        <label>Ubicación</label>
        <input type="text" name="ubicacion" required>

        <label>DQO (mg/L)</label>
        <input type="number" name="dqo_mg_l" step="0.01" required>

        <label>Temperatura (°C)</label>
        <input type="number" name="temperatura" step="0.01">

        <label>pH</label>
        <input type="number" name="ph" step="0.01">

        <label>Observaciones</label>
        <textarea name="observaciones"></textarea>

        <input type="submit" value="Guardar">
    </form>

<?php elseif ($tab === 'dqo_edit' && $editDqo): ?>

    <h2>Editar Registro DQO #<?= $editDqo['id'] ?></h2>
    <form method="post">
        <input type="hidden" name="action" value="dqo_edit">
        <input type="hidden" name="id" value="<?= $editDqo['id'] ?>">

        <label>Fecha de Muestreo</label>
        <input type="date" name="fecha_muestreo" value="<?= htmlspecialchars($editDqo['fecha_muestreo']) ?>" required>

        <label>Ubicación</label>
        <input type="text" name="ubicacion" value="<?= htmlspecialchars($editDqo['ubicacion']) ?>" required>

        <label>DQO (mg/L)</label>
        <input type="number" name="dqo_mg_l" step="0.01" value="<?= $editDqo['dqo_mg_l'] ?>" required>

        <label>Temperatura (°C)</label>
        <input type="number" name="temperatura" step="0.01" value="<?= $editDqo['temperatura'] ?>">

        <label>pH</label>
        <input type="number" name="ph" step="0.01" value="<?= $editDqo['ph'] ?>">

        <label>Observaciones</label>
        <textarea name="observaciones"><?= htmlspecialchars($editDqo['observaciones']) ?></textarea>

        <input type="submit" value="Actualizar">
    </form>

<?php elseif ($tab === 'dbo_view' || $tab === 'dbo'): ?>

    <?php
    $res = $conn->query("SELECT * FROM dbo ORDER BY fecha_muestreo DESC");
    $dbo_records = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) $dbo_records[] = $row;
    }
    ?>

    <h2>Demanda Bioquímica de Oxígeno (DBO) - Listado</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Fecha</th><th>Ubicación</th><th>DBO5 (mg/L)</th><th>DBO20 (mg/L)</th><th>Temperatura (°C)</th><th>pH</th><th>Observaciones</th><th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($dbo_records): foreach ($dbo_records as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['fecha_muestreo']) ?></td>
                <td><?= htmlspecialchars($r['ubicacion']) ?></td>
                <td><?= $r['dbo5_mg_l'] ?></td>
                <td><?= $r['dbo20_mg_l'] ?></td>
                <td><?= $r['temperatura'] ?></td>
                <td><?= $r['ph'] ?></td>
                <td><?= htmlspecialchars($r['observaciones']) ?></td>
                <td><a href="?tab=dbo_edit&id=<?= $r['id'] ?>" class="edit-btn">Editar</a></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="9">No hay registros de DBO.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

<?php elseif ($tab === 'dbo_add'): ?>

    <h2>Agregar Registro DBO</h2>
    <form method="post">
        <input type="hidden" name="action" value="dbo_add" />

        <label>Fecha de Muestreo</label>
        <input type="date" name="fecha_muestreo" required />

        <label>Ubicación</label>
        <input type="text" name="ubicacion" required />

        <label>DBO5 (mg/L)</label>
        <input type="number" step="0.01" name="dbo5_mg_l" required />

        <label>DBO20 (mg/L)</label>
        <input type="number" step="0.01" name="dbo20_mg_l" required />

        <label>Temperatura (°C)</label>
        <input type="number" step="0.01" name="temperatura" />

        <label>pH</label>
        <input type="number" step="0.01" name="ph" />

        <label>Observaciones</label>
        <textarea name="observaciones"></textarea>

        <input type="submit" value="Guardar" />
    </form>

<?php elseif ($tab === 'dbo_edit' && $editDbo): ?>

    <h2>Editar Registro DBO</h2>
    <form method="post">
        <input type="hidden" name="action" value="dbo_edit" />
        <input type="hidden" name="id" value="<?= $editDbo['id'] ?>" />

        <label>Fecha de Muestreo</label>
        <input type="date" name="fecha_muestreo" value="<?= htmlspecialchars($editDbo['fecha_muestreo']) ?>" required />

        <label>Ubicación</label>
        <input type="text" name="ubicacion" value="<?= htmlspecialchars($editDbo['ubicacion']) ?>" required />

        <label>DBO5 (mg/L)</label>
        <input type="number" step="0.01" name="dbo5_mg_l" value="<?= $editDbo['dbo5_mg_l'] ?>" required />

        <label>DBO20 (mg/L)</label>
        <input type="number" step="0.01" name="dbo20_mg_l" value="<?= $editDbo['dbo20_mg_l'] ?>" required />

        <label>Temperatura (°C)</label>
        <input type="number" step="0.01" name="temperatura" value="<?= $editDbo['temperatura'] ?>" />

        <label>pH</label>
        <input type="number" step="0.01" name="ph" value="<?= $editDbo['ph'] ?>" />

        <label>Observaciones</label>
        <textarea name="observaciones"><?= htmlspecialchars($editDbo['observaciones']) ?></textarea>

        <input type="submit" value="Actualizar" />
    </form>
<?php elseif ($tab === 'sst_view' || $tab === 'sst'): ?>

    <?php
    $res = $conn->query("SELECT * FROM sst ORDER BY fecha_muestreo DESC");
    $sst_records = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) $sst_records[] = $row;
    }
    ?>

    <h2>Sólidos Suspendidos Totales (SST) - Listado</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th><th>Fecha</th><th>Ubicación</th><th>SST (mg/L)</th><th>Temperatura (°C)</th><th>pH</th><th>Observaciones</th><th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($sst_records): foreach ($sst_records as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['fecha_muestreo']) ?></td>
                <td><?= htmlspecialchars($r['ubicacion']) ?></td>
                <td><?= $r['sst_mg_l'] ?></td>
                <td><?= $r['temperatura'] ?></td>
                <td><?= $r['ph'] ?></td>
                <td><?= htmlspecialchars($r['observaciones']) ?></td>
                <td><a href="?tab=sst_edit&id=<?= $r['id'] ?>" class="edit-btn">Editar</a></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="8">No hay registros de SST.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

<?php elseif ($tab === 'sst_add'): ?>

    <h2>Agregar Registro SST</h2>
    <form method="post">
        <input type="hidden" name="action" value="sst_add" />

        <label>Fecha de Muestreo</label>
        <input type="date" name="fecha_muestreo" required />

        <label>Ubicación</label>
        <input type="text" name="ubicacion" required />

        <label>SST (mg/L)</label>
        <input type="number" step="0.01" name="sst_mg_l" required />

        <label>Temperatura (°C)</label>
        <input type="number" step="0.01" name="temperatura" />

        <label>pH</label>
        <input type="number" step="0.01" name="ph" />

        <label>Observaciones</label>
        <textarea name="observaciones"></textarea>

        <input type="submit" value="Guardar" />
    </form>

<?php elseif ($tab === 'sst_edit' && $editSst): ?>

    <h2>Editar Registro SST</h2>
    <form method="post">
        <input type="hidden" name="action" value="sst_edit" />
        <input type="hidden" name="id" value="<?= $editSst['id'] ?>" />

        <label>Fecha de Muestreo</label>
        <input type="date" name="fecha_muestreo" value="<?= htmlspecialchars($editSst['fecha_muestreo']) ?>" required />

        <label>Ubicación</label>
        <input type="text" name="ubicacion" value="<?= htmlspecialchars($editSst['ubicacion']) ?>" required />

        <label>SST (mg/L)</label>
        <input type="number" step="0.01" name="sst_mg_l" value="<?= $editSst['sst_mg_l'] ?>" required />

        <label>Temperatura (°C)</label>
        <input type="number" step="0.01" name="temperatura" value="<?= $editSst['temperatura'] ?>" />

        <label>pH</label>
        <input type="number" step="0.01" name="ph" value="<?= $editSst['ph'] ?>" />

        <label>Observaciones</label>
        <textarea name="observaciones"><?= htmlspecialchars($editSst['observaciones']) ?></textarea>

        <input type="submit" value="Actualizar" />
    </form>

    <?php elseif ($tab === 'calendario'): ?>
    <h2>Calendario de Mantenimientos</h2>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <div id="calendar"></div>

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

                                fetch('notificar.php');
                            }
                        });
                    }
                }
            });
            calendar.render();
        });

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



<?php else: ?>



<p>Pestaña no válida o registro no encontrado.</p>

<?php endif; ?>

</body>
</html>

</body>
</html>