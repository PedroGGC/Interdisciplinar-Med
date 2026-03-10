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
    <title>Rodízios do Preceptor</title>
    <style>
        .bg-custom-green { background-color: #005E00 !important; color: white !important; }
        .border-custom-green { border-color: #005E00 !important; }
        .badge-custom-green { background-color: #005E00 !important; color: white !important; border-radius: 20px !important; padding: 5px 12px !important; }
    </style>
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
                <h2 class="mb-1">Meus Rodízios</h2>
                <p class="text-muted mb-0">Módulos e grupos atualmente sob sua preceptoria</p>
            </div>

            <?php
            $idpreceptor = $_SESSION['idusuario'] ?? null;
            if ($idpreceptor) {
                // ... (mantendo lógica de consulta e filtragem igual)
                $rodizios = [];
                $sql = "SELECT m.idmodulo, m.nome_modulo, m.periodo, r.inicio AS rodizio_inicio, r.fim AS rodizio_fim,
                                GROUP_CONCAT(DISTINCT sg.nome_subgrupo ORDER BY sg.nome_subgrupo SEPARATOR ', ') AS subgrupos
                        FROM preceptores_modulos pm
                        JOIN modulos m ON pm.idmodulo = m.idmodulo
                        JOIN rodizios r ON r.idmodulo = m.idmodulo
                        JOIN rodizios_subgrupos rs ON rs.idrodizio = r.idrodizio
                        JOIN subgrupos sg ON sg.idsubgrupo = rs.idsubgrupo
                        WHERE pm.idusuario = ?
                            AND (pm.data_inicio IS NULL OR pm.data_inicio <= r.fim)
                            AND (pm.data_fim IS NULL OR pm.data_fim >= r.inicio)
                        GROUP BY m.idmodulo, m.nome_modulo, m.periodo, r.inicio, r.fim
                        ORDER BY CAST(m.periodo AS UNSIGNED), m.nome_modulo, r.inicio";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("i", $idpreceptor);
                    if ($stmt->execute()) {
                        $res = $stmt->get_result();
                        while ($row = $res->fetch_assoc()) {
                            $subgruposStr = $row['subgrupos'];
                            $subgruposList = $subgruposStr ? explode(', ', $subgruposStr) : [];
                            $dataInicio = $row['rodizio_inicio'] ? date('d/m/Y', strtotime($row['rodizio_inicio'])) : 'Não definida';
                            $dataFim = $row['rodizio_fim'] ? date('d/m/Y', strtotime($row['rodizio_fim'])) : 'Não definida';
                            $rodizios[] = [
                                'idmodulo' => $row['idmodulo'],
                                'nomeModulo' => $row['nome_modulo'],
                                'periodo' => $row['periodo'],
                                'dataInicio' => $dataInicio,
                                'dataFim' => $dataFim,
                                'subgrupos' => $subgruposList
                            ];
                        }
                    }
                    $stmt->close();
                }

                $subPermitidos = [];
                $sqlPermitidos = "SELECT DISTINCT sg.nome_subgrupo FROM horarios h JOIN subgrupos sg ON sg.idsubgrupo = h.idsubgrupo WHERE h.idpreceptor = ?";
                if ($stmtP = $conn->prepare($sqlPermitidos)) {
                    $stmtP->bind_param("i", $idpreceptor);
                    if ($stmtP->execute()) {
                        $resP = $stmtP->get_result();
                        while ($r = $resP->fetch_row()) { $subPermitidos[] = $r[0]; }
                    }
                    $stmtP->close();
                }

                foreach ($rodizios as &$rod) {
                    $rod['subgrupos'] = array_values(array_filter($rod['subgrupos'], function ($sg) use ($subPermitidos) {
                        return in_array($sg, $subPermitidos);
                    }));
                }
                unset($rod);

                $rodiziosPorModulo = [];
                foreach ($rodizios as $rod) { $rodiziosPorModulo[$rod['nomeModulo']][] = $rod; }

                if (empty($rodizios)) {
                    echo '<div class="alert alert-info border-0 shadow-sm py-4"><i class="bi bi-info-circle-fill me-2"></i> Você não está associado a nenhum módulo ativo.</div>';
                }

                foreach ($rodiziosPorModulo as $nomeModulo => $lista) {
                    echo '<div class="mb-5">
                            <h4 class="mb-4 d-flex align-items-center">
                                <span class="badge-pill-glow badge-info-glow me-3" style="text-transform:none; letter-spacing:0;">' . htmlspecialchars($lista[0]['periodo']) . 'º Período</span>
                                ' . htmlspecialchars($nomeModulo) . '
                            </h4>
                            <div class="row g-4">';
                    foreach ($lista as $rod) {
                        echo '<div class="col-md-6 col-lg-4">
                                <div class="card h-100 border-0 shadow-sm overflow-hidden">
                                    <div class="card-header bg-accent p-1 opacity-50"></div>
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between mb-4">
                                            <div>
                                                <div class="small text-muted mb-1 text-uppercase fw-bold" style="letter-spacing:1px; font-size:10px;">Início</div>
                                                <div class="fw-medium text-white">' . htmlspecialchars($rod['dataInicio']) . '</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="small text-muted mb-1 text-uppercase fw-bold" style="letter-spacing:1px; font-size:10px;">Término</div>
                                                <div class="fw-medium text-white">' . htmlspecialchars($rod['dataFim']) . '</div>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="small text-muted mb-3 text-uppercase fw-bold" style="letter-spacing:1px; font-size:10px;">Subgrupos Atribuídos</div>
                                            <div class="d-flex flex-wrap gap-2">';
                                            
                        if (empty($rod['subgrupos'])) {
                            echo '<span class="text-muted small italic">Nenhum subgrupo</span>';
                        } else {
                            foreach ($rod['subgrupos'] as $sg) {
                                echo '<span class="badge-pill-glow badge-success-glow" style="padding: 4px 10px; font-size:10px;">' . htmlspecialchars($sg) . '</span>';
                            }
                        }

                        echo '              </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-surface-2 border-0 p-3">
                                        <a href="grupos.php" class="btn btn-sm btn-action w-100 text-muted" style="height:auto; padding:8px;">
                                            <i class="bi bi-arrow-right-circle me-2"></i> Gerenciar Integrantes
                                        </a>
                                    </div>
                                </div>
                              </div>';
                    }
                    echo '</div></div>';
                }
            } else {
                echo '<div class="alert alert-danger border-0">Usuário não autenticado.</div>';
            }
            ?>
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