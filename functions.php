<?php

function isLoggedIn() {
    return isset($_SESSION['username']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    if (empty($url) || !is_string($url)) {
        throw new InvalidArgumentException("URL inválida para redirección: $url");
    }
    
    if (parse_url($url, PHP_URL_SCHEME) === null) {
        $url = '/' . ltrim($url, '/');
    }

    header("Location: $url");
    exit();
}

function logError($message) {
    $logFile = __DIR__ . '/error.log';
    if (!is_writable($logFile)) {
        throw new RuntimeException("El archivo de log no es escribible: $logFile");
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function handleError($message) {
    logError($message);
    echo "Ocurrió un error. Por favor, intenta de nuevo más tarde.";
}

function sanitizeFileName($fileName) {
    return preg_replace("/[^a-zA-Z0-9\.\-_]/", "_", $fileName);
}

// Nueva función para obtener asignaturas del profesor
function getSubjectsForTeacher($teacher_id) {
    global $conn;
    $teacher_id = (int)$teacher_id;
    $sql = "SELECT s.* FROM subjects s JOIN teachers_subjects ts ON s.id = ts.subject_id WHERE ts.teacher_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        handleError("Error al preparar la consulta: " . $conn->error);
    }
    $stmt->bind_param('i', $teacher_id);
    if (!$stmt->execute()) {
        handleError("Error al ejecutar la consulta: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    return $subjects;
}

?>
