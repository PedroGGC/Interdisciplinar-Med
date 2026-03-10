<?php
include('../../../cfg/config.php');
include('acoes-horario.php');

// Verifica se o usuário está logado
checkLogin();

$horarioData = null;
// Verifica se o ID do horário foi passado na URL
if (isset($_GET['id'])) {
    $idHorario = $_GET['id'];
    $horarioData = getHorarioData($conn, $idHorario);
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'getModulos':
            $idUnidade = $_GET['idunidade'];
            echo getModulos($conn, $idUnidade);
            break;

        case 'getSubgrupos':
            $idModulo = $_GET['idmodulo'];
            echo getSubgrupos($conn, $idModulo);
            break;

        case 'getPreceptores':
            $idModulo = $_GET['idmodulo'];
            $idUnidade = $_GET['idunidade'];
            echo getPreceptores($conn, $idModulo, $idUnidade);
            break;
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = saveOrUpdateHorario($conn, $_POST);
    if ($result['success']) {
        echo "<script>alert('" . $result['message'] . "'); location.href='horarios.php';</script>";
    } else {
        echo "<script>alert('" . $result['message'] . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preencher Horário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">

</head>
<body>
    <header>
        <?php include('../../../includes/navbar.php'); ?>
        <?php include('../../../includes/menu-lateral-coordenacao.php'); ?>
    </header>
    <main class="container py-5">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><?php echo isset($horarioData['idhorario']) ? 'Editar Horário' : 'Preencher Horário'; ?></h2>
                <p class="text-muted mb-0">Informe os detalhes para a nova grade horária</p>
            </div>
            <a href="horarios.php" class="btn btn-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            <input type="hidden" name="idHorario" value="<?php echo $horarioData['idhorario'] ?? ''; ?>">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="idUnidade" class="form-label">Unidade Hospitalar</label>
                                    <select name="idUnidade" id="idUnidade" class="form-select" required>
                                        <option value="">Selecione a Unidade</option>
                                        <?php
                                        $unidades = getUnidades($conn);
                                        foreach ($unidades as $unidade) {
                                            $selected = ($horarioData['idunidade'] ?? '') == $unidade['idunidade'] ? 'selected' : '';
                                            echo "<option value='" . $unidade['idunidade'] . "' $selected>" . $unidade['nome_unidade'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="idModulo" class="form-label">Módulo de Ensino</label>
                                    <select name="idModulo" id="idModulo" class="form-select" required>
                                        <option value="">Selecione o Módulo</option>
                                        <?php
                                        if (isset($horarioData['idunidade'])) {
                                            $modulos = getModulosByUnidade($conn, $horarioData['idunidade']);
                                            foreach ($modulos as $modulo) {
                                                $selected = ($horarioData['idmodulo'] ?? '') == $modulo['idmodulo'] ? 'selected' : '';
                                                echo "<option value='" . $modulo['idmodulo'] . "' $selected>" . $modulo['nome_modulo'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="subgrupo" class="form-label">Subgrupo de Alunos</label>
                                    <select name="subgrupo" id="subgrupo" class="form-select" required>
                                        <option value="">Selecione o Subgrupo</option>
                                        <?php
                                        if (isset($horarioData['idmodulo'])) {
                                            $subgrupos = getSubgruposByModulo($conn, $horarioData['idmodulo']);
                                            foreach ($subgrupos as $subgrupo) {
                                                $selected = ($horarioData['idsubgrupo'] ?? '') == $subgrupo['idsubgrupo'] ? 'selected' : '';
                                                echo "<option value='" . $subgrupo['idsubgrupo'] . "' $selected>" . $subgrupo['nome_subgrupo'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="idPreceptor" class="form-label">Preceptor Responsável</label>
                                    <select name="idPreceptor" id="idPreceptor" class="form-select" required>
                                        <option value="">Selecione o Preceptor</option>
                                        <?php
                                        if (isset($horarioData['idmodulo']) && isset($horarioData['idunidade'])) {
                                            $preceptores = getPreceptoresByModuloUnidade($conn, $horarioData['idmodulo'], $horarioData['idunidade']);
                                            foreach ($preceptores as $preceptor) {
                                                $selected = ($horarioData['idpreceptor'] ?? '') == $preceptor['idusuario'] ? 'selected' : '';
                                                echo "<option value='" . $preceptor['idusuario'] . "' $selected>" . $preceptor['nome'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="diaSemana" class="form-label">Dia da Semana</label>
                                    <select name="diaSemana" id="diaSemana" class="form-select" required>
                                        <option value="Segunda" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Segunda') ? 'selected' : ''; ?>>Segunda-feira</option>
                                        <option value="Terça" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Terça') ? 'selected' : ''; ?>>Terça-feira</option>
                                        <option value="Quarta" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Quarta') ? 'selected' : ''; ?>>Quarta-feira</option>
                                        <option value="Quinta" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Quinta') ? 'selected' : ''; ?>>Quinta-feira</option>
                                        <option value="Sexta" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Sexta') ? 'selected' : ''; ?>>Sexta-feira</option>
                                        <option value="Sábado" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Sábado') ? 'selected' : ''; ?>>Sábado</option>
                                        <option value="Domingo" <?php echo (isset($horarioData['dia_semana']) && $horarioData['dia_semana'] == 'Domingo') ? 'selected' : ''; ?>>Domingo</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="horaInicio" class="form-label">Início</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-surface-2 border-border text-accent"><i class="bi bi-clock"></i></span>
                                        <input type="text" name="horaInicio" id="horaInicio" class="form-control" required value="<?php echo htmlspecialchars($horarioData['hora_inicio'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="horaFim" class="form-label">Término</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-surface-2 border-border text-accent"><i class="bi bi-clock-history"></i></span>
                                        <input type="text" name="horaFim" id="horaFim" class="form-control" required value="<?php echo htmlspecialchars($horarioData['hora_fim'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="col-12 mt-4 text-end">
                                    <hr class="border-border opacity-25 mb-4">
                                    <button type="submit" class="btn btn-primary px-5">
                                        <i class="bi bi-check2-circle me-2"></i> Confirmar Dados
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#idUnidade').change(function() {
                var idUnidade = $(this).val();
                if (idUnidade) {
                    $.ajax({
                        url: 'preencher-horario.php',
                        type: 'GET',
                        data: { action: 'getModulos', idunidade: idUnidade },
                        success: function(data) {
                            var modulos = JSON.parse(data);
                            // Limpar o campo de seleção de módulos antes de adicionar novos
                            $('#idModulo').html('<option value="">Selecione o Módulo</option>');

                            $.each(modulos, function(index, modulo) {
                                $('#idModulo').append('<option value="'+modulo.idmodulo+'">'+modulo.nome_modulo+'</option>');
                            });
                        }
                    });
                }
            });

            $('#idModulo').change(function() {
                var idModulo = $(this).val();
                if (idModulo) {
                    $.ajax({
                        url: 'preencher-horario.php',
                        type: 'GET',
                        data: { action: 'getSubgrupos', idmodulo: idModulo },
                        success: function(data) {
                            var subgrupos = JSON.parse(data);
                            // Limpar o campo de seleção de subgrupos antes de adicionar novos
                            $('#subgrupo').html('<option value="">Selecione o Subgrupo</option>');

                            $.each(subgrupos, function(index, subgrupo) {
                                $('#subgrupo').append('<option value="'+subgrupo.idsubgrupo+'">'+subgrupo.nome_subgrupo+'</option>');
                            });
                        }
                    });

                    var idUnidade = $('#idUnidade').val();
                    $.ajax({
                        url: 'preencher-horario.php',
                        type: 'GET',
                        data: { action: 'getPreceptores', idunidade: idUnidade, idmodulo: idModulo },
                        success: function(data) {
                            var preceptores = JSON.parse(data);
                            // Limpar o campo de seleção de preceptores antes de adicionar novos
                            $('#idPreceptor').html('<option value="">Selecione o Preceptor</option>');

                            $.each(preceptores, function(index, preceptor) {
                                $('#idPreceptor').append('<option value="'+preceptor.idusuario+'">'+preceptor.nome+'</option>');
                            });
                        }
                    });
                }
            });

            // Preencher os campos no carregamento da página se estivermos editando um horário
            <?php if (isset($horarioData)) { ?>
                // Disparar os eventos 'change' para preencher os campos relacionados
                $('#idUnidade').trigger('change');
                setTimeout(function() {
                    $('#idModulo').val('<?php echo $horarioData['idmodulo']; ?>').trigger('change');
                    setTimeout(function() {
                        $('#subgrupo').val('<?php echo $horarioData['idsubgrupo']; ?>');
                        $('#idPreceptor').val('<?php echo $horarioData['idpreceptor']; ?>');
                    }, 500);
                }, 500);
            <?php } ?>
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  flatpickr("#horaInicio", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: true
  });

  flatpickr("#horaFim", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: true
  });
</script>

</body>
</html>