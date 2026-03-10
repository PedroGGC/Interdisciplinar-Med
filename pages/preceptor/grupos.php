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
    <title>Grupos do Preceptor</title>
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
                <h2 class="mb-1">Meus Subgrupos</h2>
                <p class="text-muted mb-0">Gerencie e visualize os integrantes dos seus grupos de internato</p>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <?php
                    $idpreceptor = $_SESSION['idusuario'] ?? null;
                    if ($idpreceptor) {
                        $subgrupos = [];
                        $sql = "SELECT DISTINCT sg.idsubgrupo, sg.nome_subgrupo, g.idgrupo, g.nome_grupo
                                FROM subgrupos sg
                                JOIN grupos g ON sg.idgrupo = g.idgrupo
                                JOIN horarios h ON sg.idsubgrupo = h.idsubgrupo
                                WHERE h.idpreceptor = ?
                                ORDER BY g.nome_grupo, sg.nome_subgrupo";
                        if ($stmt = $conn->prepare($sql)) {
                            $stmt->bind_param("i", $idpreceptor);
                            if ($stmt->execute()) {
                                $res = $stmt->get_result();
                                while ($row = $res->fetch_assoc()) {
                                    // contagem de alunos no subgrupo
                                    $count = 0;
                                    $stmtC = $conn->prepare("SELECT COUNT(*) FROM alunos_subgrupos WHERE idsubgrupo = ?");
                                    $stmtC->bind_param("i", $row['idsubgrupo']);
                                    $stmtC->execute();
                                    $stmtC->bind_result($count);
                                    $stmtC->fetch();
                                    $stmtC->close();

                                    $subgrupos[] = [
                                        'idsubgrupo' => $row['idsubgrupo'],
                                        'nomeSubgrupo' => $row['nome_subgrupo'],
                                        'idgrupo' => $row['idgrupo'],
                                        'nomeGrupo' => $row['nome_grupo'],
                                        'totalAlunos' => $count
                                    ];
                                }
                            }
                            $stmt->close();
                        }
                    ?>
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Grupo Principal</th>
                                    <th>Subgrupo</th>
                                    <th>Integrantes</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($subgrupos)) {
                                    foreach ($subgrupos as $sg) { ?>
                                        <tr>
                                            <td>
                                                <span class="badge-pill-glow badge-info-glow"><?= htmlspecialchars($sg['nomeGrupo']) ?></span>
                                            </td>
                                            <td>
                                                <span class="fw-medium text-white"><?= htmlspecialchars($sg['nomeSubgrupo']) ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center text-muted">
                                                    <i class="bi bi-people me-2"></i>
                                                    <span><?= htmlspecialchars($sg['totalAlunos']) ?> Alunos</span>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <a href="grupos-alunos.php?idsubgrupo=<?= urlencode($sg['idsubgrupo']) ?>" class="btn btn-primary btn-sm px-3">
                                                    <i class="bi bi-eye me-1"></i> Abrir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } 
                                } else { ?>
                                    <tr><td colspan="4" class="text-center py-5 text-muted">Nenhum subgrupo vinculado ao seu perfil.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } else { echo '<div class="p-4"><div class="alert alert-danger mb-0">Usuário não autenticado.</div></div>'; } ?>
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