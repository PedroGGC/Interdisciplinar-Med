<div class="container mt-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Módulos de Ensino</h2>
            <p class="text-muted mb-0">Gestão de currículo e distribuição por períodos</p>
        </div>
        <div class="d-flex gap-2">
            <a href="?page=importar-modulos" class="btn btn-secondary">
                <i class="bi bi-upload me-2"></i>Importar CSV
            </a>
            <button onclick="location.href='?page=registrar-modulos'" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Novo Módulo
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Nome do Módulo</th>
                            <th>Período</th>
                            <th>Estudantes</th>
                            <th class="text-end">Gerenciamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM modulos ORDER BY CAST(periodo AS UNSIGNED) ASC, nome_modulo ASC";
                        $res = $conn->query($sql);

                        if (!$res) {
                            die("Erro na consulta: " . $conn->error);
                        }

                        if ($res->num_rows > 0) {
                            while ($row = $res->fetch_object()) {
                                $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM modulos_alunos WHERE idmodulo = ?");
                                $stmtCount->bind_param("i", $row->idmodulo);
                                $stmtCount->execute();
                                $resultCount = $stmtCount->get_result();
                                $countRow = $resultCount->fetch_assoc();
                                $totalAlunos = $countRow['total'];

                                echo "<tr>";
                                echo "<td>
                                        <div class='d-flex align-items-center'>
                                            <div class='btn-action me-3' style='pointer-events: none;'>
                                                <i class='bi bi-journal-bookmark-fill'></i>
                                            </div>
                                            <span class='fw-medium text-white'>" . htmlspecialchars($row->nome_modulo) . "</span>
                                        </div>
                                      </td>";
                                echo "<td><span class='badge-pill-glow badge-info-glow'>" . htmlspecialchars($row->periodo) . "º Período</span></td>";
                                echo "<td>
                                        <div class='d-flex align-items-center text-muted small'>
                                            <i class='bi bi-people me-2'></i>
                                            <span>" . htmlspecialchars($totalAlunos) . " Alunos</span>
                                        </div>
                                      </td>"; 
                                echo "<td class='text-end'>
                                        <div class='d-flex justify-content-end gap-2'>
                                            <button onclick=\"location.href='?page=visualizar-modulos&idmodulo=" . $row->idmodulo . "';\" class='btn-action' title='Vincular Alunos'><i class='bi bi-person-plus'></i></button>
                                            <button onclick=\"location.href='?page=editar-modulos&idmodulo=" . $row->idmodulo . "';\" class='btn-action' title='Editar'><i class='bi bi-pencil'></i></button>
                                            <button onclick=\"if(confirm('Tem certeza que deseja excluir?')) location.href='acoes-modulos.php?acao=excluir&idmodulo=" . $row->idmodulo . "';\" class='btn-action text-danger' title='Excluir'><i class='bi bi-trash'></i></button>
                                        </div>
                                     </td>";
                                echo "</tr>";

                                $stmtCount->close();
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-5 text-muted'>Nenhum módulo registrado.</td></tr>"; 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>