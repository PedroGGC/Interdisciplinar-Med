<?php
session_start();
if (empty($_SESSION["login"])) {
    echo "<script>location.href='../../../index.php';</script>";
    exit();
}
require_once('../../../cfg/config.php');

$idsubgrupo = filter_input(INPUT_POST, 'idsubgrupo', FILTER_VALIDATE_INT) ?: filter_input(INPUT_GET, 'idsubgrupo', FILTER_VALIDATE_INT);
$nomeSubgrupo = filter_input(INPUT_POST, 'nome_subgrupo', FILTER_CALLBACK, ['options' => 'trim']) ?: filter_input(INPUT_GET, 'nome_subgrupo', FILTER_CALLBACK, ['options' => 'trim']);

if (!$idsubgrupo) { die("ID de subgrupo inválido."); }

// Adicionar alunos ao subgrupo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alunos'])) {
    $alunos = filter_input(INPUT_POST, 'alunos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare("INSERT INTO alunos_subgrupos (idusuario, idsubgrupo) VALUES (?, ?)");
        foreach ($alunos as $idaluno) {
            $stmt->bind_param("ii", $idaluno, $idsubgrupo);
            $stmt->execute();
        }
        $conn->commit();
        $stmt->close();
        header("Location: ver-alunos.php?idsubgrupo={$idsubgrupo}&nome_subgrupo=" . urlencode($nomeSubgrupo));
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Erro ao adicionar alunos.");
    }
}

// Remover aluno do subgrupo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_aluno'])) {
    $idaluno = filter_input(INPUT_POST, 'idaluno', FILTER_VALIDATE_INT);
    try {
        $stmt = $conn->prepare("DELETE FROM alunos_subgrupos WHERE idusuario = ? AND idsubgrupo = ?");
        $stmt->bind_param("ii", $idaluno, $idsubgrupo);
        $stmt->execute();
        $stmt->close();
        header("Location: ver-alunos.php?idsubgrupo={$idsubgrupo}&nome_subgrupo=" . urlencode($nomeSubgrupo));
        exit();
    } catch (Exception $e) {
        die("Erro ao remover aluno.");
    }
}

$queryAlunos = "SELECT u.idusuario, u.nome, u.registro FROM usuarios u JOIN alunos_subgrupos asg ON u.idusuario = asg.idusuario WHERE asg.idsubgrupo = ? ORDER BY u.nome ASC";
$queryAlunosSemSubgrupo = "SELECT DISTINCT u.idusuario, u.nome, u.registro FROM usuarios u JOIN modulos_alunos ma ON u.idusuario = ma.idusuario WHERE u.idusuario NOT IN (SELECT idusuario FROM alunos_subgrupos WHERE idsubgrupo = ?) ORDER BY u.nome ASC";

$stmtAlunos = $conn->prepare($queryAlunos);
$stmtAlunos->bind_param("i", $idsubgrupo);
$stmtAlunos->execute();
$resultAlunos = $stmtAlunos->get_result();

$stmtAlunosSemSubgrupo = $conn->prepare($queryAlunosSemSubgrupo);
$stmtAlunosSemSubgrupo->bind_param("i", $idsubgrupo);
$stmtAlunosSemSubgrupo->execute();
$resultAlunosSemSubgrupo = $stmtAlunosSemSubgrupo->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrantes do Subgrupo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                    <h2 class="mb-1">Subgrupo: <?= htmlspecialchars($nomeSubgrupo) ?></h2>
                    <p class="text-muted mb-0">Gestão de integrantes e alocação de novos estudantes</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="grupos.php" class="btn btn-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
                    <?php if ($resultAlunosSemSubgrupo->num_rows > 0): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#alunosSemSubgrupoModal">
                            <i class="bi bi-person-plus me-1"></i> Adicionar Alunos
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden mb-5">
                <div class="card-header border-bottom border-border p-3">
                    <h6 class="mb-0 text-white"><i class="bi bi-people me-2"></i>Alunos no Subgrupo</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Estudante</th>
                                    <th>RA / Registro</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultAlunos->num_rows > 0): ?>
                                    <?php while ($aluno = $resultAlunos->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="btn-action me-3" style="pointer-events: none;">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <span class="fw-medium text-white"><?= htmlspecialchars($aluno['nome']) ?></span>
                                                </div>
                                            </td>
                                            <td><span class="font-monospace text-muted small"><?= htmlspecialchars($aluno['registro']) ?></span></td>
                                            <td class="text-end">
                                                <form method="post" class="d-inline" onsubmit="return confirm('Remover este aluno?');">
                                                    <input type="hidden" name="idsubgrupo" value="<?= $idsubgrupo ?>">
                                                    <input type="hidden" name="idaluno" value="<?= $aluno['idusuario'] ?>">
                                                    <input type="hidden" name="nome_subgrupo" value="<?= htmlspecialchars($nomeSubgrupo) ?>">
                                                    <button type="submit" name="remove_aluno" class="btn-action text-danger" title="Remover do Grupo">
                                                        <i class="bi bi-person-dash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-5 text-muted small">Nenhum aluno vinculado a este subgrupo.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Alunos sem Subgrupo -->
        <div class="modal fade" id="alunosSemSubgrupoModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="background: var(--surface);">
                    <div class="modal-header border-border">
                        <h5 class="modal-title text-white">Vincular Novos Alunos</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post">
                        <div class="modal-body p-0" style="max-height: 400px; overflow-y: auto;">
                            <input type="hidden" name="idsubgrupo" value="<?= $idsubgrupo ?>">
                            <input type="hidden" name="nome_subgrupo" value="<?= htmlspecialchars($nomeSubgrupo) ?>">
                            <table class="table-modern table-sm mb-0">
                                <thead class="sticky-top" style="background: var(--surface-2);">
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th>RA</th>
                                        <th>Nome do Aluno</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($aluno = $resultAlunosSemSubgrupo->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center">
                                            <div class="form-check m-0 d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="alunos[]" value="<?= $aluno['idusuario'] ?>" id="aluno<?= $aluno['idusuario'] ?>">
                                            </div>
                                        </td>
                                        <td><span class="font-monospace text-muted small"><?= htmlspecialchars($aluno['registro']) ?></span></td>
                                        <td><label class="form-check-label text-white small" for="aluno<?= $aluno['idusuario'] ?>"><?= htmlspecialchars($aluno['nome']) ?></label></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer border-border p-3">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-sm px-4">Confirmar Vínculo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="footer-home rounded-0"><div class="card-body"></div></div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
