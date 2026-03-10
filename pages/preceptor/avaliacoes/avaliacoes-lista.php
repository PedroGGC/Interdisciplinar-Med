<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="mb-1">Avaliações</h2>
        <p class="text-muted mb-0">Selecione um aluno para realizar ou visualizar avaliações</p>
    </div>
</div>

<div class="table-responsive">
    <table class="table-modern">
        <thead>
            <tr>
                <th>Nome do Aluno</th>
                <th>Registro / RA</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $idpreceptor = $_SESSION['idusuario'];
            $sql = "SELECT DISTINCT u.* 
                    FROM usuarios u 
                    JOIN alunos_subgrupos als ON u.idusuario = als.idusuario
                    JOIN horarios h ON als.idsubgrupo = h.idsubgrupo
                    WHERE u.tipo = 0 AND h.idpreceptor = ?
                    ORDER BY u.nome ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $idpreceptor);
            $stmt->execute();
            $res = $stmt->get_result();

            if (!$res) {
                die("Erro na consulta: " . $conn->error);
            }

            if ($res->num_rows > 0) {
                while ($row = $res->fetch_object()) {
                    // Verifica se já foi avaliado pelo preceptor logado
                    $sql_avaliacao = "SELECT COUNT(*) AS total_avaliacoes FROM avaliacoes WHERE idaluno = ? AND idpreceptor = ?";
                    $stmt_av = $conn->prepare($sql_avaliacao);
                    $stmt_av->bind_param("ii", $row->idusuario, $idpreceptor);
                    $stmt_av->execute();
                    $res_av = $stmt_av->get_result();
                    $row_av = $res_av->fetch_object();
                    $avaliacao_realizada = $row_av->total_avaliacoes > 0;

                    echo "<tr>";
                    echo "<td>
                            <div class='d-flex align-items-center'>
                                <div class='btn-action me-3' style='pointer-events: none;'>
                                    <i class='bi bi-person'></i>
                                </div>
                                <span class='fw-medium text-white'>" . htmlspecialchars($row->nome) . "</span>
                            </div>
                          </td>";
                    echo "<td><span class='font-monospace text-muted'>" . htmlspecialchars($row->registro) . "</span></td>";
                    echo "<td class='text-end'>";
                    if ($avaliacao_realizada) {
                        echo "<span class='badge-pill-glow badge-success-glow'><i class='bi bi-check-circle me-1'></i> Avaliado</span>";
                    } else {
                        echo "<a href='?page=realizar-avaliacao&idaluno=" . $row->idusuario . "' class='btn btn-primary btn-sm px-3'>
                                <i class='bi bi-pencil-square me-1'></i> Avaliar
                              </a>";
                    }
                    echo "</td>";
                    echo "</tr>";
                    $stmt_av->close();
                }
            } else {
                echo "<tr><td colspan='3' class='text-center py-5 text-muted'>Nenhum aluno encontrado para avaliação.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
