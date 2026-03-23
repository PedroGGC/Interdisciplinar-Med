<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../../../cfg/config.php');

if (empty($_SESSION["login"])) {
    echo json_encode([
        "success" => false,
        "message" => "Usuário não autenticado. Redirecionando para a página de login.",
        "redirect" => "../../index.php"
    ]);
    exit();
}

// Verificar se o ID da unidade foi passado na URL
if (isset($_GET['idunidade']) && is_numeric($_GET['idunidade'])) {
    $idunidade = intval($_GET['idunidade']);

    // Buscar a unidade pelo ID
    $stmt = $conn->prepare("SELECT idunidade, nome_unidade FROM unidades WHERE idunidade = ?");
    $stmt->bind_param("i", $idunidade);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $unidade = $result->fetch_assoc();
    } else {
        echo json_encode(["success" => false, "message" => "Unidade não encontrada."]);
        exit();
    }

    // Buscar todos os preceptores
    $sqlPreceptores = "SELECT u.*, pu.idunidade, un.nome_unidade FROM usuarios u 
                       LEFT JOIN preceptores_unidades pu ON u.idusuario = pu.idusuario 
                       LEFT JOIN unidades un ON pu.idunidade = un.idunidade 
                       WHERE u.tipo = 1";
    $resPreceptores = $conn->query($sqlPreceptores);

    // Buscar módulos associados e não associados
    $modulosAssociados = $conn->query("SELECT m.idmodulo, m.nome_modulo FROM modulos m JOIN unidades_modulos um ON m.idmodulo = um.idmodulo WHERE um.idunidade = $idunidade");
    $nummodulosAssociados = $modulosAssociados->num_rows;

    $modulosNaoAssociados = $conn->query("SELECT idmodulo, nome_modulo FROM modulos WHERE idmodulo NOT IN (SELECT idmodulo FROM unidades_modulos WHERE idunidade = $idunidade)");
    $nummodulosNaoAssociados = $modulosNaoAssociados->num_rows;
} else {
    echo json_encode(["success" => false, "message" => "ID da unidade não informado ou inválido."]);
    exit();
}

// Associar ou desassociar preceptores e módulos via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ["success" => false, "message" => ""];
    $preceptoresSelecionados = $_POST['preceptores'] ?? [];
    $modulosSelecionados = $_POST['modulos'] ?? [];
    $idunidade = intval($_POST['idunidade'] ?? 0);
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'associar' && !empty($modulosSelecionados)) {
        foreach ($modulosSelecionados as $idModulo) {
            // Associar módulo à unidade
            $stmt = $conn->prepare("INSERT INTO unidades_modulos (idmodulo, idunidade) VALUES (?, ?)");
            $stmt->bind_param("ii", $idModulo, $idunidade);
            $stmt->execute();
        }
        $response["success"] = true;
        $response["message"] = 'Módulos associados com sucesso!';
    } elseif ($acao === 'desassociar' && !empty($modulosSelecionados)) {
        foreach ($modulosSelecionados as $idModulo) {
            // Desassociar módulo da unidade
            $stmt = $conn->prepare("DELETE FROM unidades_modulos WHERE idmodulo = ? AND idunidade = ?");
            $stmt->bind_param("ii", $idModulo, $idunidade);
            $stmt->execute();
        }
        $response["success"] = true;
        $response["message"] = 'Módulos desassociados com sucesso!';
    } elseif ($acao === 'associar' && !empty($preceptoresSelecionados)) {
        foreach ($preceptoresSelecionados as $idPreceptor) {
            // Desassociar o preceptor de qualquer unidade anterior
            $stmt = $conn->prepare("DELETE FROM preceptores_unidades WHERE idusuario = ?");
            $stmt->bind_param("i", $idPreceptor);
            $stmt->execute();

            // Associar preceptor à nova unidade
            $stmt = $conn->prepare("INSERT INTO preceptores_unidades (idusuario, idunidade) VALUES (?, ?)");
            $stmt->bind_param("ii", $idPreceptor, $idunidade);
            $stmt->execute();
        }
        $response["success"] = true;
        $response["message"] = 'Preceptores associados com sucesso!';
    } elseif ($acao === 'desassociar' && !empty($preceptoresSelecionados)) {
        foreach ($preceptoresSelecionados as $idPreceptor) {
            // Desassociar o preceptor de todas as unidades
            $stmt = $conn->prepare("DELETE FROM preceptores_unidades WHERE idusuario = ?");
            $stmt->bind_param("i", $idPreceptor);
            $stmt->execute();

            // Desassociar o preceptor de todos os módulos
            $stmt = $conn->prepare("DELETE FROM preceptores_modulos WHERE idusuario = ?");
            $stmt->bind_param("i", $idPreceptor);
            $stmt->execute();
        }
        $response["success"] = true;
        $response["message"] = 'Preceptores desassociados com sucesso!';
    } else {
        $response["message"] = 'Ação inválida ou nenhum item selecionado.';
    }

    echo json_encode($response);
    exit();
}
?>
<?php
// ... original logic kept ...
?>

<style>
    .container-card {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }

    @media (max-width: 992px) {
        .container-card {
            grid-template-columns: 1fr;
        }
    }

    .scroll-card {
        max-height: 400px;
        overflow-y: auto;
    }

    .card-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: var(--surface-2) !important;
    }
</style>

<script>
    function toggleCheckboxes(selectAllCheckbox, checkboxClass) {
        const checkboxes = document.querySelectorAll(`.${checkboxClass}`);
        checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
    }

    function submitForm(event, form) {
        event.preventDefault();
        const formData = $(form).serialize();

        $.ajax({
            type: 'POST',
            url: 'visualizar-unidade.php?idunidade=<?php echo $idunidade; ?>',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message || 'Ocorreu um erro ao processar sua solicitação.');
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                }
            },
            error: function(xhr, status, error) {
                alert('Ocorreu um erro ao processar sua solicitação.');
            }
        });
    }
</script>
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1"><?php echo htmlspecialchars($unidade['nome_unidade']); ?></h2>
                    <p class="text-muted mb-0">Gestão de vínculos e alocação de módulos/preceptores</p>
                </div>
                <button class="btn btn-secondary px-4" onclick="location.href='unidades.php'"><i class="bi bi-arrow-left me-1"></i> Voltar</button>
            </div>

            <h5 class="mb-3 text-accent-light"><i class="bi bi-journal-text me-2"></i>Vínculo de Módulos</h5>
            <div class="container-card">
                <!-- Módulos Associados -->
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header border-bottom border-border p-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-white">Módulos Vinculados</h6>
                        <span class="badge-pill-glow badge-success-glow"><?php echo $nummodulosAssociados; ?></span>
                    </div>
                    <div class="card-body p-0 scroll-card">
                        <form method="POST" onsubmit="submitForm(event, this)">
                            <input type="hidden" name="idunidade" value="<?php echo $idunidade; ?>">
                            <input type="hidden" name="acao" value="desassociar">
                            <div class="list-group list-group-flush">
                                <?php if ($nummodulosAssociados > 0): ?>
                                    <?php while ($modulo = $modulosAssociados->fetch_assoc()): ?>
                                        <div class="list-group-item bg-transparent border-border p-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="modulos[]" value="<?php echo $modulo['idmodulo']; ?>"
                                                    class="form-check-input modulo-associado"
                                                    id="modulo-<?php echo $modulo['idmodulo']; ?>">
                                                <label class="form-check-label text-white ms-2" for="modulo-<?php echo $modulo['idmodulo']; ?>">
                                                    <?php echo htmlspecialchars($modulo['nome_modulo']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted small">Nenhum módulo vinculado.</div>
                                <?php endif; ?>
                            </div>
                            <?php if ($nummodulosAssociados > 0): ?>
                                <div class="p-3 bg-surface-2 border-top border-border">
                                    <button type="submit" class="btn btn-danger btn-sm w-100">Desvincular Selecionados</button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Módulos Disponíveis -->
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header border-bottom border-border p-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-white">Módulos Disponíveis</h6>
                        <span class="badge-pill-glow badge-info-glow"><?php echo $nummodulosNaoAssociados; ?></span>
                    </div>
                    <div class="card-body p-0 scroll-card">
                        <form method="POST" onsubmit="submitForm(event, this)">
                            <input type="hidden" name="idunidade" value="<?php echo $idunidade; ?>">
                            <input type="hidden" name="acao" value="associar">
                            <div class="list-group list-group-flush">
                                <?php if ($nummodulosNaoAssociados > 0): ?>
                                    <?php while ($modulo = $modulosNaoAssociados->fetch_assoc()): ?>
                                        <div class="list-group-item bg-transparent border-border p-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="modulos[]" value="<?php echo $modulo['idmodulo']; ?>"
                                                    class="form-check-input modulo-nao-associado"
                                                    id="modulo-dispo-<?php echo $modulo['idmodulo']; ?>">
                                                <label class="form-check-label text-white ms-2" for="modulo-dispo-<?php echo $modulo['idmodulo']; ?>">
                                                    <?php echo htmlspecialchars($modulo['nome_modulo']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted small">Nenhum módulo disponível.</div>
                                <?php endif; ?>
                            </div>
                            <?php if ($nummodulosNaoAssociados > 0): ?>
                                <div class="p-3 bg-surface-2 border-top border-border">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Vincular Selecionados</button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <h5 class="mb-3 text-accent-light"><i class="bi bi-person-badge me-2"></i>Vínculo de Preceptores</h5>
            <div class="container-card pb-5">
                <!-- Preceptores Vinculados -->
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header border-bottom border-border p-3">
                        <h6 class="mb-0 text-white">Preceptores na Unidade</h6>
                    </div>
                    <div class="card-body p-0 scroll-card">
                        <form method="POST" onsubmit="submitForm(event, this)">
                            <input type="hidden" name="idunidade" value="<?php echo $idunidade; ?>">
                            <input type="hidden" name="acao" value="desassociar">
                            <div class="list-group list-group-flush">
                                <?php mysqli_data_seek($resPreceptores, 0); 
                                $countP = 0;
                                while ($preceptor = $resPreceptores->fetch_assoc()): ?>
                                    <?php if (!is_null($preceptor['idunidade']) && $preceptor['idunidade'] == $idunidade): $countP++; ?>
                                        <div class="list-group-item bg-transparent border-border p-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="preceptores[]" value="<?php echo $preceptor['idusuario']; ?>" class="form-check-input preceptor-associado" id="pre-vinc-<?php echo $preceptor['idusuario']; ?>">
                                                <label class="form-check-label text-white ms-2" for="pre-vinc-<?php echo $preceptor['idusuario']; ?>">
                                                    <?php echo htmlspecialchars($preceptor['nome']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                                <?php if ($countP == 0): ?>
                                    <div class="p-4 text-center text-muted small">Nenhum preceptor vinculado.</div>
                                <?php endif; ?>
                            </div>
                            <?php if ($countP > 0): ?>
                                <div class="p-3 bg-surface-2 border-top border-border">
                                    <button type="submit" class="btn btn-danger btn-sm w-100">Remover Selecionados</button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Preceptores Disponíveis -->
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header border-bottom border-border p-3">
                        <h6 class="mb-0 text-white">Preceptores Disponíveis</h6>
                    </div>
                    <div class="card-body p-0 scroll-card">
                        <form method="POST" onsubmit="submitForm(event, this)">
                            <input type="hidden" name="idunidade" value="<?php echo $idunidade; ?>">
                            <input type="hidden" name="acao" value="associar">
                            <div class="list-group list-group-flush">
                                <?php mysqli_data_seek($resPreceptores, 0);
                                $countD = 0;
                                while ($preceptor = $resPreceptores->fetch_assoc()): ?>
                                    <?php if (is_null($preceptor['idunidade'])): $countD++; ?>
                                        <div class="list-group-item bg-transparent border-border p-3">
                                            <div class="form-check">
                                                <input type="checkbox" name="preceptores[]" value="<?php echo $preceptor['idusuario']; ?>" class="form-check-input preceptor-nao-associado" id="pre-disp-<?php echo $preceptor['idusuario']; ?>">
                                                <label class="form-check-label text-white ms-2" for="pre-disp-<?php echo $preceptor['idusuario']; ?>">
                                                    <?php echo htmlspecialchars($preceptor['nome']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                                <?php if ($countD == 0): ?>
                                    <div class="p-4 text-center text-muted small">Nenhum preceptor livre.</div>
                                <?php endif; ?>
                            </div>
                            <?php if ($countD > 0): ?>
                                <div class="p-3 bg-surface-2 border-top border-border">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Vincular Selecionados</button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
