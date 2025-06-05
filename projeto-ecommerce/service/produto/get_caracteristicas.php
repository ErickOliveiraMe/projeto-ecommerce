<?php
header('Content-Type: application/json');
include '../db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo json_encode(['error' => 'Falha na conexão com o banco de dados']);
    exit;
}

$id_categoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;

if ($id_categoria > 0) {
    $stmt = $conn->prepare("SELECT c.id_caracteristica, c.nome FROM caracteristicas c 
                            INNER JOIN produto_caracteristica pc ON c.id_caracteristica = pc.id_caracteristica 
                            INNER JOIN produto p ON pc.id_produto = p.id_produto 
                            WHERE p.id_categoria = ? GROUP BY c.id_caracteristica");
    $stmt->bind_param("i", $id_categoria);
    $stmt->execute();
    $result = $stmt->get_result();
    $caracteristicas = [];
    while ($row = $result->fetch_assoc()) {
        $stmt_valores = $conn->prepare("SELECT id_valor, valor FROM valores_caracteristica WHERE id_caracteristica = ?");
        $stmt_valores->bind_param("i", $row['id_caracteristica']);
        $stmt_valores->execute();
        $valores_result = $stmt_valores->get_result();
        $valores = [];
        while ($valor = $valores_result->fetch_assoc()) {
            $valores[] = $valor;
        }
        $stmt_valores->close();
        $row['valores'] = $valores;
        $caracteristicas[] = $row;
    }
    $stmt->close();
    echo json_encode($caracteristicas);
} else {
    echo json_encode([]);
}
$conn->close();
?>