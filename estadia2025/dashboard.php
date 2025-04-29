<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: index.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      overflow-x: hidden;
    }
    .sidebar {
      min-height: 100vh;
      background-color: #f8f9fa;
      border-right: 1px solid #dee2e6;
    }
    .menu-button {
      width: 100%;
      text-align: left;
    }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Menú lateral -->
    <div class="col-md-3 col-lg-2 sidebar p-3">
      <h5>Administración</h5>
      <button class="btn btn-outline-primary menu-button my-2" onclick="mostrarSeccion('notas')">Notas</button>
      <button class="btn btn-outline-secondary menu-button my-2" disabled>Noticias</button>
      <a href="logout.php" class="btn btn-danger menu-button mt-4">Cerrar sesión</a>
    </div>

    <!-- Contenido derecho -->
    <div class="col-md-9 col-lg-10 p-4">
      <div id="contenido"></div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script>
function mostrarSeccion(seccion) {
  if (seccion === 'notas') {
    document.getElementById('contenido').innerHTML = `
      <h4>Notas</h4>
      <form id="formNota" class="mb-4">
        <div class="mb-3">
          <label class="form-label">Nueva Nota</label>
          <textarea class="form-control" name="nota" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Guardar Nota</button>
      </form>
      <div id="listaNotas">
        <h5>Notas Guardadas</h5>
        <ul id="notasGuardadas" class="list-group"></ul>
      </div>
    `;

    // cargar notas desde localStorage
    const notas = JSON.parse(localStorage.getItem('notas') || '[]');
    const lista = document.getElementById('notasGuardadas');
    lista.innerHTML = '';
    notas.forEach(nota => {
      const li = document.createElement('li');
      li.className = 'list-group-item';
      li.textContent = nota;
      lista.appendChild(li);
    });

    // agregar evento al formulario
    document.getElementById('formNota').onsubmit = function(e) {
      e.preventDefault();
      const nuevaNota = this.nota.value;
      notas.push(nuevaNota);
      localStorage.setItem('notas', JSON.stringify(notas));
      mostrarSeccion('notas'); // recargar la vista
    };
  }
}
</script>
</body>
</html>
