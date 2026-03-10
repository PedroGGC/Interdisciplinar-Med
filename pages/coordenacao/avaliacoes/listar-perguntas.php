<?php
if (!isset($conn)) {
    include_once('../../../cfg/config.php');
}

$sql = "SELECT * FROM perguntas_avaliacoes ORDER BY idpergunta ASC";
$result = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Critérios de Avaliação</h2>
            <p class="text-muted mb-0">Gestão de indicadores para avaliações clínicas</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-secondary btn-sm px-3" onclick="location.href='?page=avaliacoes'">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </button>
            <button class="btn btn-primary btn-sm px-3" onclick="location.href='?page=acoes-avaliacoes&acao=adicionar'">
                <i class="bi bi-plus-circle me-1"></i> Nova Pergunta
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Título da Pergunta</th>
                            <th>Descrição / Orientação</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-white"><?php echo htmlspecialchars($row['titulo']); ?></div>
                                        <div class="small text-muted mt-1">Escala: Insuficiente a Excelente</div>
                                    </td>
                                    <td>
                                        <div class="text-muted small" style="max-width: 500px; line-height: 1.4;">
                                            <?php echo htmlspecialchars($row['descricao']); ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="?page=acoes-avaliacoes&acao=editar&idpergunta=<?php echo $row['idpergunta']; ?>&titulo=<?php echo urlencode($row['titulo']); ?>&descricao=<?php echo urlencode($row['descricao']); ?>"
                                                class="btn-action" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?page=acoes-avaliacoes&acao=excluir&idpergunta=<?php echo $row['idpergunta']; ?>"
                                                class="btn-action text-danger"
                                                onclick="return confirm('Tem certeza que deseja excluir esta pergunta?')"
                                                title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center py-5 text-muted">Nenhum critério cadastrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
