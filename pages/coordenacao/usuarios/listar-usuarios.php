<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
    <style>
        body {
            overflow-y: hidden;
        }

        .card {
            overflow-y: auto;
            max-height: 750px;
        }
    </style>
</head>

<body>

    <?php
    function tipoTexto($tipo)
    {
        switch ($tipo) {
            case '0':
                return 'Aluno';
            case '1':
                return 'Preceptor';
            case '2':
                return 'Coordenação';
            case '3':
                return 'Coordenação e Preceptor';
            default:
                return 'Não definido';
        }
    }

    function ativoTexto($ativo)
    {
        switch ($ativo) {
            case '0':
                return 'Inativo';
            case '1':
                return 'Ativo';
            default:
                return 'Não definido';
        }
    }

    $sql = "SELECT idusuario, nome, tipo, registro, ativo FROM usuarios";
    $result = $conn->query($sql);

    if (!$result) {
        die("Erro na consulta SQL: " . $conn->error);
    }

    ?>

    <div class="container mt-4">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Gestão de Usuários</h2>
                <p class="text-muted mb-0">Controle de acesso, tipos de conta e status do sistema</p>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form id="formUsuarios" method="post" action="">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <select name="novo_tipo" class="form-select form-select-sm" id="novoTipoSelect" style="width: 220px;">
                                <option value="-1">Alterar Tipo Para...</option>
                                <option value="0">Aluno</option>
                                <option value="1">Preceptor</option>
                                <option value="2">Coordenação</option>
                                <option value="3">Coordenação e Preceptor</option>
                            </select>
                            <button type="button" class="btn btn-primary btn-sm px-3" onclick="alterarTipoSelecionados()">Aplicar Tipo</button>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary btn-sm px-3" onclick="alternarAtivoSelecionados()">
                                <i class="bi bi-toggle-on me-1"></i> Alternar Status
                            </button>
                            <button type="button" class="btn btn-danger btn-sm px-3" onclick="excluirSelecionados()">
                                <i class="bi bi-trash me-1"></i> Excluir Selecionados
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="checkbox" id="selectAll" onclick="selecionarTodos(this)">
                                        </div>
                                    </th>
                                    <th>Usuário / Nome</th>
                                    <th>Tipo de Conta</th>
                                    <th>RA / CRM</th>
                                    <th>Status Atual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): 
                                    $statusClass = $row['ativo'] == '1' ? 'badge-success-glow' : 'badge-warning-glow';
                                    $statusText = $row['ativo'] == '1' ? 'Ativo' : 'Inativo';
                                    
                                    // Mapeamento de ícones por tipo
                                    $icon = 'bi-person';
                                    if($row['tipo'] == '1') $icon = 'bi-person-badge';
                                    if($row['tipo'] == '2') $icon = 'bi-shield-lock';
                                    if($row['tipo'] == '3') $icon = 'bi-person-gear';
                                ?>
                                    <tr id="linha-<?php echo $row['idusuario']; ?>">
                                        <td>
                                            <div class="form-check m-0">
                                                <input class="form-check-input" type="checkbox" name="usuarios[]" value="<?php echo $row['idusuario']; ?>" id="user-<?php echo $row['idusuario']; ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class='d-flex align-items-center'>
                                                <div class='btn-action me-3' style='pointer-events: none;'>
                                                    <i class='bi <?php echo $icon; ?>'></i>
                                                </div>
                                                <span class='fw-medium text-white'><?php echo htmlspecialchars($row['nome']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small"><?php echo tipoTexto($row['tipo']); ?></span>
                                        </td>
                                        <td>
                                            <span class="font-monospace text-muted small"><?php echo isset($row['registro']) ? htmlspecialchars($row['registro']) : '---'; ?></span>
                                        </td>
                                        <td>
                                            <span class='badge-pill-glow <?php echo $statusClass; ?>'><?php echo $statusText; ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>

        function selecionarTodos(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('input[name="usuarios[]"]');
            checkboxes.forEach((checkbox) => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        function excluirSelecionados() {
            const checkboxes = document.querySelectorAll('input[name="usuarios[]"]:checked');
            if (checkboxes.length === 0) {
                alert_selecionar_usuario();
                return;
            }

            const form = document.getElementById('formUsuarios');

            Swal.fire({
                title: 'Você tem certeza?',
                text: "Esta ação não pode ser revertida!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.action = '?page=excluir-usuarios';
                    form.method = 'POST';
                    form.submit();
                }
            });
        }

        function alterarTipoSelecionados() {
            const novoTipo = document.getElementById('novoTipoSelect').value;
            const checkboxes = document.querySelectorAll('input[name="usuarios[]"]:checked');

            if (novoTipo === "-1") {
                alert_selecionar_tipo();
                return;
            }

            if (checkboxes.length === 0) {
                alert_selecionar_usuario();
                return;
            }

            const form = document.getElementById('formUsuarios');
            form.action = '?page=alterar-tipo';
            form.method = 'POST';
            form.submit();
        }

        function alternarAtivoSelecionados() {
            const checkboxes = document.querySelectorAll('input[name="usuarios[]"]:checked');

            if (checkboxes.length === 0) {
                alert_selecionar_usuario();
                return;
            }

            const form = document.getElementById('formUsuarios');
            form.action = '?page=alterar-status';
            form.method = 'POST';
            form.submit();
        }
    </script>

</body>

</html>
