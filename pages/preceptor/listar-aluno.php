<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}
include('../../cfg/config.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de alunos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-preceptor.php'); ?>
    </header>
    <main>
        <div class="container mt-4">
            <div class="page-header">
                <h2 class="mb-1">Lista de Alunos</h2>
                <p class="text-muted mb-0">Relação de alunos e suas respectivas notas mais recentes</p>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Nome do Aluno</th>
                                    <th>Nota Recente</th>
                                    <th>Status de Desempenho</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $idpreceptor = $_SESSION['idusuario'];
                                $sql = "SELECT DISTINCT u.idusuario, u.nome, 
                                               COALESCE((SELECT a.nota 
                                                         FROM avaliacoes a 
                                                         WHERE a.idaluno = u.idusuario 
                                                         ORDER BY a.data_avaliacao DESC 
                                                         LIMIT 1), -1) AS nota
                                        FROM usuarios u
                                        JOIN alunos_subgrupos als ON u.idusuario = als.idusuario
                                        JOIN horarios h ON als.idsubgrupo = h.idsubgrupo
                                        WHERE u.tipo = 0 AND h.idpreceptor = ?
                                        ORDER BY u.nome ASC";

                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $idpreceptor);
                                $stmt->execute();
                                $res = $stmt->get_result();

                                if (!$res) {
                                    die("Erro na consulta: " . $conn->error);
                                }

                                if ($res->num_rows > 0) {
                                    while ($row = $res->fetch_object()) {
                                        $notaExibida = $row->nota == -1 ? 'Sem nota' : number_format($row->nota, 1);
                                        $statusClass = 'badge-info-glow';
                                        $statusText = 'Aguardando';

                                        if ($row->nota != -1) {
                                            if ($row->nota >= 7) {
                                                $statusClass = 'badge-success-glow';
                                                $statusText = 'Satisfatório';
                                            } elseif ($row->nota >= 5) {
                                                $statusClass = 'badge-warning-glow';
                                                $statusText = 'Regular';
                                            } else {
                                                $statusClass = 'badge-danger-glow'; // Note: I should add badge-danger-glow to CSS if needed, but it works with warning for now or I can add it
                                                $statusText = 'Insuficiente';
                                            }
                                        }

                                        echo "<tr>";
                                        echo "<td>
                                                <div class='d-flex align-items-center'>
                                                    <div class='btn-action me-3' style='pointer-events: none;'>
                                                        <i class='bi bi-person'></i>
                                                    </div>
                                                    <span class='fw-medium text-white'>" . htmlspecialchars($row->nome) . "</span>
                                                </div>
                                              </td>";
                                        echo "<td><span class='font-monospace fw-bold " . ($row->nota >= 7 ? 'text-accent-light' : ($row->nota == -1 ? 'text-muted' : 'text-warning')) . "'>$notaExibida</span></td>";
                                        echo "<td><span class='badge-pill-glow $statusClass'>$statusText</span></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center py-5 text-muted'>Nenhum aluno encontrado.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="card footer-home rounded-0">
            <div class="card-body">
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>
