<?php
session_start();
include 'config.php';
include 'functions.php';
include 'templates/header.php';

// Verifica si el usuario es profesor
if (!isLoggedIn() || $_SESSION['role'] !== 'teacher') {
    logError("No estás autorizado para acceder a esta página.");
    redirect('login.php');
}

// Obtener las asignaturas permitidas para este profesor
$teacher_id = $_SESSION['user_id'];
$sql = "SELECT subjects.id, subjects.name FROM subjects 
        INNER JOIN teachers_subjects ON subjects.id = teachers_subjects.subject_id 
        WHERE teachers_subjects.teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

// Si el profesor no tiene asignaturas asignadas, redirigir
if ($result->num_rows == 0) {
    echo "No tienes asignaturas asignadas.";
    include 'templates/footer.php';
    exit();
}

$subjects = $result->fetch_all(MYSQLI_ASSOC);

// Procesar la subida de la tarea
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = $_POST['subject_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $task_date = $_POST['task_date'];
    $target_dir = "uploads/";
    $file_paths = [];

    // Verificar que el profesor tenga acceso a la asignatura seleccionada
    $subject_found = false;
    foreach ($subjects as $subject) {
        if ($subject['id'] == $subject_id) {
            $subject_found = true;
            break;
        }
    }

    if (!$subject_found) {
        handleError("No tienes permiso para subir tareas en esta asignatura.");
        exit();
    }

    // Procesar el archivo subido
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {
        $file = $_FILES["file"];
        $target_file = $target_dir . sanitizeFileName(basename($file["name"]));
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $file_paths[] = $target_file;
        } else {
            handleError("Error al mover el archivo.");
        }
    }

    // Guardar la tarea en la base de datos
    $file_paths_str = implode(',', $file_paths);
    $sql = "INSERT INTO tasks (subject_id, title, description, task_date, file_path) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        handleError("Error al preparar la consulta: " . $conn->error);
    }
    $stmt->bind_param('issss', $subject_id, $title, $description, $task_date, $file_paths_str);
    if (!$stmt->execute()) {
        handleError("Error al subir tarea: " . $stmt->error);
    } else {
        echo "Tarea subida exitosamente.";
    }
}
?>

<div class="container">
    <h1>Subir Tarea</h1>
    <form method="POST" action="" enctype="multipart/form-data">
        <select name="subject_id" required>
            <option value="">Seleccionar Asignatura</option>
            <?php
            foreach ($subjects as $subject) {
                echo "<option value='" . htmlspecialchars($subject['id']) . "'>" . htmlspecialchars($subject['name']) . "</option>";
            }
            ?>
        </select>
        <input type="text" name="title" placeholder="Título de la Tarea" required>
        <textarea name="description" placeholder="Descripción de la Tarea" required></textarea>
        
        <!-- Campo de fecha con un calendario -->
        <input type="text" id="task_date" name="task_date" placeholder="Seleccionar Fecha" required>
        
        <!-- Campo de archivo -->
        <input type="file" name="file">
        
        <button type="submit" name="upload_task">Subir Tarea</button>
    </form>
</div>

<script>
$(function() {
    $("#task_date").datepicker({
        dateFormat: "yy-mm-dd"
    });
});
</script>

<?php include 'templates/footer.php'; ?>
