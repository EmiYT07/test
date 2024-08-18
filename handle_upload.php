<?php
session_start();
include 'config.php';
include 'functions.php';

if (!isLoggedIn()) {
    handleError("No estás autorizado para subir tareas.");
    exit();
}

$subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
$title = isset($_POST['title']) ? $_POST['title'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$file = $_FILES['file'];

if ($subject_id <= 0 || empty($title) || empty($description) || $file['error'] !== UPLOAD_ERR_OK) {
    handleError("Datos de subida inválidos.");
    exit();
}

$target_dir = 'uploads/';
$target_file = $target_dir . basename($file['name']);
$upload_ok = 1;

// Verifica si el archivo es un tipo permitido
$file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$allowed_types = ['jpg', 'png', 'pdf', 'docx', 'pptx'];
if (!in_array($file_type, $allowed_types)) {
    handleError("Tipo de archivo no permitido.");
    $upload_ok = 0;
}

// Verifica el tamaño del archivo (por ejemplo, límite de 10MB)
if ($file['size'] > 10485760) {
    handleError("El archivo es demasiado grande.");
    $upload_ok = 0;
}

// Intenta subir el archivo
if ($upload_ok && move_uploaded_file($file['tmp_name'], $target_file)) {
    // Inserta los datos en la base de datos
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        handleError("Error de conexión a la base de datos: " . $conn->connect_error);
        exit();
    }

    $sql = "INSERT INTO tasks (subject_id, title, description, file_path) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        handleError("Error al preparar la consulta: " . $conn->error);
        exit();
    }

    $file_path = $target_file;
    $stmt->bind_param('isss', $subject_id, $title, $description, $file_path);
    if ($stmt->execute()) {
        redirect('subject.php?id=' . $subject_id);
    } else {
        handleError("Error al insertar la tarea en la base de datos: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    handleError("Error al subir el archivo.");
}
?>
