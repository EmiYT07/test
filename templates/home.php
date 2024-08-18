<div class="container">
    <h1>Bienvenido a la plataforma educativa</h1>
    <?php
    $sql = "SELECT * FROM subjects";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<div class='subject'>";
            echo "<h2>" . htmlspecialchars($row['name']) . "</h2>";
            echo "<a href='subject.php?id=" . $row['id'] . "'>Ver tareas</a>";
            echo "</div>";
        }
    } else {
        echo "No hay asignaturas disponibles.";
    }
    ?>
</div>
