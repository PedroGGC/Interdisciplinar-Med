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
    <title>Lista de Alunos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <?php
    function ativoTexto($ativo) {
        switch ($ativo) {
            case '0':
                return 'Inativo';
            case '1':
                return 'Ativo';
            default:
                return 'Não definido';
        }
    }
    ?>
    <header>
        <?php include('../../includes/navbar.php'); ?>
        <?php include('../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main>
        <div class="container mt-4">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Lista de Alunos</h2>
                    <p class="text-muted mb-0">Visualização completa da base de alunos cadastrados</p>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>RA / Registro</th>
                                    <th>Status</th>
                                    <th>Contato</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM usuarios WHERE tipo = 0 ORDER BY nome ASC";
                                $res = $conn->query($sql);

                                if (!$res) {
                                    die("Erro na consulta: " . $conn->error);
                                }

                                if ($res->num_rows > 0) {
                                    while ($row = $res->fetch_object()) {
                                        $statusClass = $row->ativo == '1' ? 'badge-success-glow' : 'badge-warning-glow';
                                        $statusText = $row->ativo == '1' ? 'Ativo' : 'Inativo';
                                        
                                        echo "<tr>";
                                        echo "<td>
                                                <div class='d-flex align-items-center'>
                                                    <div class='btn-action me-3' style='pointer-events: none;'>
                                                        <i class='bi bi-person'></i>
                                                    </div>
                                                    <span class='fw-medium'>" . htmlspecialchars($row->nome) . "</span>
                                                </div>
                                              </td>";
                                        echo "<td><span class='font-monospace text-muted'>" . htmlspecialchars($row->registro) . "</span></td>";
                                        echo "<td><span class='badge-pill-glow $statusClass'>$statusText</span></td>"; 
                                        echo "<td>
                                                <div class='small text-muted mb-1'><i class='bi bi-envelope me-1'></i> " . htmlspecialchars($row->email) . "</div>
                                                <div class='small text-muted'><i class='bi bi-telephone me-1'></i> " . htmlspecialchars($row->telefone) . "</div>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center py-5 text-muted'>Nenhum aluno encontrado.</td></tr>";
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
