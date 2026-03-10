<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="container mt-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Gerar Novos Rodízios</h2>
            <p class="text-muted mb-0">Configure os períodos e a distribuição automática de grupos</p>
        </div>
        <button onclick="history.back()" class="btn btn-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </button>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="text-white mb-4"><i class="bi bi-layers me-2"></i>Configuração Base</h6>
                    
                    <div class="mb-4">
                        <label for="selectPeriodo" class="form-label text-muted small fw-bold text-uppercase">Período Acadêmico</label>
                        <select name="periodo" class="form-select" id="selectPeriodo" onchange="loadModulos()">
                            <option value="">Selecione...</option>
                            <option value="9">9º Período</option>
                            <option value="10">10º Período</option>
                            <option value="11">11º Período</option>
                            <option value="12">12º Período</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label text-muted small fw-bold text-uppercase">Módulos Identificados</label>
                        <div id="modulosContainer" class="p-3 rounded bg-surface-2 border border-border min-height-100">
                            <p class="text-muted small mb-0">Selecione o período para listar os módulos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="text-white mb-4"><i class="bi bi-calendar-event me-2"></i>Definição de Cronograma</h6>
                    
                    <form id="rodizioForm" method="POST" action="processar-rodizios.php">
                        <input type="hidden" name="periodo" value="" id="hiddenPeriodo">
                        
                        <?php for($i=1; $i<=3; $i++): ?>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Rodízio <?= $i ?></label>
                            <div class="row g-2 align-items-center">
                                <div class="col">
                                    <div class="input-group">
                                        <span class="input-group-text bg-surface-2 border-border text-muted small px-2">DE</span>
                                        <input type="date" class="form-control font-monospace" style="font-variant-numeric: tabular-nums; letter-spacing: -0.5px; font-size: 0.9rem;" id="inicio<?= $i ?>" name="inicio<?= $i ?>" required>
                                    </div>
                                </div>
                                <div class="col-auto text-muted">
                                    <i class="bi bi-arrow-right"></i>
                                </div>
                                <div class="col">
                                    <div class="input-group">
                                        <span class="input-group-text bg-surface-2 border-border text-muted small px-2">ATÉ</span>
                                        <input type="date" class="form-control font-monospace" style="font-variant-numeric: tabular-nums; letter-spacing: -0.5px; font-size: 0.9rem;" id="fim<?= $i ?>" name="fim<?= $i ?>" required>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="modulo<?= $i ?>" name="modulo<?= $i ?>">
                        </div>
                        <?php endfor; ?>

                        <div class="form-check form-switch mt-4 p-0 ps-5">
                            <input class="form-check-input ms-n5" type="checkbox" name="no_fill_groups" id="no_fill_groups" style="width: 2.5em; height: 1.25em;">
                            <label class="form-check-label text-white ms-2" for="no_fill_groups">
                                Não preencher grupos com alunos automaticamente
                                <small class="d-block text-muted">Marque se deseja apenas criar a estrutura sem alocar estudantes.</small>
                            </label>
                        </div>

                        <div class="mt-5 d-flex gap-2">
                            <button type="button" class="btn btn-primary px-5 py-2" onclick="gerarRodizios()">
                                <i class="bi bi-check-circle me-2"></i> Confirmar e Gerar Rodízios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadModulos() {
        var periodo = document.getElementById('selectPeriodo').value;
        const container = document.getElementById('modulosContainer');
        
        if (periodo !== '') {
            container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-accent-light" role="status"></div></div>';
            
            fetch(`modulos-rodizios.php?periodo=${periodo}`)
                .then(response => response.text())
                .then(data => {
                    container.innerHTML = data;
                })
                .catch(error => {
                    console.error('Erro ao carregar módulos:', error);
                    container.innerHTML = "<p class='text-danger small mb-0'>Erro ao carregar módulos</p>";
                });
        } else {
            container.innerHTML = "<p class='text-muted small mb-0'>Selecione um período primeiro</p>";
        }
    }

    function gerarRodizios() {
        var inicio1 = document.getElementById('inicio1').value;
        var fim1 = document.getElementById('fim1').value;
        var inicio2 = document.getElementById('inicio2').value;
        var fim2 = document.getElementById('fim2').value;
        var inicio3 = document.getElementById('inicio3').value;
        var fim3 = document.getElementById('fim3').value;
        var periodo = document.getElementById('selectPeriodo').value;
        
        if(!periodo) {
            alert("Selecione o período acadêmico.");
            return false;
        }
        
        document.getElementById('hiddenPeriodo').value = periodo;

        if (new Date(fim1) <= new Date(inicio1)) {
            alert("A data de término do Rodízio 1 deve ser após a data de início.");
            return false;
        }
        if (new Date(inicio2) <= new Date(fim1) || new Date(fim2) <= new Date(inicio2)) {
            alert("O Rodízio 2 deve começar após o término do Rodízio 1 e a data de término deve ser após a data de início.");
            return false;
        }
        if (new Date(inicio3) <= new Date(fim2) || new Date(fim3) <= new Date(inicio3)) {
            alert("O Rodízio 3 deve começar após o término do Rodízio 2 e a data de término deve ser após a data de início.");
            return false;
        }

        var modulos = document.querySelectorAll('#modulosContainer [data-idmodulo]');
        if (modulos.length < 3) {
            alert("É necessário ter ao menos 3 módulos disponíveis para o período selecionado.");
            return false;
        }

        var modulosArray = Array.from(modulos).map(modulo => modulo.dataset.idmodulo);

        // Atribuição simplificada (o backend gerencia a rotação real)
        document.getElementById('modulo1').value = modulosArray[0];
        document.getElementById('modulo2').value = modulosArray[1];
        document.getElementById('modulo3').value = modulosArray[2];

        document.getElementById('rodizioForm').submit();
    }
</script>