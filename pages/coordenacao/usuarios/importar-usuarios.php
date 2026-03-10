<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $maxFileSize = 5 * 1024 * 1024;

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $originalName = $_FILES['file']['name'];
        $fileType = mime_content_type($file);

        // Fallback para quando o mime_content_type não é confiável (ex: arquivos .tmp no Windows)
        // ou se o fallback em config.php retornou application/octet-stream
        if ($fileType === 'application/octet-stream' || $fileType === false) {
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if ($ext === 'csv') {
                $fileType = 'text/csv';
            } elseif ($ext === 'txt') {
                $fileType = 'text/plain';
            }
        }

        if ($fileType !== 'text/plain' && $fileType !== 'text/csv' && $fileType !== 'application/vnd.ms-excel') {
            echo "<div class='alert alert-danger'>Erro: Apenas arquivos CSV são permitidos. (Tipo detectado: $fileType)</div>";
            echo "<a href=\"?page=listar-usuarios\" class=\"btn btn-secondary\">Voltar</a>";
            exit();
        }

        if ($fileSize > $maxFileSize) {
            echo "<div class='alert alert-danger'>Erro: O arquivo excede o tamanho máximo permitido de 5MB.</div>";
            echo "<a href=\"?page=listar-usuarios\" class=\"btn btn-secondary\">Voltar</a>";
            exit();
        }

        if (($handle = fopen($file, "r")) !== false) {
            fgetcsv($handle, 0, ",", "\"", "\\");

            $success_count = 0;
            $error_count = 0;
            $duplicated_count = 0;
            $messages = [];

            // Aumenta o tempo limite para processos longos
            set_time_limit(120);

            // Prepara as consultas fora do loop para performance
            $checkStmt = $conn->prepare("SELECT idusuario FROM usuarios WHERE login = ?");
            $insertStmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, login, senha, tipo, registro, ativo, periodo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Inicia transação para acelerar múltiplas inserções
            $conn->begin_transaction();

            try {
                while (($data = fgetcsv($handle, 1000, ",", "\"", "\\")) !== false) {
                    if (count($data) < 9) continue;

                    $nome = $data[0];
                    $email = $data[1];
                    $telefone = $data[2];
                    $login = $data[3];
                    $senha_plana = $data[4];
                    $tipo = $data[5];
                    $registro = $data[6];
                    $ativo = $data[7];
                    $periodo = $data[8];

                    if (empty($nome) || empty($email) || empty($login) || empty($senha_plana)) {
                        $messages[] = "<div class='alert alert-danger'>Dados obrigatórios faltando para o login '$login'.</div>";
                        $error_count++;
                        continue;
                    }

                    // Verifica se já existe
                    $checkStmt->bind_param("s", $login);
                    $checkStmt->execute();
                    $checkStmt->store_result();

                    if ($checkStmt->num_rows > 0) {
                        $duplicated_count++;
                        continue;
                    }

                    // Hash da senha
                    $senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);

                    $insertStmt->bind_param("sssssisii", $nome, $email, $telefone, $login, $senha_hash, $tipo, $registro, $ativo, $periodo);

                    if ($insertStmt->execute()) {
                        $success_count++;
                    } else {
                        $messages[] = "<div class='alert alert-danger'>Erro ao inserir o usuário '$login'.</div>";
                        $error_count++;
                    }
                }
                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                $messages[] = "<div class='alert alert-danger'>Erro crítico: " . $e->getMessage() . "</div>";
            }

            fclose($handle);
            $checkStmt->close();
            $insertStmt->close();

            echo "<div class='alert alert-success'>$success_count usuários importados com sucesso.</div>";
            if ($duplicated_count > 0) {
                echo "<div class='alert alert-warning'>$duplicated_count logins já existiam e foram ignorados.</div>";
            }
            if ($error_count > 0) {
                echo "<div class='alert alert-danger'>$error_count erros durante a importação.</div>";
                foreach (array_slice($messages, 0, 5) as $msg) echo $msg;
                if (count($messages) > 5) echo "<div class='alert alert-info'>...e mais " . (count($messages) - 5) . " erros.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Erro ao abrir o arquivo.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Erro no upload do arquivo.</div>";
    }
}
?>

<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            <h3>Importar Lista de Usuários</h3>
            <form action="?page=importar-usuarios" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="file" class="form-label">Selecione o arquivo CSV</label>
                    <input type="file" name="file" id="file" class="form-control" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary">Importar</button>
                <a href="?page=listar-usuarios" class="btn btn-secondary">Voltar</a>
            </form>
        </div>
    </div>
</div>
</div>