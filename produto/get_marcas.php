<?php
header('Content-Type: application/json');
include '../db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo json_encode(['error' => 'Falha na conexão com o banco de dados']);
    exit;
}

$id_categoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;

if ($id_categoria > 0) {
    $stmt = $conn->prepare("SELECT m.id_marca, m.nome_marca FROM marcas m 
                            INNER JOIN categoria_marcas cm ON m.id_marca = cm.id_marca 
                            WHERE cm.id_categoria = ?");
    $stmt->bind_param("i", $id_categoria);
    $stmt->execute();
    $result = $stmt->get_result();
    $marcas = [];
    while ($row = $result->fetch_assoc()) {
        $marcas[] = $row;
    }
    $stmt->close();
    echo json_encode($marcas);
} else {
    echo json_encode([]);
}
$conn->close();
?>