<?php
session_start();
include 'config.php';
include 'functions.php';
include 'templates/header.php';

// Verifica que el ID de la asignatura se ha pasado y es un número
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
    exit();
}

$subject_id = (int)$_GET['id'];

// Conéctate a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    handleError("Error de conexión a la base de datos: " . $conn->connect_error);
    exit();
}

// Prepara la consulta para obtener las tareas de la asignatura
$sql = "SELECT title, description, file_path, task_date FROM tasks WHERE subject_id = ? ORDER BY task_date DESC";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    handleError("Error al preparar la consulta: " . $conn->error);
    exit();
}

$stmt->bind_param('i', $subject_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<div class='container'>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='task'>";
        echo "<h2>" . htmlspecialchars($row['title']) . "</h2>";
        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
        echo "<p><strong>Fecha de la tarea:</strong> " . htmlspecialchars($row['task_date']) . "</p>";
        
        // Obtener archivos asociados a la tarea
        $files = explode(',', $row['file_path']);
        echo "<div class='file-container'>";
        foreach ($files as $file) {
            $file = trim($file);
            if (file_exists($file)) {
                echo "<div class='file'>";
                echo "<a href='" . htmlspecialchars($file) . "' download>";
                echo "<p>Archivo</p>";
                echo "<p>" . htmlspecialchars(pathinfo($file, PATHINFO_BASENAME)) . "</p>";
                echo "</a>";
                echo "</div>";
            }
        }
        echo "</div>";

        echo "</div>";
    }
} else {
    echo "<p>No hay tareas disponibles para esta asignatura.</p>";
}
echo "</div>";

$stmt->close();
$conn->close();

include 'templates/footer.php';
?>
