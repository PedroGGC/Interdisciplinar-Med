<?php
include("../../../cfg/config.php");

$acao = $_GET['acao'] ?? null;

switch ($acao) {
    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idpergunta'], $_POST['titulo'], $_POST['descricao'])) {
            $idpergunta = (int) $_POST['idpergunta'];
            $titulo = $_POST['titulo'];
            $descricao = $_POST['descricao'];
            $query = "UPDATE perguntas_avaliacoes SET titulo = ?, descricao = ? WHERE idpergunta = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ssi', $titulo, $descricao, $idpergunta);
            if ($stmt->execute()) {
                echo "<script>location.href='?page=listar-perguntas';</script>";
            } else {
                echo "<script>alert('Não foi possível concluir a alteração.');</script>";
            }
        } else {
            $idpergunta = (int) $_GET['idpergunta'];
            $query = "SELECT * FROM perguntas_avaliacoes WHERE idpergunta = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $idpergunta);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $titulo = $row['titulo'];
            $descricao = $row['descricao'];
        }
        break;

    case 'excluir':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $idpergunta = (int) $_POST["idpergunta"];
            $sql = "DELETE FROM perguntas_avaliacoes WHERE idpergunta = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $idpergunta);
            if ($stmt->execute()) {
                echo "<script>location.href='?page=listar-perguntas';</script>";
            } else {
                echo "<script>alert('Não foi possível excluir a pergunta.');</script>";
            }
            $stmt->close();
        }
        break;

    case "adicionar":
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titulo'], $_POST['descricao'])) {
            $titulo = $_POST["titulo"];
            $descricao = $_POST["descricao"];
            $sql = "INSERT INTO perguntas_avaliacoes (titulo, descricao, tipo_resposta, ativo) VALUES (?, ?, 'escala', 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $titulo, $descricao);
            $stmt->execute();
            $stmt->close();
            echo "<script>location.href='?page=listar-perguntas';</script>";
        }
        break;
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php if ($acao == 'editar'): ?>
                        <div class="d-flex align-items-center mb-4">
                            <div class="btn-action me-3" style="pointer-events: none;"><i class="bi bi-pencil-square"></i></div>
                            <div>
                                <h4 class="mb-0 text-white">Editar Critério</h4>
                                <p class="text-muted small mb-0">Atualize as informações do indicador de avaliação</p>
                            </div>
                        </div>
                        <form action="" method="post">
                            <input type="hidden" name="idpergunta" value="<?= htmlspecialchars($idpergunta); ?>">
                            <div class="mb-3">
                                <label for="titulo" class="form-label text-muted small fw-bold">TÍTULO DO CRITÉRIO</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($titulo); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="descricao" class="form-label text-muted small fw-bold">DESCRIÇÃO DETALHADA</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="4" required><?= htmlspecialchars($descricao); ?></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="?page=listar-perguntas" class="btn btn-secondary px-4">Cancelar</a>
                                <button type="submit" class="btn btn-primary px-4">Salvar Alterações</button>
                            </div>
                        </form>

                    <?php elseif ($acao == 'excluir'): ?>
                        <div class="text-center py-4">
                            <div class="btn-action mx-auto mb-3 text-danger" style="width: 64px; height: 64px; pointer-events: none; background: rgba(248, 81, 73, 0.1);"><i class="bi bi-exclamation-triangle fs-2"></i></div>
                            <h4 class="text-white">Confirmar Exclusão</h4>
                            <p class="text-muted mb-4">Você está prestes a remover permanentemente este critério de avaliação. Esta ação não pode ser desfeita.</p>
                            <form action="" method="post">
                                <input type="hidden" name="idpergunta" value="<?= htmlspecialchars($_GET["idpergunta"]); ?>">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="?page=listar-perguntas" class="btn btn-secondary px-4">Voltar</a>
                                    <button type="submit" class="btn btn-danger px-4" name="excluir">Confirmar Exclusão</button>
                                </div>
                            </form>
                        </div>

                    <?php elseif ($acao == 'adicionar'): ?>
                        <div class="d-flex align-items-center mb-4">
                            <div class="btn-action me-3" style="pointer-events: none;"><i class="bi bi-plus-circle"></i></div>
                            <div>
                                <h4 class="mb-0 text-white">Novo Critério</h4>
                                <p class="text-muted small mb-0">Adicione um novo indicador para as avaliações clínicas</p>
                            </div>
                        </div>
                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="titulo" class="form-label text-muted small fw-bold">TÍTULO DO CRITÉRIO</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ex: Raciocínio Clínico" required>
                            </div>
                            <div class="mb-4">
                                <label for="descricao" class="form-label text-muted small fw-bold">DESCRIÇÃO DETALHADA</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="4" placeholder="Descreva o que deve ser observado pelo preceptor..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="?page=listar-perguntas" class="btn btn-secondary px-4">Cancelar</a>
                                <button type="submit" class="btn btn-primary px-4">Adicionar Critério</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
