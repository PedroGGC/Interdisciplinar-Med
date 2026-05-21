<?php
session_start();
include('../../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}

// Capturando os filtros do formulário
$filterDia = $_GET['dia'] ?? '';
$filterUnidade = $_GET['unidade'] ?? '';
$filterSubgrupo = $_GET['subgrupo'] ?? '';
$filterModulo = $_GET['modulo'] ?? '';
$filterPreceptor = $_GET['preceptor'] ?? '';

// Carregar opções para os selects do banco de dados
$unidades = $conn->query("SELECT DISTINCT idunidade, nome_unidade FROM unidades");
$subgrupos = $conn->query("SELECT DISTINCT idsubgrupo, nome_subgrupo FROM subgrupos");
$modulos = $conn->query("SELECT DISTINCT m.idmodulo, m.nome_modulo FROM modulos m JOIN unidades_modulos um ON m.idmodulo = um.idmodulo");
$preceptores = $conn->query("SELECT DISTINCT idusuario, nome FROM usuarios WHERE tipo = 1");

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-4">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Gerenciamento de Horários</h2>
                    <p class="text-muted mb-0">Configure a grade horária de preceptores e subgrupos</p>
                </div>
                <a href="preencher-horario.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Adicionar Horário</a>
            </div>
            
            <!-- Formulário de Filtro -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3" id="filterForm">
                        <div class="col-md-2">
                            <label for="dia" class="form-label text-muted small fw-bold">DIA DA SEMANA</label>
                            <select name="dia" id="dia" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="Segunda" <?php if ($filterDia == 'Segunda') echo 'selected'; ?>>Segunda</option>
                                <option value="Terça" <?php if ($filterDia == 'Terça') echo 'selected'; ?>>Terça</option>
                                <option value="Quarta" <?php if ($filterDia == 'Quarta') echo 'selected'; ?>>Quarta</option>
                                <option value="Quinta" <?php if ($filterDia == 'Quinta') echo 'selected'; ?>>Quinta</option>
                                <option value="Sexta" <?php if ($filterDia == 'Sexta') echo 'selected'; ?>>Sexta</option>
                                <option value="Sábado" <?php if ($filterDia == 'Sábado') echo 'selected'; ?>>Sábado</option>
                                <option value="Domingo" <?php if ($filterDia == 'Domingo') echo 'selected'; ?>>Domingo</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="unidade" class="form-label text-muted small fw-bold">UNIDADE</label>
                            <select name="unidade" id="unidade" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Todas</option>
                                <?php mysqli_data_seek($unidades, 0); while ($row = $unidades->fetch_assoc()): ?>
                                    <option value="<?php echo $row['nome_unidade']; ?>" <?php if ($filterUnidade == $row['nome_unidade']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($row['nome_unidade']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="subgrupo" class="form-label text-muted small fw-bold">SUBGRUPO</label>
                            <select name="subgrupo" id="subgrupo" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <?php mysqli_data_seek($subgrupos, 0); while ($row = $subgrupos->fetch_assoc()): ?>
                                    <option value="<?php echo $row['nome_subgrupo']; ?>" <?php if ($filterSubgrupo == $row['nome_subgrupo']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($row['nome_subgrupo']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="modulo" class="form-label text-muted small fw-bold">MÓDULO</label>
                            <select name="modulo" id="modulo" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <?php mysqli_data_seek($modulos, 0); while ($row = $modulos->fetch_assoc()): ?>
                                    <option value="<?php echo $row['nome_modulo']; ?>" <?php if ($filterModulo == $row['nome_modulo']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($row['nome_modulo']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="preceptor" class="form-label text-muted small fw-bold">PRECEPTOR</label>
                            <select name="preceptor" id="preceptor" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <?php mysqli_data_seek($preceptores, 0); while ($row = $preceptores->fetch_assoc()): ?>
                                    <option value="<?php echo $row['nome']; ?>" <?php if ($filterPreceptor == $row['nome']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($row['nome']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                        <a href="horarios.php" class="btn btn-secondary btn-sm" title="Limpar filtros"><i class="bi bi-x-circle"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden mb-5">
                <div class="card-body p-0">
                    <?php
                    // Re-construindo a consulta com os filtros
                    $query = "SELECT h.idhorario, u.nome_unidade, m.nome_modulo, p.nome AS preceptor_nome, h.dia_semana, h.hora_inicio, h.hora_fim, sg.nome_subgrupo 
                            FROM horarios h
                            JOIN unidades u ON h.idunidade = u.idunidade
                            JOIN modulos m ON h.idmodulo = m.idmodulo
                            JOIN usuarios p ON h.idpreceptor = p.idusuario
                            JOIN subgrupos sg ON h.idsubgrupo = sg.idsubgrupo
                            WHERE 1=1";

                    if ($filterDia) $query .= " AND h.dia_semana = '" . mysqli_real_escape_string($conn, $filterDia) . "'";
                    if ($filterUnidade) $query .= " AND u.nome_unidade LIKE '%" . mysqli_real_escape_string($conn, $filterUnidade) . "%'";
                    if ($filterSubgrupo) $query .= " AND sg.nome_subgrupo LIKE '%" . mysqli_real_escape_string($conn, $filterSubgrupo) . "%'";
                    if ($filterModulo) $query .= " AND m.nome_modulo LIKE '%" . mysqli_real_escape_string($conn, $filterModulo) . "%'";
                    if ($filterPreceptor) $query .= " AND p.nome LIKE '%" . mysqli_real_escape_string($conn, $filterPreceptor) . "%'";

                    $query .= " ORDER BY FIELD(h.dia_semana, 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'), h.hora_inicio";
                    $result = mysqli_query($conn, $query);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        echo '<div class="table-responsive">
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th>Unidade / Módulo</th>
                                        <th>Preceptor</th>
                                        <th>Agenda (Dia / Hora)</th>
                                        <th>Subgrupo</th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>';
                        
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>
                                    <td>
                                        <div class="fw-medium text-white">' . htmlspecialchars($row['nome_unidade']) . '</div>
                                        <div class="small text-muted">' . htmlspecialchars($row['nome_modulo']) . '</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="bi bi-person me-2"></i>
                                            <span>' . htmlspecialchars($row['preceptor_nome']) . '</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="badge-pill-glow badge-info-glow" style="min-width: 90px; text-align: center;">' . $row['dia_semana'] . '</span>
                                            <div class="small font-monospace text-white">' . date('H:i', strtotime($row['hora_inicio'])) . ' <span class="text-muted mx-1">→</span> ' . date('H:i', strtotime($row['hora_fim'])) . '</div>
                                        </div>
                                    </td>
                                    <td><span class="text-muted">' . htmlspecialchars($row['nome_subgrupo']) . '</span></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="preencher-horario.php?id=' . $row['idhorario'] . '" class="btn-action" title="Editar"><i class="bi bi-pencil"></i></a>
                                            <a href="excluir-horario.php?id=' . $row['idhorario'] . '" class="btn-action text-danger" title="Excluir" onclick="return confirm(\'Tem certeza que deseja excluir este horário?\');"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </td>
                                  </tr>';
                        }
                        echo '</tbody></table></div>';
                    } else {
                        echo '<div class="p-5 text-center text-muted"><i class="bi bi-info-circle fs-2 mb-3 d-block"></i>Não há horários correspondentes aos critérios.</div>';
                    }
                    ?>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
