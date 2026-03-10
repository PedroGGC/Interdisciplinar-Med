<?php
include('../../../cfg/config.php');

if (isset($_GET['periodo'])) {
    $periodo = $_GET['periodo'];

    $query = "SELECT idmodulo, nome_modulo FROM modulos WHERE periodo = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $periodo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            echo '<div class="d-flex flex-column gap-2">';
            while ($row = $result->fetch_assoc()) {
                echo '<div class="badge-pill-glow badge-info-glow w-100 py-2 justify-content-start" data-idmodulo="' . $row['idmodulo'] . '">';
                echo '<i class="bi bi-journal-medical me-2"></i>' . htmlspecialchars($row['nome_modulo']);
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p class="text-muted small mb-0">Nenhum módulo disponível.</p>';
        }

        $stmt->close();
    } else {
        echo '<p>Erro ao preparar a consulta: ' . $conn->error . '</p>';
    }
}

$conn->close();
