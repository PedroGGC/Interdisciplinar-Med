<?php
include('../../../cfg/config.php');

$idaluno = isset($_GET['idaluno']) ? intval($_GET['idaluno']) : null;
$idpreceptor = isset($_SESSION['idusuario']) ? $_SESSION['idusuario'] : null;

if (!$idaluno) {
    echo "<script>alert('ID do aluno não informado ou inválido.'); location.href='avaliacoes.php';</script>";
    exit();
}

// Busca módulos do aluno
$queryModulos = "
    SELECT m.idmodulo, m.nome_modulo 
    FROM modulos m
    JOIN modulos_alunos ma ON m.idmodulo = ma.idmodulo
    WHERE ma.idusuario = ?";
$stmtModulos = $conn->prepare($queryModulos);
$stmtModulos->bind_param("i", $idaluno);
$stmtModulos->execute();
$resultModulos = $stmtModulos->get_result();

// Busca critérios de avaliação
$queryPerguntas = "SELECT idpergunta, titulo, descricao FROM perguntas_avaliacoes";
$resultPerguntas = $conn->query($queryPerguntas);
?>

<style>
    .compact-eval-row {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 30px;
    }
    .compact-eval-row:hover {
        background: var(--surface-2);
    }
    .compact-eval-row:last-child {
        border-bottom: none;
    }
    .score-selector {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .score-selector .btn {
        min-width: 100px;
        height: 38px;
        padding: 0 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
        border-radius: 8px;
        border-width: 2px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .question-info {
        flex-grow: 1;
    }
    .question-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
    }
    .question-desc {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 0;
        max-width: 600px;
    }
    
    /* Estilos de seleção com cores semânticas */
    .btn-check:checked + .btn-outline-danger { background-color: var(--danger); color: white; border-color: var(--danger); box-shadow: 0 0 12px rgba(248, 81, 73, 0.4); }
    .btn-check:checked + .btn-outline-warning { background-color: #eba235; color: white; border-color: #eba235; box-shadow: 0 0 12px rgba(235, 162, 53, 0.4); }
    .btn-check:checked + .btn-outline-info { background-color: #38b6ff; color: white; border-color: #38b6ff; box-shadow: 0 0 12px rgba(56, 182, 255, 0.4); }
    .btn-check:checked + .btn-outline-success { background-color: var(--accent); color: white; border-color: var(--accent); box-shadow: 0 0 12px var(--accent-glow); }

    @media (max-width: 992px) {
        .compact-eval-row { flex-direction: column; align-items: flex-start; gap: 15px; }
        .score-selector { width: 100%; justify-content: space-between; }
        .score-selector .btn { min-width: auto; flex: 1; font-size: 10px; padding: 0 5px; }
    }
</style>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Avaliação de Desempenho</h2>
        <p class="text-muted mb-0">Atribua os conceitos baseados na observação clínica</p>
    </div>
    <a href="avaliacoes.php" class="btn btn-secondary btn-sm px-3"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
</div>

<form method="post" action="processar-avaliacao.php?<?php echo 'idaluno=' . $idaluno . '&idpreceptor=' . $idpreceptor; ?>">
    <!-- Seleção de Módulo -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <label for="modulo" class="me-3 small text-muted text-nowrap fw-bold">MÓDULO ATUAL:</label>
                        <select name="modulo" id="modulo" class="form-select form-select-sm" required>
                            <?php if ($resultModulos->num_rows > 0): ?>
                                <?php while ($rowModulo = $resultModulos->fetch_assoc()): ?>
                                    <option value="<?php echo $rowModulo['idmodulo']; ?>">
                                        <?php echo htmlspecialchars($rowModulo['nome_modulo']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="">Nenhum módulo vinculado</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Critérios -->
    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-body p-0">
            <?php if ($resultPerguntas && $resultPerguntas->num_rows > 0): ?>
                <?php while ($pergunta = $resultPerguntas->fetch_assoc()): 
                    $pid = $pergunta['idpergunta'];
                ?>
                    <div class="compact-eval-row">
                        <div class="question-info">
                            <h6 class="question-title"><?php echo htmlspecialchars($pergunta['titulo']); ?></h6>
                            <p class="question-desc">
                                <?php echo htmlspecialchars($pergunta['descricao']); ?>
                            </p>
                        </div>
                        
                        <div class="score-selector">
                            <input type="radio" class="btn-check" name="pergunta_<?php echo $pid; ?>" id="q<?php echo $pid; ?>_4" value="4" required>
                            <label class="btn btn-outline-danger" for="q<?php echo $pid; ?>_4">Insuficiente</label>

                            <input type="radio" class="btn-check" name="pergunta_<?php echo $pid; ?>" id="q<?php echo $pid; ?>_6" value="6" required>
                            <label class="btn btn-outline-warning" for="q<?php echo $pid; ?>_6">Regular</label>

                            <input type="radio" class="btn-check" name="pergunta_<?php echo $pid; ?>" id="q<?php echo $pid; ?>_8" value="8" required>
                            <label class="btn btn-outline-info" for="q<?php echo $pid; ?>_8">Bom</label>

                            <input type="radio" class="btn-check" name="pergunta_<?php echo $pid; ?>" id="q<?php echo $pid; ?>_10" value="10" required>
                            <label class="btn btn-outline-success" for="q<?php echo $pid; ?>_10">Excelente</label>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-clipboard-x fs-2 mb-2 d-block"></i>
                    Nenhum critério disponível.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ações -->
    <div class="d-flex justify-content-end gap-2 mb-5">
        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
            SALVAR AVALIAÇÃO <i class="bi bi-check-all ms-2"></i>
        </button>
    </div>
</form>
