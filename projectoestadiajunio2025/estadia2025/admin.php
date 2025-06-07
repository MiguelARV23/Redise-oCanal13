<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
$usuario = $_SESSION['usuario'];

include 'conexion.php';

// Procesar formulario de nota
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asunto'], $_POST['descripcion'])) {
    $asunto = $conexion->real_escape_string($_POST['asunto']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    $autor = $conexion->real_escape_string($usuario); // el que está en sesión

    $sql_insert_nota = "INSERT INTO notas (asunto, descripcion, autor, fecha_creacion) 
                        VALUES ('$asunto', '$descripcion', '$autor', NOW())";

    if ($conexion->query($sql_insert_nota)) {
        header("Location: admin.php?pagina=1");
        exit;
    } else {
        echo "Error al subir la nota: " . $conexion->error;
    }
}

// Procesar formulario de noticia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'], $_POST['categoria'], $_POST['estado']) && isset($_FILES['imagen'])) {
    $titulo = $conexion->real_escape_string($_POST['titulo']);
    $autor = $conexion->real_escape_string($usuario); // desde sesión
    $categoria = $conexion->real_escape_string($_POST['categoria']);
    $estado = $conexion->real_escape_string($_POST['estado']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);

    $imagen_blob = addslashes(file_get_contents($_FILES['imagen']['tmp_name']));

    $sql_insert_noticia = "INSERT INTO noticias (titulo, autor, categoria, estado, descripcion, imagen, fecha_creacion) 
                           VALUES ('$titulo', '$autor', '$categoria', '$estado', '$descripcion', '$imagen_blob', NOW())";

    if ($conexion->query($sql_insert_noticia)) {
        header("Location: admin.php");
        exit;
    } else {
        echo "Error al subir la noticia: " . $conexion->error;
    }
}


$notas_por_pagina = 6;

// Página actual
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $notas_por_pagina;

// Consulta total de notas
$sql_total = "SELECT COUNT(*) as total FROM notas";
$total_resultado = $conexion->query($sql_total);
$total_filas = $total_resultado->fetch_assoc()['total'];
$total_paginas = ceil($total_filas / $notas_por_pagina);

// Consulta para notas paginadas
$sql_notas = "SELECT * FROM notas ORDER BY fecha_creacion DESC LIMIT $inicio, $notas_por_pagina";
$result_notas = $conexion->query($sql_notas);

$sql_noticias = "SELECT * FROM noticias ORDER BY fecha_creacion DESC";
$result_noticias = $conexion->query($sql_noticias);

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "SELECT n.id, n.titulo, n.descripcion, n.fecha_creacion, 
                   n.categorias, n.estado, n.imagen 
            FROM noticias n 
            WHERE n.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($noticia = $result->fetch_assoc()) {
            // Convertir la imagen BLOB a base64
            if (!empty($noticia['imagen'])) {
                $noticia['imagen'] = base64_encode($noticia['imagen']);
            } else {
                $noticia['imagen'] = ""; // O puedes poner una imagen por defecto
            }
            echo json_encode($noticia);
        } else {
            echo json_encode(["error" => "No encontrada"]);
        }
    } else {
        echo json_encode(["error" => "Error en la consulta"]);
    }
    $stmt->close();
} else {
    echo json_encode(["error" => "ID inválido"]);
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
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .logo img {
            width: 100%;
            max-height: 100px;
            object-fit: contain;
            padding: 10px;
        }

        .sidebar {
            width: 250px;
            background-color: #c62828;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .sidebar .logo {
            padding: 20px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar .nav-button {
            width: 100%;
            padding: 15px;
            border: none;
            background: none;
            color: white;
            text-align: left;
            font-size: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar .nav-button:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .bienvenida-overlay {
            position: fixed;
            top: 0;
            left: 250px; /* o el ancho de tu barra lateral */
            width: calc(100% - 250px);
            height: 100vh;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }


        .content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            position: relative;
        }

        .action-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1001;
            display: none;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="IMG\cropped-LOGO-CANAL-TRECE.png" alt="Logo">
    </div>
    <button class="nav-button" onclick="mostrarSeccion('notas')">Notas</button>
    <button class="nav-button" onclick="mostrarSeccion('noticias')">Noticias</button>
    <button class="nav-button" onclick="mostrarSeccion('programacion')">Programación</button>
</div>

<div class="content">
<div id="mensaje-bienvenida" class="bienvenida-overlay">
  <h2>Bienvenido <?php echo htmlspecialchars($usuario); ?></h2>
</div>
    <div id="seccion-notas" class="seccion" style="display: none;">
        <h3>Notas</h3>
        <?php
        if ($result_notas->num_rows > 0) {
            echo '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';
        
            while ($row = $result_notas->fetch_assoc()) {
                $asunto = htmlspecialchars($row['asunto']);
                $descripcion = htmlspecialchars($row['descripcion']);
                $autor = htmlspecialchars($row['autor']);
                $fecha = date("d M Y", strtotime($row['fecha_creacion']));
                $id = $row['id'];
        
                echo '
                <div class="col">
                    <div class="card h-100 shadow-sm nota-card" onclick="verNotaCompleta(' . $id . ')">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold">' . $asunto . '</h5>
                            <p class="card-text mb-2 text-muted small"><strong>Autor:</strong> ' . $autor . '</p>
                            <p class="card-text mb-2 text-muted small"><strong>Fecha:</strong> ' . $fecha . '</p>
                            <p class="card-text flex-grow-1">' . substr($descripcion, 0, 100) . '...</p>
                            <div class="mt-auto">
                                <button class="btn btn-sm btn-outline-primary mt-2" onclick="verNotaCompleta(' . $id . ')">Leer más</button>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        
            echo '</div>'; //linea 231
            // Paginación
            echo '<nav aria-label="Paginación de notas" class="mt-4"><ul class="pagination justify-content-center">';
            for ($i = 1; $i <= $total_paginas; $i++) {
            $active = ($i == $pagina_actual) ? 'active' : '';
            echo '<li class="page-item ' . $active . '"><a class="page-link" href="?pagina=' . $i . '">' . $i . '</a></li>';
            }
            echo '</ul></nav>';
            } else {
                echo '<p>No hay notas disponibles.</p>';
            }
        ?>
    </div>

    <div id="seccion-noticias" class="seccion" style="display: none;">
        <h3>Noticias</h3>
        <?php
        if ($result_noticias->num_rows > 0) {
            echo '<div class="row row-cols-1 row-cols-md-2 g-4">';
        
            while ($row = $result_noticias->fetch_assoc()) {
                // Convertir imagen BLOB a base64
                $imagen = base64_encode($row['imagen']);
                $titulo = htmlspecialchars($row['titulo']);
                $autor = htmlspecialchars($row['autor']);
                $fecha = date("d M Y", strtotime($row['fecha_creacion']));
                $estado = htmlspecialchars($row['estado']);
                $categoria = htmlspecialchars($row['categoria']);
                $descripcion = htmlspecialchars($row['descripcion']);
                $id = $row['id'];
        
                echo '
                <div class="col">
                    <div class="card noticia-card h-100 text-white border-0" style="background-image: url(data:image/jpeg;base64,' . $imagen . '); background-size: cover; background-position: center;">
                        <div class="card-body d-flex flex-column justify-content-end bg-dark bg-opacity-50 p-3 rounded" style="height: 250px;" onclick="verNoticiaCompleta(' . $id . ')">
                            <h5 class="card-title fw-bold">
                                <a href="javascript:void(0);" onclick="verNoticiaCompleta(' . $id . ')" class="text-white text-decoration-underline">
                                    ' . $titulo . '
                                </a>
                            </h5>
                            <p class="card-text mb-1"><strong>Autor:</strong> ' . $autor . '</p>
                            <p class="card-text mb-1"><strong>Fecha:</strong> ' . $fecha . '</p>
                            <p class="card-text mb-1"><strong>Estado:</strong> ' . $estado . '</p>
                            <p class="card-text mb-1"><strong>Categoría:</strong> ' . $categoria . '</p>
                            <p class="card-text mb-1">' . substr($descripcion, 0, 80) . '...
                                <a href="javascript:void(0);" onclick="verNoticiaCompleta(' . $id . ')" class="text-white text-decoration-underline">Leer más</a>
                            </p>
                        </div>
                    </div>
                </div>';

            }
        
            echo '</div>';
        } else {
            echo '<p>No hay noticias disponibles.</p>';
        }
        ?>
    </div>

    <div id="seccion-programacion" class="seccion" style="display: none;">
        <h3>Programación</h3>
    </div>
</div>

<div id="extendida" class="seccion" style="display: none; position: relative;">
    <button class="btn btn-outline-secondary mb-3" onclick="volverNoticias()" style="position: absolute; top: 20px; left: 270px; z-index: 1000;">← Volver</button>
    <div id="contenido-noticia-completa" class="mt-5"></div>
</div>

<!-- Botones flotantes -->
<button class="btn btn-danger action-button" id="btn-notas" data-bs-toggle="modal" data-bs-target="#modalNota">Subir Nota</button>
<button class="btn btn-danger action-button" id="btn-noticias" data-bs-toggle="modal" data-bs-target="#modalNoticia">Subir Noticia</button>
<button class="btn btn-danger action-button" id="btn-programacion">Modificar Horario</button>

<!-- Modal para subir nota -->
<div class="modal fade" id="modalNota" tabindex="-1" aria-labelledby="modalNotaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="#">
      <div class="modal-header">
        <h5 class="modal-title" id="modalNotaLabel">Subir Nota</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label for="asunto" class="form-label">Asunto</label>
            <input type="text" class="form-control" id="asunto" name="asunto" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar Nota</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal para subir noticia -->
<div class="modal fade" id="modalNoticia" tabindex="-1" aria-labelledby="modalNoticiaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" action="#" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title" id="modalNoticiaLabel">Subir Noticia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" class="form-control" id="titulo" name="titulo" required>
        </div>
        <div class="mb-3">
            <label for="autor" class="form-label">Autor</label>
            <input type="text" class="form-control" id="autor" name="autor" value="<?php echo htmlspecialchars($usuario); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="categoria" class="form-label">Categoría</label>
            <input type="text" class="form-control" id="categoria" name="categoria" required>
        </div>
        <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <input type="text" class="form-control" id="estado" name="estado" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen</label>
            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar Noticia</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>


<script>
    function mostrarSeccion(seccion) {
        document.getElementById("mensaje-bienvenida").style.display = "none";

        // Ocultar todas las secciones y botones
        const secciones = document.querySelectorAll(".seccion");
        secciones.forEach(sec => sec.style.display = "none");

        const botones = document.querySelectorAll(".action-button");
        botones.forEach(btn => btn.style.display = "none");

        // Mostrar sección y botón correspondiente
        const actual = document.getElementById("seccion-" + seccion);
        if (actual) actual.style.display = "block";

        const boton = document.getElementById("btn-" + seccion);
        if (boton) boton.style.display = "block";
    }

    // 🔹 Mostrar detalle de una nota usando detalle.php
    function verNotaCompleta(id) {
        fetch('detalle.php?id=' + id + '&tipo=nota')
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    const contenedor = document.querySelector(".content");
                    contenedor.innerHTML = `
                        <button class="btn btn-outline-secondary mb-3" onclick="mostrarSeccion('notas')" style="position: absolute; top: 20px; left: 270px; z-index: 1000;">← Volver</button>
                        <div class="card shadow mb-4 p-4">
                            <h3>${data.asunto}</h3>
                            <p><strong>Autor:</strong> ${data.autor}</p>
                            <p><strong>Fecha:</strong> ${data.fecha_creacion}</p>
                            <p>${data.descripcion}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error("Error al obtener nota:", error);
            });
    }

    // 🔹 Mostrar detalle de una noticia usando detalle.php
    function verNoticiaCompleta(id) {
    fetch('detalle.php?id=' + id + '&tipo=noticia')
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                const contenedor = document.getElementById("contenido-noticia-completa");
                contenedor.innerHTML = `
                    <div class="card shadow mb-4">
                        <img src="data:image/jpeg;base64,${data.imagen}" class="card-img-top" style="object-fit: cover; height: 300px;">
                        <div class="card-body">
                            <h3 class="card-title">${data.titulo}</h3>
                            <p><strong>Fecha:</strong> ${data.fecha_creacion}</p>
                            <p><strong>Categorías:</strong> ${data.categorias || 'N/A'}</p>
                            ${data.estado ? `<p><strong>Estado:</strong> ${data.estado}</p>` : ''}
                            <p>${data.descripcion}</p>
                        </div>
                    </div>
                `;

                // Oculta las otras secciones
                document.getElementById("seccion-noticias").style.display = "none";
                document.getElementById("extendida").style.display = "block";
            }
        })
        .catch(error => {
            console.error("Error al obtener noticia:", error);
        });
}

function volverNoticias() {
    document.getElementById("extendida").style.display = "none";
    document.getElementById("seccion-noticias").style.display = "block";
}

    // 🔹 Cargar detalle al hacer clic desde tarjeta de noticia
    document.addEventListener("DOMContentLoaded", function () {
        const newsCards = document.querySelectorAll(".noticia-card");

        newsCards.forEach(card => {
            card.addEventListener("click", function () {
                const noticiaId = this.getAttribute("data-id");
                verNoticiaCompleta(noticiaId); // 👉 usar la nueva función
            });
        });
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
