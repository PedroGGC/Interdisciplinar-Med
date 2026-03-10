<?php
require_once '../../../cfg/config.php';

$idusuario = isset($_GET['idusuario']) ? intval($_GET['idusuario']) : null;
$periodo = isset($_GET['periodo']) ? intval($_GET['periodo']) : null;
$acao = isset($_GET['acao']) ? $_GET['acao'] : 'periodos';

if (!$idusuario) {
    echo json_encode(['error' => 'ID do usuário não fornecido']);
    exit;
}

if ($acao == 'periodos') {
    $sql = "SELECT DISTINCT m.periodo 
            FROM modulos_alunos ma 
            JOIN modulos m ON ma.idmodulo = m.idmodulo 
            WHERE ma.idusuario = ? 
            ORDER BY m.periodo ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $periodos = [];
    while ($row = $result->fetch_assoc()) {
        $periodos[] = $row['periodo'];
    }
    
    echo json_encode(['periodos' => $periodos]);
    exit;
}

if ($acao == 'modulos' && $periodo) {
    $sql = "SELECT m.idmodulo, m.nome_modulo, a.nota, a.data_avaliacao, a.idavaliacao 
            FROM modulos_alunos ma 
            JOIN modulos m ON ma.idmodulo = m.idmodulo 
            LEFT JOIN avaliacoes a ON (a.idmodulo = m.idmodulo AND a.idaluno = ma.idusuario) 
            WHERE ma.idusuario = ? AND m.periodo = ?
            ORDER BY m.nome_modulo ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idusuario, $periodo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $modulos = [];
    while ($row = $result->fetch_assoc()) {
        $modulos[] = [
            'idmodulo' => $row['idmodulo'],
            'nome_modulo' => $row['nome_modulo'],
            'nota' => $row['nota'],
            'data_avaliacao' => $row['data_avaliacao'],
            'idavaliacao' => $row['idavaliacao']
        ];
    }
    
    echo json_encode(['modulos' => $modulos]);
    exit;
}
?>
