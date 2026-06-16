<?php
/*
 * SQL para crear la tabla:
 *
 * CREATE TABLE IF NOT EXISTS tareas (
 *     id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 *     texto         VARCHAR(255) NOT NULL,
 *     completada    TINYINT(1)   NOT NULL DEFAULT 0,
 *     fecha_creacion DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
 * );
 */

$host   = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'db_alumnoXX';
$user   = getenv('DB_USER') ?: 'alumnoXX';
$pass   = getenv('DB_PASS') ?: 'alumnoXX';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('<p style="color:red">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

// Acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $texto = trim($_POST['texto'] ?? '');
        if ($texto !== '') {
            $stmt = $pdo->prepare('INSERT INTO tareas (texto) VALUES (:texto)');
            $stmt->execute([':texto' => $texto]);
        }
    } elseif ($accion === 'completar') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE tareas SET completada = NOT completada WHERE id = :id')
            ->execute([':id' => $id]);
    } elseif ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM tareas WHERE id = :id')
            ->execute([':id' => $id]);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Obtener tareas
$tareas = $pdo->query('SELECT * FROM tareas ORDER BY completada ASC, fecha_creacion DESC')
              ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lista de Tareas</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: system-ui, sans-serif;
    background: #f0f2f5;
    color: #222;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    padding: 2rem 1rem;
  }

  .container {
    width: 100%;
    max-width: 560px;
  }

  h1 {
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
    color: #1a1a2e;
    text-align: center;
  }

  /* Formulario */
  .form-agregar {
    display: flex;
    gap: .5rem;
    margin-bottom: 1.5rem;
  }

  .form-agregar input[type="text"] {
    flex: 1;
    padding: .6rem .9rem;
    border: 1.5px solid #ccc;
    border-radius: 8px;
    font-size: 1rem;
    outline: none;
    transition: border-color .2s;
  }

  .form-agregar input[type="text"]:focus { border-color: #4f8ef7; }

  .btn {
    padding: .6rem 1.1rem;
    border: none;
    border-radius: 8px;
    font-size: .9rem;
    cursor: pointer;
    font-weight: 600;
    transition: opacity .15s;
  }
  .btn:hover { opacity: .85; }

  .btn-agregar  { background: #4f8ef7; color: #fff; }
  .btn-completar { background: #22c55e; color: #fff; }
  .btn-eliminar  { background: #ef4444; color: #fff; }

  /* Lista */
  .lista-tareas { list-style: none; display: flex; flex-direction: column; gap: .6rem; }

  .tarea {
    background: #fff;
    border-radius: 10px;
    padding: .75rem 1rem;
    display: flex;
    align-items: center;
    gap: .75rem;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
  }

  .tarea.completada .texto { text-decoration: line-through; color: #999; }

  .texto { flex: 1; font-size: 1rem; word-break: break-word; }

  .fecha { font-size: .72rem; color: #aaa; white-space: nowrap; }

  .acciones { display: flex; gap: .4rem; flex-shrink: 0; }

  .vacio {
    text-align: center;
    color: #aaa;
    padding: 2rem 0;
    font-size: .95rem;
  }
</style>
</head>
<body>
<div class="container">
  <h1>Lista de Tareas</h1>

  <form class="form-agregar" method="post">
    <input type="hidden" name="accion" value="agregar">
    <input type="text" name="texto" placeholder="Nueva tarea…" autofocus required maxlength="255">
    <button class="btn btn-agregar" type="submit">Agregar</button>
  </form>

  <?php if (empty($tareas)): ?>
    <p class="vacio">No hay tareas todavía. ¡Agrega una!</p>
  <?php else: ?>
  <ul class="lista-tareas">
    <?php foreach ($tareas as $t): ?>
    <li class="tarea <?= $t['completada'] ? 'completada' : '' ?>">
      <span class="texto"><?= htmlspecialchars($t['texto']) ?></span>
      <span class="fecha"><?= date('d/m/Y H:i', strtotime($t['fecha_creacion'])) ?></span>
      <div class="acciones">
        <form method="post">
          <input type="hidden" name="accion" value="completar">
          <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
          <button class="btn btn-completar" type="submit" title="<?= $t['completada'] ? 'Desmarcar' : 'Completar' ?>">
            <?= $t['completada'] ? '↩' : '✓' ?>
          </button>
        </form>
        <form method="post" onsubmit="return confirm('¿Eliminar esta tarea?')">
          <input type="hidden" name="accion" value="eliminar">
          <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
          <button class="btn btn-eliminar" type="submit" title="Eliminar">✕</button>
        </form>
      </div>
    </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div>
</body>
</html>
