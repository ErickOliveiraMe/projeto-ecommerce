<?php
header('Content-Type: application/json');
include '../db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo json_encode(['error' => 'Falha na conexão com o banco de dados']);
    exit;
}

$id_categoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;

if ($id_categoria > 0) {
    $stmt = $conn->prepare("SELECT a.id_atributo, a.nome_atributo FROM atributos a 
                            INNER JOIN categoria_atributos ca ON a.id_atributo = ca.id_atributo 
                            WHERE ca.id_categoria = ?");
    $stmt->bind_param("i", $id_categoria);
    $stmt->execute();
    $result = $stmt->get_result();
    $atributos = [];
    while ($row = $result->fetch_assoc()) {
        $stmt_valores = $conn->prepare("SELECT id_valor, valor FROM valores_atributos WHERE id_atributo = ?");
        $stmt_valores->bind_param("i", $row['id_atributo']);
        $stmt_valores->execute();
        $valores_result = $stmt_valores->get_result();
        $valores = [];
        while ($valor = $valores_result->fetch_assoc()) {
            $valores[] = $valor;
        }
        $stmt_valores->close();
        $row['valores'] = $valores;
        $atributos[] = $row;
    }
    $stmt->close();
    echo json_encode($atributos);
} else {
    echo json_encode([]);
}
$conn->close();
?>