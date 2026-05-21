<nav class="navbar" style="overflow: visible !important;">
    <div class="container-fluid" style="display:flex; align-items:center; justify-content:space-between; width:100%; padding:0; overflow:visible;">
        <a href="<?php echo BASE_URL; ?>/includes/redirecionar.php" class="nodec navbar-brand">
            <span class="brand-dot"></span>
            <span>
                InterMed
                <span class="brand-sub">Sistema Medicina</span>
            </span>
        </a>
        <div class="navbar-user d-flex align-items-center">
            <span class="navbar-text me-2">Olá, <strong><?php echo htmlspecialchars($_SESSION["nome"]); ?></strong></span>
            <div class="dropdown">
                <a class="text-decoration-none dropdown-toggle no-caret d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="color:var(--text-muted);">
                    <i class="bi bi-person-circle icone-perfil"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>/includes/perfil.php">
                            <i class="bi bi-person me-2"></i>Perfil
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider" style="border-color:var(--border);">
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>/cadastro_e_login/logout.php" style="color:var(--danger);">
                            <i class="bi bi-box-arrow-right me-2"></i>Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
