<?php
session_start();
include('../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horários do Preceptor</title>
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
                <h2 class="mb-1">Meus Horários</h2>
                <p class="text-muted mb-0">Grade horária detalhada de suas atividades de preceptoria</p>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Agenda (Dia / Hora)</th>
                                    <th>Módulo</th>
                                    <th>Unidade</th>
                                    <th>Subgrupo</th>
                                    <th>Período</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $idpreceptor = $_SESSION['idusuario'] ?? null;

                                if ($idpreceptor) {
                                    $sql = "SELECT h.*, u.nome_unidade, m.nome_modulo, m.periodo, sg.nome_subgrupo
                                            FROM horarios h
                                            JOIN unidades u ON h.idunidade = u.idunidade
                                            JOIN modulos m ON h.idmodulo = m.idmodulo
                                            JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo
                                            WHERE h.idpreceptor = ?
                                            ORDER BY FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), h.hora_inicio";

                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $idpreceptor);
                                    if ($stmt->execute()) {
                                        $res = $stmt->get_result();
                                        if ($res->num_rows > 0) {
                                            while ($row = $res->fetch_object()) {
                                                echo "<tr>";
                                                echo "<td>
                                                        <div class='d-flex align-items-center gap-3'>
                                                            <span class='badge-pill-glow badge-info-glow' style='min-width: 90px; text-align: center;'>" . htmlspecialchars($row->dia_semana) . "</span>
                                                            <div class='font-monospace text-white small'>
                                                                " . date('H:i', strtotime($row->hora_inicio)) . " <span class='text-muted mx-1'>→</span> " . date('H:i', strtotime($row->hora_fim)) . "
                                                            </div>
                                                        </div>
                                                      </td>";
                                                echo "<td><span class='fw-medium text-white'>" . htmlspecialchars($row->nome_modulo) . "</span></td>";

                                                echo "<td><div class='small text-muted'><i class='bi bi-building me-1'></i> " . htmlspecialchars($row->nome_unidade) . "</div></td>";
                                                echo "<td><span class='badge-pill-glow' style='background:rgba(255,255,255,0.05); color:var(--text-muted);'>" . htmlspecialchars($row->nome_subgrupo) . "</span></td>";
                                                echo "<td><span class='text-muted small'>" . htmlspecialchars($row->periodo) . "º Semestre</span></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center py-5 text-muted'>Nenhum horário registrado em sua agenda.</td></tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center py-5 text-danger'>Erro ao buscar dados.</td></tr>";
                                    }
                                    $stmt->close();
                                } else {
                                    echo "<tr><td colspan='6' class='text-center py-5 text-warning'>Usuário não autenticado.</td></tr>";
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
            <div class="card-body"></div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>