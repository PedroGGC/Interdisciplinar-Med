<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../index.php';</script>";
    exit();
}
include('../../../cfg/config.php');

$queryGrupos = "SELECT g.nome_grupo, s.nome_subgrupo, r.periodo, r.inicio, r.fim, m.nome_modulo, s.idsubgrupo 
                FROM grupos g 
                JOIN subgrupos s ON g.idgrupo = s.idgrupo 
                JOIN rodizios_subgrupos rs ON s.idsubgrupo = rs.idsubgrupo 
                JOIN rodizios r ON rs.idrodizio = r.idrodizio 
                JOIN modulos m ON r.idmodulo = m.idmodulo 
                WHERE 1=1";

$stmtGrupos = $conn->prepare($queryGrupos);
$stmtGrupos->execute();
$resultGrupos = $stmtGrupos->get_result();

$grupos = array();
while ($row = $resultGrupos->fetch_assoc()) {
    $nomeGrupo = $row['nome_grupo'];
    $nomeSubgrupo = $row['nome_subgrupo'];

    if (!isset($grupos[$nomeGrupo])) {
        $grupos[$nomeGrupo] = array();
    }

    if (!isset($grupos[$nomeGrupo][$nomeSubgrupo])) {
        $grupos[$nomeGrupo][$nomeSubgrupo] = array(
            'nome_modulo' => $row['nome_modulo'],
            'periodo' => $row['periodo'],
            'inicio' => $row['inicio'],
            'fim' => $row['fim'],
            'idsubgrupo' => $row['idsubgrupo']
        );
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>

<body>

    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>

    <style>
        /* Estilos Pro Max para a área de Grupos */
        .group-row {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .group-row:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        .group-row.active {
            background-color: var(--surface-2) !important;
            box-shadow: inset 4px 0 0 var(--accent);
        }

        .group-row.active .btn-expand i {
            transform: rotate(180deg);
            color: var(--accent-light);
        }

        .btn-expand i {
            transition: transform 0.3s ease;
        }

        .expansion-row {
            background-color: var(--surface-2) !important;
        }

        .expansion-content {
            animation: slideDown 0.3s ease-out forwards;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .expansion-container {
            background: linear-gradient(180deg, rgba(22, 27, 34, 0.5) 0%, rgba(13, 17, 23, 0.8) 100%);
            border-bottom: 1px solid var(--border);
        }
    </style>

    <main class="container py-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4 mt-5">
            <div>
                <h2 class="mb-1">Distribuição de Grupos</h2>
                <p class="text-muted mb-0">Gestão de subgrupos e alocação estratégica de estudantes</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm overflow-hidden mb-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Grupo Principal</th>
                                <th>Estrutura</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($grupos)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">Nenhum grupo configurado no momento.</td>
                                </tr>
                            <?php else: ?>
                                <?php $i = 0;
                                foreach ($grupos as $grupo => $subgrupos): $i++; ?>
                                    <tr onclick="toggleExpansion(<?= $i ?>)" class="group-row" id="row-<?= $i ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="btn-action me-3" style="pointer-events: none; background: var(--accent-glow); color: var(--accent-light); border-color: rgba(46, 168, 85, 0.2);">
                                                    <span class="fw-bold"><?= htmlspecialchars($grupo) ?></span>
                                                </div>
                                                <span class="fw-bold text-white fs-5">Grupo <?= htmlspecialchars($grupo) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">
                                                <i class="bi bi-diagram-3 me-1"></i> <?= count($subgrupos) ?> Subgrupos ativos
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-action btn-expand">
                                                <i class="bi bi-chevron-down"></i>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Área de Expansão Redesenhada -->
                                    <tr class="expansion-row" id="expansion-<?= $i ?>" style="display: none;">
                                        <td colspan="3" class="p-0 border-0">
                                            <div class="expansion-content expansion-container p-4">
                                                <div class="row g-3">
                                                    <?php foreach ($subgrupos as $subgrupoNome => $subgrupo): ?>
                                                        <div class="col-md-4">
                                                            <div class="subgroup-glass-card">
                                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                                    <div>
                                                                        <div class="card-label">Identificação</div>
                                                                        <div class="card-value"><?= htmlspecialchars($subgrupoNome) ?></div>
                                                                    </div>
                                                                    <div class="badge-pill-glow badge-success-glow" style="font-size: 9px;">Ativo</div>
                                                                </div>

                                                                <div class="mb-4">
                                                                    <div class="card-label">Período Acadêmico</div>
                                                                    <div class="text-white small d-flex align-items-center">
                                                                        <i class="bi bi-calendar-check me-2 text-accent-light"></i>
                                                                        <?= htmlspecialchars($subgrupo['periodo']) ?>º Semestre / Internato
                                                                    </div>
                                                                </div>

                                                                <div class="d-grid">
                                                                    <form method="POST" action="ver-alunos.php">
                                                                        <input type="hidden" name="idsubgrupo" value="<?= $subgrupo['idsubgrupo'] ?>">
                                                                        <input type="hidden" name="nome_subgrupo" value="<?= $subgrupoNome ?>">
                                                                        <button type="submit" class="btn btn-secondary w-100 btn-sm d-flex align-items-center justify-content-center gap-2 py-2">
                                                                            <i class="bi bi-people-fill"></i>
                                                                            Gerenciar Alunos
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                </div>
                </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        </table>
            </div>
        </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let openGroupId = null;

        function toggleExpansion(id) {
            const row = document.getElementById(`row-${id}`);
            const expansion = document.getElementById(`expansion-${id}`);

            if (openGroupId === id) {
                expansion.style.display = 'none';
                row.classList.remove('active');
                openGroupId = null;
                return;
            }

            if (openGroupId !== null) {
                const oldExp = document.getElementById(`expansion-${openGroupId}`);
                const oldRow = document.getElementById(`row-${openGroupId}`);
                if (oldExp) oldExp.style.display = 'none';
                if (oldRow) oldRow.classList.remove('active');
            }

            expansion.style.display = 'table-row';
            row.classList.add('active');
            openGroupId = id;
        }
    </script>
</body>

</html>

<?php
$stmtGrupos->close();
$conn->close();
?>