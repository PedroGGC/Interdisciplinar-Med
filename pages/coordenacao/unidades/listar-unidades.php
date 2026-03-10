<div class="container mt-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Unidades Hospitalares</h2>
            <p class="text-muted mb-0">Gestão de campos de estágio e infraestrutura clínica</p>
        </div>
        <button onclick="location.href='?page=registrar-unidades'" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nova Unidade
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Unidade / Hospital</th>
                            <th>Endereço e Localização</th>
                            <th class="text-end">Gerenciamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM unidades ORDER BY nome_unidade ASC";
                        $res = $conn->query($sql);

                        if (!$res) {
                            die("Erro na consulta: " . $conn->error);
                        }

                        if ($res->num_rows > 0) {
                            while ($row = $res->fetch_object()) {
                                echo "<tr>";
                                echo "<td>
                                        <div class='d-flex align-items-center'>
                                            <div class='btn-action me-3' style='pointer-events: none;'>
                                                <i class='bi bi-building-fill-check'></i>
                                            </div>
                                            <span class='fw-medium text-white'>" . htmlspecialchars($row->nome_unidade) . "</span>
                                        </div>
                                      </td>";
                                echo "<td>
                                        <div class='small text-muted'>
                                            <i class='bi bi-geo-alt me-1'></i> " . htmlspecialchars($row->endereco_unidade) . "
                                        </div>
                                      </td>";
                                echo "<td class='text-end'>
                                        <div class='d-flex justify-content-end gap-2'>
                                            <button onclick=\"location.href='?page=visualizar-unidade&idunidade=" . $row->idunidade . "';\" class='btn-action' title='Visualizar Vínculos'><i class='bi bi-eye'></i></button>
                                            <button onclick=\"location.href='?page=editar-unidades&idunidade=" . $row->idunidade . "';\" class='btn-action' title='Editar'><i class='bi bi-pencil'></i></button>
                                            <button onclick=\"if(confirm('Tem certeza que deseja excluir?')) location.href='acoes-unidades.php?acao=excluir&idunidade=" . $row->idunidade . "';\" class='btn-action text-danger' title='Excluir'><i class='bi bi-trash'></i></button>
                                        </div>
                                     </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center py-5 text-muted'>Nenhuma unidade cadastrada.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>