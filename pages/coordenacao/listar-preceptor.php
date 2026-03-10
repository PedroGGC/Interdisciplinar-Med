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
    <title>Lista de Preceptores</title>
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
                    <h2 class="mb-1">Lista de Preceptores</h2>
                    <p class="text-muted mb-0">Gestão de profissionais e vínculos hospitalares</p>
                </div>
                <button class="btn btn-primary" onclick="location.href='associar-preceptor.php'"><i class="bi bi-gear me-2"></i>Gerenciar Vínculos</button>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Preceptor</th>
                                    <th>CRM / Registro</th>
                                    <th>Status</th>
                                    <th>Unidade Vinculada</th>
                                    <th>Contato</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "
                                    SELECT u.*, un.nome_unidade 
                                    FROM usuarios u
                                    LEFT JOIN preceptores_unidades pu ON u.idusuario = pu.idusuario
                                    LEFT JOIN unidades un ON pu.idunidade = un.idunidade
                                    WHERE u.tipo = 1
                                    ORDER BY u.nome ASC
                                ";
                                $res = $conn->query($sql);

                                if (!$res) {
                                    die("Erro na consulta: " . $conn->error);
                                }

                                if ($res->num_rows > 0) {
                                    while ($row = $res->fetch_object()) {
                                        $statusClass = $row->ativo == '1' ? 'badge-success-glow' : 'badge-warning-glow';
                                        $statusText = $row->ativo == '1' ? 'Ativo' : 'Inativo';
                                        $unidade = $row->nome_unidade ?? 'Não Associado';
                                        
                                        echo "<tr>";
                                        echo "<td>
                                                <div class='d-flex align-items-center'>
                                                    <div class='btn-action me-3' style='pointer-events: none;'>
                                                        <i class='bi bi-person-badge'></i>
                                                    </div>
                                                    <span class='fw-medium text-white'>" . htmlspecialchars($row->nome) . "</span>
                                                </div>
                                              </td>";
                                        echo "<td><span class='font-monospace text-muted'>" . htmlspecialchars($row->registro) . "</span></td>";
                                        echo "<td><span class='badge-pill-glow $statusClass'>$statusText</span></td>";
                                        echo "<td><span class='badge-pill-glow " . ($row->nome_unidade ? 'badge-info-glow' : '') . "' style='" . (!$row->nome_unidade ? 'background:rgba(255,255,255,0.05); color:var(--text-muted);' : '') . "'>" . htmlspecialchars($unidade) . "</span></td>";
                                        echo "<td>
                                                <div class='small text-muted mb-1'><i class='bi bi-envelope me-1'></i> " . htmlspecialchars($row->email) . "</div>
                                                <div class='small text-muted'><i class='bi bi-telephone me-1'></i> " . htmlspecialchars($row->telefone) . "</div>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center py-5 text-muted'>Nenhum preceptor encontrado.</td></tr>";
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
