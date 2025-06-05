<?php
session_start(['cookie_path' => '/Projetos/nexcommerce']);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

include '../db_connect.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("<script>console.log('Erro: Falha na conexão com o banco: " . addslashes($conn->connect_error ?? 'Desconhecido') . "');</script>");
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_produto = filter_input(INPUT_POST, 'delete_id', FILTER_SANITIZE_NUMBER_INT);
    $id_usuario = $_SESSION['id_usuario'];

    // Verificar se o produto pertence ao usuário
    $stmt = $conn->prepare("SELECT id_produto FROM produto WHERE id_produto = ? AND id_usuario_cadastro = ?");
    $stmt->bind_param("ii", $id_produto, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $conn->begin_transaction();
        try {
            // Excluir atributos do produto
            $stmt = $conn->prepare("DELETE FROM produto_atributos WHERE id_produto = ?");
            $stmt->bind_param("i", $id_produto);
            $stmt->execute();
            
            // Excluir estoque do produto
            $stmt = $conn->prepare("DELETE FROM estoque WHERE id_produto = ?");
            $stmt->bind_param("i", $id_produto);
            $stmt->execute();
            
            // Excluir produto
            $stmt = $conn->prepare("DELETE FROM produto WHERE id_produto = ?");
            $stmt->bind_param("i", $id_produto);
            $stmt->execute();
            
            $conn->commit();
            $message = "Produto excluído com sucesso!";
            $message_type = "success";
            echo "<script>console.log('Sucesso: Produto ID $id_produto excluído');</script>";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Erro ao excluir produto: " . $e->getMessage();
            $message_type = "error";
            echo "<script>console.log('Erro ao excluir produto: " . addslashes($e->getMessage()) . "');</script>";
        }
    } else {
        $message = "Produto não encontrado ou você não tem permissão para excluí-lo.";
        $message_type = "error";
        echo "<script>console.log('Erro: Produto ID $id_produto não encontrado ou sem permissão');</script>";
    }
    $stmt->close();
}

// Buscar produtos do usuário
$id_usuario = $_SESSION['id_usuario'];
$stmt = $conn->prepare("
    SELECT p.id_produto, p.titulo_produto, p.preco_produto, p.descricao_produto, p.image_produto, c.nome_categoria, m.nome_marca, e.quantidade
    FROM produto p
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
    LEFT JOIN marcas m ON p.id_marca = m.id_marca
    LEFT JOIN estoque e ON p.id_produto = e.id_produto
    WHERE p.id_usuario_cadastro = ?
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$produtos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Produtos</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 2rem;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: 0 auto;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        th, td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .product-image {
            max-width: 100px;
            height: auto;
            display: block;
        }
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            display: inline-block;
            margin-right: 0.5rem;
        }
        .edit-btn {
            background-color: #28a745;
        }
        .edit-btn:hover {
            background-color: #218838;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .back-btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.8rem 1.5rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            text-align: center;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
        @media (max-width: 600px) {
            table {
                font-size: 0.9rem;
            }
            .action-btn {
                padding: 0.4rem 0.8rem;
            }
            .product-image {
                max-width: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Meus Produtos</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (empty($produtos)): ?>
            <p>Nenhum produto cadastrado.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Título</th>
                        <th>Preço (R$)</th>
                        <th>Categoria</th>
                        <th>Marca</th>
                        <th>Quantidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto): ?>
                        <tr>
                            <td>
                                <?php if (!empty($produto['image_produto'])): ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($produto['image_produto']); ?>" alt="Imagem do produto" class="product-image">
                                <?php else: ?>
                                    Sem imagem
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($produto['titulo_produto']); ?></td>
                            <td><?php echo number_format($produto['preco_produto'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($produto['nome_categoria'] ?? 'Sem categoria'); ?></td>
                            <td><?php echo htmlspecialchars($produto['nome_marca'] ?? 'Sem marca'); ?></td>
                            <td><?php echo $produto['quantidade'] ?? 0; ?></td>
                            <td>
                                <a href="editar_produto.php?id=<?php echo $produto['id_produto']; ?>" class="action-btn edit-btn">Editar</a>
                                <form action="mostrar_produtos.php" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $produto['id_produto']; ?>">
                                    <button type="submit" class="action-btn delete-btn">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="dashboard_admin.php" class="back-btn">Voltar ao Dashboard</a>
    </div>
    <script>
        console.log('Página mostrar_produtos.php carregada');
    </script>
</body>
</html>