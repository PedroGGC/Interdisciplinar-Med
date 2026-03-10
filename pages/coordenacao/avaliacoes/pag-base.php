<?php
include('../../../cfg/config.php');

// Consulta de alunos
$sql = "SELECT DISTINCT u.idusuario, u.nome, u.registro
        FROM usuarios u
        JOIN modulos_alunos ma ON u.idusuario = ma.idusuario
        WHERE u.tipo = 0
        ORDER BY u.nome ASC";
$resAlunos = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Painel de Avaliações</h2>
            <p class="text-muted mb-0">Visão geral do desempenho acadêmico e gestão de critérios</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="location.href='?page=listar-perguntas'" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-list-check me-1"></i> Configurar Critérios
            </button> 
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header border-bottom border-border p-3">
            <h6 class="mb-0 text-white"><i class="bi bi-mortarboard me-2"></i>Estudantes e Avaliações</h6>
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
                        <?php if ($resAlunos && $resAlunos->num_rows > 0): ?>
                            <?php while ($row = $resAlunos->fetch_assoc()): ?>
                                <!-- Linha Principal do Aluno -->
                                <tr onclick="toggleExpansion(<?= $row['idusuario'] ?>, '<?= addslashes($row['nome']) ?>')" class="student-row" id="row-<?= $row['idusuario'] ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="btn-action me-3" style="pointer-events: none;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <span class="fw-medium text-white"><?= htmlspecialchars($row['nome']) ?></span>
                                        </div>
                                    </td>
                                    <td><span class="font-monospace text-muted small"><?= htmlspecialchars($row['registro']) ?></span></td>
                                    <td class="text-end">
                                        <div class="btn-action btn-expand">
                                            <i class="bi bi-chevron-down"></i>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Linha Expansível (Escondida por padrão) -->
                                <tr class="expansion-row" id="expansion-<?= $row['idusuario'] ?>" style="display: none;">
                                    <td colspan="3" class="p-0 border-0">
                                        <div class="expansion-content bg-surface-2 p-4 border-bottom border-border">
                                            <div id="content-<?= $row['idusuario'] ?>">
                                                <!-- Conteúdo carregado via AJAX -->
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center py-5 text-muted">Nenhum aluno cadastrado no momento.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .student-row {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .student-row:hover {
        background-color: rgba(255,255,255,0.03) !important;
    }
    .student-row.active {
        background-color: var(--surface-2) !important;
        box-shadow: inset 4px 0 0 var(--accent);
    }
    .student-row.active .btn-expand i {
        transform: rotate(180deg);
        color: var(--accent-light);
    }
    .btn-expand i {
        transition: transform 0.3s ease;
    }
    .expansion-row {
        background-color: var(--surface-2) !important;
    }
    .expansion-content {
        animation: slideDown 0.3s ease-out forwards;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Estilo da Tabela Interna de Módulos */
    .table-inner {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .table-inner thead th {
        background: rgba(255,255,255,0.03);
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 10px 15px;
        border-bottom: 1px solid var(--border);
    }
    .table-inner td {
        padding: 12px 15px;
        border-bottom: 1px solid rgba(255,255,255,0.03);
        vertical-align: middle;
    }
    
    .period-card {
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .period-card:hover {
        border-color: var(--accent);
        background: var(--accent-glow);
        transform: translateY(-2px);
    }
</style>

<script>
let openStudentId = null;

function toggleExpansion(idusuario, nome) {
    const row = document.getElementById(`row-${idusuario}`);
    const expansion = document.getElementById(`expansion-${idusuario}`);
    const content = document.getElementById(`content-${idusuario}`);

    // Se clicar no mesmo que já está aberto, fecha
    if (openStudentId === idusuario) {
        expansion.style.display = 'none';
        row.classList.remove('active');
        openStudentId = null;
        return;
    }

    // Fecha o anterior se houver
    if (openStudentId !== null) {
        document.getElementById(`expansion-${openStudentId}`).style.display = 'none';
        document.getElementById(`row-${openStudentId}`).classList.remove('active');
    }

    // Abre o novo
    expansion.style.display = 'table-row';
    row.classList.add('active');
    openStudentId = idusuario;

    // Carrega períodos
    carregarPeriodos(idusuario, nome);
}

function carregarPeriodos(idusuario, nome) {
    const content = document.getElementById(`content-${idusuario}`);
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-accent-light" role="status"></div></div>';

    const currentPath = window.location.pathname;
    const baseDir = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
    
    fetch(`${baseDir}get-dados-aluno.php?idusuario=${idusuario}&acao=periodos`)
        .then(response => response.json())
        .then(data => {
            if (data.periodos && data.periodos.length > 0) {
                let html = `
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-calendar-event me-2 text-accent-light"></i>
                        <span class="text-white fw-bold">Selecione o Período Acadêmico</span>
                    </div>
                    <div class="row g-3">`;
                data.periodos.forEach(p => {
                    html += `
                        <div class="col-md-3">
                            <div class="period-card" onclick="carregarModulos(${idusuario}, ${p}, '${nome}')">
                                <div class="small text-muted mb-1">Carga Horária</div>
                                <div class="text-white fw-bold">${p}º Período</div>
                            </div>
                        </div>`;
                });
                html += '</div>';
                content.innerHTML = html;
            } else {
                content.innerHTML = '<p class="text-muted text-center py-3">Nenhum período vinculado.</p>';
            }
        })
        .catch(err => {
            console.error(err);
            content.innerHTML = '<p class="text-danger">Erro ao carregar dados.</p>';
        });
}

function carregarModulos(idusuario, periodo, nome) {
    const content = document.getElementById(`content-${idusuario}`);
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-accent-light" role="status"></div></div>';

    const currentPath = window.location.pathname;
    const baseDir = currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
    
    fetch(`${baseDir}get-dados-aluno.php?idusuario=${idusuario}&periodo=${periodo}&acao=modulos`)
        .then(response => response.json())
        .then(data => {
            if (data.modulos && data.modulos.length > 0) {
                let html = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-secondary me-3" onclick="carregarPeriodos(${idusuario}, '${nome}')">
                                <i class="bi bi-arrow-left"></i>
                            </button>
                            <span class="text-white fw-bold">Avaliações do ${periodo}º Período</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table-inner">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                    <th>Nota Final</th>
                                    <th>Data Avaliação</th>
                                    <th class="text-end">Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>`;
                data.modulos.forEach(m => {
                    const notaVal = m.nota !== null ? parseFloat(m.nota) : null;
                    const notaText = notaVal !== null ? notaVal.toFixed(1) : '---';
                    const dataAv = m.data_avaliacao ? formatarData(m.data_avaliacao) : '---';
                    const link = notaVal !== null ? `?page=detalhes-avaliacoes&idavaliacao=${m.idavaliacao}` : '#';
                    
                    let badgeClass = 'badge-pill-glow';
                    if (notaVal === null) badgeClass += ' text-muted';
                    else if (notaVal >= 7) badgeClass += ' badge-success-glow';
                    else if (notaVal >= 5) badgeClass += ' badge-warning-glow';
                    else badgeClass += ' badge-danger-glow';

                    html += `
                        <tr>
                            <td><span class="text-white fw-medium">${m.nome_modulo}</span></td>
                            <td><span class="${badgeClass}">${notaText}</span></td>
                            <td><span class="text-muted small">${dataAv}</span></td>
                            <td class="text-end">
                                ${notaVal !== null ? 
                                    `<a href="${link}" class="btn-action" title="Ver Detalhes"><i class="bi bi-file-earmark-text"></i></a>` : 
                                    `<button class="btn-action opacity-25" disabled title="Avaliação pendente"><i class="bi bi-file-earmark-text"></i></button>`
                                }
                            </td>
                        </tr>`;
                });
                html += '</tbody></table></div>';
                content.innerHTML = html;
            } else {
                content.innerHTML = '<p class="text-muted">Nenhum módulo encontrado.</p>';
            }
        })
        .catch(err => {
            console.error(err);
            content.innerHTML = '<p class="text-danger">Erro ao carregar módulos.</p>';
        });
}

function formatarData(dataStr) {
    if (!dataStr) return '---';
    const partes = dataStr.split('-');
    if (partes.length !== 3) return dataStr;
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}
</script>
