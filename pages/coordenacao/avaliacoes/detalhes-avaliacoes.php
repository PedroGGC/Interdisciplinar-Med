<?php
require_once '../../../cfg/config.php';

$idavaliacao = isset($_GET['idavaliacao']) ? intval($_GET['idavaliacao']) : null;

if (!$idavaliacao) {
    echo "<script>alert('Erro: Avaliação não encontrada.'); location.href='?page=listar-avaliacoes';</script>";
    exit();
}

$query_avaliacao = "
    SELECT 
        aluno.nome AS aluno_nome,
        preceptor.nome AS preceptor_nome,
        a.data_avaliacao,
        a.nota,
        m.nome_modulo AS modulo_nome,
        m.idmodulo AS idmodulo
    FROM 
        avaliacoes AS a
    JOIN usuarios AS aluno ON a.idaluno = aluno.idusuario
    JOIN usuarios AS preceptor ON a.idpreceptor = preceptor.idusuario
    JOIN modulos AS m ON a.idmodulo = m.idmodulo 
    WHERE 
        a.idavaliacao = ?
";
$stmt_avaliacao = $conn->prepare($query_avaliacao);
$stmt_avaliacao->bind_param("i", $idavaliacao);
$stmt_avaliacao->execute();
$result_avaliacao = $stmt_avaliacao->get_result();
$avaliacao = $result_avaliacao->fetch_assoc();

if (!$avaliacao) {
    echo "<script>alert('Erro: Avaliação não encontrada.'); location.href='?page=listar-avaliacoes';</script>";
    exit();
}

$query_respostas = "
    SELECT 
        p.titulo AS titulo_pergunta,
        p.descricao AS pergunta,
        ar.resposta
    FROM 
        avaliacoes_respostas AS ar
    JOIN perguntas_avaliacoes AS p ON ar.idpergunta = p.idpergunta
    WHERE 
        ar.idavaliacao = ?
";
$stmt_respostas = $conn->prepare($query_respostas);
$stmt_respostas->bind_param("i", $idavaliacao);
$stmt_respostas->execute();
$result_respostas = $stmt_respostas->get_result();

$query_rodizio = "
    SELECT inicio, fim FROM rodizios WHERE idmodulo = ? LIMIT 1
";
$stmt_rodizio = $conn->prepare($query_rodizio);
$stmt_rodizio->bind_param("i", $avaliacao['idmodulo']);
$stmt_rodizio->execute();
$result_rodizio = $stmt_rodizio->get_result();
$rodizio = $result_rodizio->fetch_assoc();

$query_unidade = "
    SELECT u.nome_unidade
    FROM unidades u
    JOIN unidades_modulos um ON u.idunidade = um.idunidade
    WHERE um.idmodulo = ? LIMIT 1
";
$stmt_unidade = $conn->prepare($query_unidade);
$stmt_unidade->bind_param("i", $avaliacao['idmodulo']);
$stmt_unidade->execute();
$result_unidade = $stmt_unidade->get_result();
$unidade = $result_unidade->fetch_assoc();

function tipo_resposta($nota) {
    switch ($nota) {
        case 4: return ["text" => "Insuficiente", "class" => "badge-danger-glow"];
        case 6: return ["text" => "Regular", "class" => "badge-warning-glow"];
        case 8: return ["text" => "Bom", "class" => "badge-info-glow"];
        case 10: return ["text" => "Excelente", "class" => "badge-success-glow"];
        default: return ["text" => "N/A", "class" => ""];
    }
}
?>

<div class="container mt-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Detalhamento de Avaliação</h2>
            <p class="text-muted mb-0">Relatório técnico do desempenho observado em campo</p>
        </div>
        <button class="btn btn-secondary px-4" onclick="location.href='?page=avaliacoes'">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </button>
    </div>

    <div class="row g-4 mb-5">
        <!-- Sumário da Avaliação -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <div class="btn-action mx-auto mb-3" style="width: 60px; height: 60px; pointer-events: none;">
                        <i class="bi bi-award fs-3"></i>
                    </div>
                    <h1 class="display-4 fw-bold text-accent-light mb-0"><?= number_format($avaliacao['nota'], 1) ?></h1>
                    <p class="text-muted text-uppercase small fw-bold letter-spacing-1">Nota Final Consolidada</p>
                    <hr class="border-border opacity-25">
                    <div class="text-start mt-4">
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Estudante</label>
                            <span class="text-white fw-medium"><?= htmlspecialchars($avaliacao['aluno_nome']) ?></span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Preceptor Responsável</label>
                            <span class="text-white fw-medium"><?= htmlspecialchars($avaliacao['preceptor_nome']) ?></span>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted text-uppercase fw-bold d-block mb-1">Data e Hora</label>
                            <span class="text-white small"><?= date('d/m/Y \à\s H:i', strtotime($avaliacao['data_avaliacao'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalhes Contextuais -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="text-white mb-4"><i class="bi bi-info-circle me-2"></i>Contexto Clínico</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-surface-2 border border-border">
                                <label class="small text-muted text-uppercase fw-bold d-block mb-1">Módulo de Ensino</label>
                                <span class="text-white"><?= htmlspecialchars($avaliacao['modulo_nome']) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-surface-2 border border-border">
                                <label class="small text-muted text-uppercase fw-bold d-block mb-1">Unidade Hospitalar</label>
                                <span class="text-white"><?= htmlspecialchars($unidade['nome_unidade'] ?? '---') ?></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="p-3 rounded bg-surface-2 border border-border">
                                <label class="small text-muted text-uppercase fw-bold d-block mb-1">Período de Rodízio</label>
                                <span class="text-white">
                                    <?php if ($rodizio): ?>
                                        <i class="bi bi-calendar-range me-2"></i>
                                        <?= date('d/m/Y', strtotime($rodizio['inicio'])) ?> até <?= date('d/m/Y', strtotime($rodizio['fim'])) ?>
                                    <?php else: ?>
                                        Datas não definidas
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Perguntas e Respostas -->
        <div class="col-12 mt-4">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header border-bottom border-border p-3">
                    <h6 class="mb-0 text-white"><i class="bi bi-journal-check me-2"></i>Critérios e Conceitos Atribuídos</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th style="width: 60%;">Critério de Avaliação</th>
                                    <th class="text-end">Conceito</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($resposta = $result_respostas->fetch_assoc()): 
                                    $conceito = tipo_resposta($resposta['resposta']);
                                ?>
                                    <tr>
                                        <td>
                                            <div class="fw-medium text-white mb-1"><?= htmlspecialchars($resposta['titulo_pergunta']) ?></div>
                                            <p class="text-muted small mb-0" style="line-height: 1.4;"><?= htmlspecialchars($resposta['pergunta']) ?></p>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge-pill-glow <?= $conceito['class'] ?>">
                                                <?= $conceito['text'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
