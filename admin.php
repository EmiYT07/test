<?php
session_start();
include 'config.php';
include 'functions.php';
include 'templates/header.php';

// Verifica si el usuario es administrador
if (!isAdmin()) {
    logError("No estás autorizado para acceder a esta página.");
    redirect('login.php');
}

// Verifica si el usuario logueado es 'emiytcl' para permitir la creación de nuevas cuentas
$is_creator = isset($_SESSION['username']) && $_SESSION['username'] === 'emiytcl';

// Obtener la lista de asignaturas
$subjects = [];
$sql = "SELECT * FROM subjects";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Agregar una nueva asignatura
    if (isset($_POST['add_subject'])) {
        $subject_name = $_POST['subject_name'];
        $sql = "INSERT INTO subjects (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            handleError("Error al preparar la consulta: " . $conn->error);
        }
        $stmt->bind_param('s', $subject_name);
        if (!$stmt->execute()) {
            handleError("Error al agregar asignatura: " . $stmt->error);
        } else {
            echo "Asignatura agregada exitosamente.";
        }
    }

    // Subir una tarea
    if (isset($_POST['upload_task'])) {
        $subject_id = $_POST['subject_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $task_date = $_POST['task_date'];
        $target_dir = "uploads/";
        $file_paths = [];

        // Verifica si el profesor tiene permiso para subir tareas a esta asignatura
        if (isAdmin() || isTeacherForSubject($subject_id)) {
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
        } else {
            handleError("No tienes permiso para subir tareas a esta asignatura.");
        }
    }

    // Crear una nueva cuenta de profesor
    if ($is_creator && isset($_POST['create_teacher'])) {
        $new_username = $_POST['new_username'];
        $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $subject_id = $_POST['subject_id'];

        // Crear el nuevo usuario (profesor)
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'teacher')";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            handleError("Error al preparar la consulta: " . $conn->error);
        }
        $stmt->bind_param('ss', $new_username, $new_password);
        if (!$stmt->execute()) {
            handleError("Error al crear nueva cuenta de profesor: " . $stmt->error);
        }

        // Obtener el ID del nuevo profesor
        $teacher_id = $stmt->insert_id;

        // Asignar la asignatura al nuevo profesor
        $sql = "INSERT INTO teachers_subjects (teacher_id, subject_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            handleError("Error al preparar la consulta: " . $conn->error);
        }
        $stmt->bind_param('ii', $teacher_id, $subject_id);
        if (!$stmt->execute()) {
            handleError("Error al asignar la asignatura al profesor: " . $stmt->error);
        } else {
            echo "Profesor creado y asignado a la asignatura exitosamente.";
        }
    }
}
?>

<div class="container">
    <h1>Panel de Administración</h1>

    <h2>Agregar Asignatura</h2>
    <form method="POST" action="">
        <input type="text" name="subject_name" placeholder="Nombre de la Asignatura" required>
        <button type="submit" name="add_subject">Agregar</button>
    </form>

    <h2>Subir Tarea</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <select name="subject_id" required>
            <option value="">Seleccionar Asignatura</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?php echo htmlspecialchars($subject['id']); ?>">
                    <?php echo htmlspecialchars($subject['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="title" placeholder="Título de la Tarea" required>
        <textarea name="description" placeholder="Descripción de la Tarea" required></textarea>
        
        <!-- Campo de fecha como calendario -->
        <input type="text" id="task_date" name="task_date" placeholder="Seleccionar Fecha" required>
        
        <!-- Campo de archivo -->
        <input type="file" name="file">
        
        <button type="submit" name="upload_task">Subir Tarea</button>
    </form>

    <?php if ($is_creator): ?>
        <h2>Crear Nueva Cuenta de Profesor</h2>
        <form method="POST" action="">
            <input type="text" name="new_username" placeholder="Nombre de Usuario" required>
            <input type="password" name="new_password" placeholder="Contraseña" required>
            
            <!-- Selección de asignatura -->
            <select name="subject_id" required>
                <option value="">Seleccionar Asignatura</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo htmlspecialchars($subject['id']); ?>">
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" name="create_teacher">Crear Profesor</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el selector de fecha
    flatpickr('#task_date', {
        dateFormat: 'Y-m-d',
        minDate: 'today'
    });
});
</script>
