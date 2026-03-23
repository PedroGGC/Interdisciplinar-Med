<?php
session_start();
include('../cfg/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $registro = $_POST['registro'];
    $login = $_POST['login'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, registro, login, senha, tipo) VALUES (?, ?, ?, ?, ? , ?, -1)");
    $stmt->bind_param("ssssss", $nome, $email, $telefone, $registro, $login, $senha);

    if ($stmt->execute()) {
        echo "<script>location.href='../index.php?alert=1';</script>";
    } else {
        echo "<script>location.href='cadastro.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InterMed — Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d1117;
            --surface: #161b22;
            --surface-2: #1c2128;
            --border: #30363d;
            --accent: #1a7940;
            --accent-light: #2ea855;
            --accent-glow: rgba(46, 168, 85, 0.18);
            --text: #e6edf3;
            --text-muted: #8b949e;
            --radius: 10px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background-color: var(--bg);
            background-image:
                radial-gradient(ellipse 60% 50% at 20% 60%, rgba(26, 121, 64, 0.12) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 80% 20%, rgba(46, 168, 85, 0.07) 0%, transparent 60%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url('../img/bg.jpg');
            background-size: cover;
            background-position: center;
            opacity: 0.07;
            z-index: 0;
        }

        .auth-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            animation: fadeUp 0.4s ease forwards;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            justify-content: center;
        }

        .auth-brand .brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 16px rgba(26, 121, 64, 0.45);
        }

        .auth-brand .brand-icon i {
            font-size: 18px;
            color: white;
        }

        .auth-brand .brand-text {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 20px;
            color: var(--text);
        }

        .auth-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.5);
        }

        .auth-card h2 {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 20px;
            color: var(--text);
            margin-bottom: 4px;
        }

        .auth-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 14px;
        }

        .form-grid .full {
            grid-column: 1 / -1;
        }

        .auth-form-group {
            display: flex;
            flex-direction: column;
        }

        .auth-form-group label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .auth-input {
            padding: 10px 14px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            outline: none;
            width: 100%;
        }

        .auth-input::placeholder {
            color: var(--text-muted);
        }

        .auth-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .auth-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 24px 0;
        }

        .btn-auth-primary {
            width: 100%;
            padding: 11px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 10px;
        }

        .btn-auth-primary:hover {
            background: var(--accent-light);
            box-shadow: 0 0 20px var(--accent-glow);
        }

        .btn-auth-secondary {
            width: 100%;
            padding: 10px;
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .btn-auth-secondary:hover {
            background: var(--surface-2);
            color: var(--text);
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-brand">
            <div class="brand-icon">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <span class="brand-text">InterMed</span>
        </div>

        <div class="auth-card">
            <h2>Criar conta</h2>
            <p class="auth-subtitle">Preencha seus dados para solicitação de acesso</p>

            <form action="" method="POST">
                <div class="form-grid">
                    <div class="auth-form-group full">
                        <label for="nome">Nome completo</label>
                        <input type="text" id="nome" name="nome" class="auth-input" placeholder="Seu nome" required>
                    </div>
                    <div class="auth-form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" class="auth-input" placeholder="email@exemplo.com" required>
                    </div>
                    <div class="auth-form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" class="auth-input" placeholder="(00) 00000-0000" required maxlength="15" onkeyup="handlePhone(event)">
                    </div>
                    <div class="auth-form-group">
                        <label for="registro">RA / CRM</label>
                        <input type="text" id="registro" name="registro" class="auth-input" placeholder="Seu registro" required>
                    </div>
                    <div class="auth-form-group">
                        <label for="login">Login</label>
                        <input type="text" id="login" name="login" class="auth-input" placeholder="Seu login" required autocomplete="username">
                    </div>
                    <div class="auth-form-group full">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" class="auth-input" placeholder="••••••••" required autocomplete="new-password">
                    </div>
                </div>

                <hr class="auth-divider">

                <button type="submit" class="btn-auth-primary">
                    <i class="bi bi-person-check me-1"></i> Cadastrar
                </button>
            </form>

            <button class="btn-auth-secondary" onclick="location.href='../index.php'">
                <i class="bi bi-arrow-left me-1"></i> Voltar para o Login
            </button>
        </div>
    </div>
    <script>
        function handlePhone(event) {
            let input = event.target;
            input.value = phoneMask(input.value);
        }

        function phoneMask(value) {
            if (!value) return "";
            value = value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, "($1) $2");
            value = value.replace(/(\d{5})(\d)/, "$1-$2");
            return value;
        }
    </script>
</body>

</html>