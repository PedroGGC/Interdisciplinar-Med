<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InterMed — Acesso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        /* Background image se existir, tratado como overlay */
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
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 400px;
            padding: 24px 16px;
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

        /* Brand logo acima do card */
        .auth-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }

        .auth-brand .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px rgba(26, 121, 64, 0.5);
        }

        .auth-brand .brand-icon i {
            font-size: 22px;
            color: white;
        }

        .auth-brand .brand-text {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 22px;
            color: var(--text);
            letter-spacing: -0.5px;
        }

        .auth-brand .brand-sub {
            font-size: 11px;
            color: var(--text-muted);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            display: block;
            margin-top: 1px;
        }

        /* Card de login */
        .auth-card {
            width: 100%;
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
            margin-bottom: 6px;
        }

        .auth-card .auth-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        /* Form group */
        .auth-form-group {
            margin-bottom: 16px;
        }

        .auth-form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .auth-input {
            width: 100%;
            padding: 10px 14px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            outline: none;
        }

        .auth-input::placeholder {
            color: var(--text-muted);
        }

        .auth-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        /* Divider */
        .auth-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 20px 0;
        }

        /* Botões */
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
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .btn-auth-secondary:hover {
            background: var(--surface-2);
            color: var(--text);
            border-color: var(--text-muted);
        }

        /* Footer do card */
        .auth-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <!-- Brand -->
        <div class="auth-brand">
            <div class="brand-icon">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <div>
                <span class="brand-text">InterMed</span>
                <span class="brand-sub">Sistema Medicina</span>
            </div>
        </div>

        <!-- Login Card -->
        <div class="auth-card">
            <h2>Bem-vindo</h2>
            <p class="auth-subtitle">Entre com suas credenciais para continuar</p>

            <form action="cadastro_e_login/login.php" method="POST">
                <div class="auth-form-group">
                    <label for="login">Login</label>
                    <input type="text" id="login" name="login" class="auth-input" placeholder="Seu login" required autocomplete="username">
                </div>
                <div class="auth-form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="auth-input" placeholder="••••••••" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-auth-primary" style="margin-top:8px;">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                </button>
            </form>

            <hr class="auth-divider">

            <button class="btn-auth-secondary" onclick="location.href='cadastro_e_login/cadastro.php'">
                <i class="bi bi-person-plus me-1"></i> Criar conta
            </button>
        </div>

        <div class="auth-footer">
            Sistema de Gestão em Medicina © <?php echo date('Y'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
if (isset($_GET['alert']) && $_GET['alert'] == '1') {
    echo "<script>
        Swal.fire({
            position: 'top',
            title: 'Cadastrado!',
            text: 'Usuário cadastrado com sucesso.',
            icon: 'success',
            confirmButtonText: 'Ok',
            background: '#161b22',
            color: '#e6edf3',
            confirmButtonColor: '#1a7940'
        }).then(function() {
            window.location.href = window.location.pathname;
        });
    </script>";
}

if (isset($_GET['alert']) && $_GET['alert'] == '2') {
    echo "<script>
        Swal.fire({
            position: 'top',
            text: 'Login ou Senha incorreto(s).',
            icon: 'error',
            confirmButtonText: 'Ok',
            background: '#161b22',
            color: '#e6edf3',
            confirmButtonColor: '#1a7940'
        }).then(function() {
            window.location.href = window.location.pathname;
        });
    </script>";
}
?>