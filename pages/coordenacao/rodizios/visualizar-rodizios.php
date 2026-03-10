<?php
require '../../../cfg/config.php';

$query = "
SELECT 
    r.idrodizio, 
    r.inicio, 
    r.fim, 
    m.nome_modulo AS modulo, 
    IFNULL(g.nome_grupo, 'não atribuído') AS grupo
FROM 
    rodizios r
LEFT JOIN 
    rodizios_subgrupos rs ON r.idrodizio = rs.idrodizio
LEFT JOIN 
    subgrupos sg ON rs.idsubgrupo = sg.idsubgrupo
LEFT JOIN 
    grupos g ON sg.idgrupo = g.idgrupo
JOIN 
    modulos m ON r.idmodulo = m.idmodulo
GROUP BY 
    r.idrodizio, m.nome_modulo, g.nome_grupo
ORDER BY 
    r.inicio, m.nome_modulo, g.nome_grupo
";

$result = $conn->query($query);

$rodizios_formatados = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $inicio = date('d/m/Y', strtotime($row['inicio']));
        $fim = date('d/m/Y', strtotime($row['fim']));
        $modulo = $row['modulo'];
        $grupo = $row['grupo'] ?: 'não atribuído';  // Define "não atribuído" quando grupo não existe

        $periodo_key = $inicio . ' até ' . $fim;

        if (!isset($rodizios_formatados[$periodo_key])) {
            $rodizios_formatados[$periodo_key] = [
                'inicio' => $inicio,
                'fim' => $fim,
                'modulos' => []
            ];
        }

        $rodizios_formatados[$periodo_key]['modulos'][] = [
            'modulo' => $modulo,
            'grupo' => $grupo
        ];
    }
}

$query_modulos = "SELECT DISTINCT nome_modulo FROM modulos ORDER BY nome_modulo";
$result_modulos = $conn->query($query_modulos);

$filtro_modulo = isset($_POST['filtro']) ? $_POST['filtro'] : '';
?>

<div class="container mt-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Rodízios Ativos</h2>
            <p class="text-muted mb-0">Cronograma de rotações clínicas e distribuição de grupos</p>
        </div>
        <button onclick="location.href='?page=gerar-rodizios'" class="btn btn-primary px-4">
            <i class="bi bi-plus-circle me-1"></i> Gerar Rodízios
        </button>
    </div>

    <?php if (empty($rodizios_formatados)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <div class="btn-action mx-auto mb-3" style="width: 64px; height: 64px; pointer-events: none; background: rgba(255,255,255,0.05);">
                    <i class="bi bi-calendar-x fs-2"></i>
                </div>
                <h5 class="text-white">Nenhum rodízio encontrado</h5>
                <p class="text-muted mb-0">Inicie a geração de rodízios para visualizar o cronograma.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($rodizios_formatados as $periodo => $info): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-header border-bottom border-border p-3 bg-surface-2 d-flex align-items-center">
                            <div class="btn-action me-3" style="pointer-events: none; background: rgba(46, 168, 85, 0.1); color: var(--accent-light);">
                                <i class="bi bi-calendar-range"></i>
                            </div>
                            <h6 class="mb-0 text-white">Período: <span class="text-accent-light fw-bold ms-1 font-monospace" style="font-variant-numeric: tabular-nums; letter-spacing: 0px;"><?= $periodo ?></span></h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table-modern mb-0">
                                    <thead>
                                        <tr>
                                            <th>Módulo Clínico</th>
                                            <th class="text-end">Grupo Atribuído</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($info['modulos'] as $modulo): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-medium text-white"><?= htmlspecialchars($modulo['modulo']) ?></div>
                                                </td>
                                                <td class="text-end">
                                                    <?php if ($modulo['grupo'] !== 'não atribuído'): ?>
                                                        <span class="badge-pill-glow badge-info-glow">
                                                            Grupo <?= htmlspecialchars($modulo['grupo']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted small italic">Não atribuído</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php $conn->close(); ?>