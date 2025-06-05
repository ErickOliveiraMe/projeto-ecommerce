<?php
session_start(['cookie_path' => '/Projetos/nexcommerce']);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

include '../db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    $message = "Erro: Falha na conexão com o banco de dados.";
    $message_type = "error";
    echo "<script>console.log('Erro: Falha na conexão com o banco: " . addslashes($conn->connect_error ?? 'Desconhecido') . "');</script>";
} else {
    $message = '';
    $message_type = '';

    // Excluir produto
    if (isset($_GET['delete_id'])) {
        $delete_id = filter_input(INPUT_GET, 'delete_id', FILTER_SANITIZE_NUMBER_INT);
        $id_usuario = $_SESSION['id_usuario'];

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("DELETE FROM produto_atributos WHERE id_produto = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM estoque WHERE id_produto = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM produto WHERE id_produto = ? AND id_usuario_cadastro = ?");
            $stmt->bind_param("ii", $delete_id, $id_usuario);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $message = "Produto excluído com sucesso!";
                $message_type = "success";
                echo "<script>console.log('Produto ID $delete_id excluído');</script>";
            } else {
                $message = "Erro: Produto não encontrado ou você não tem permissão.";
                $message_type = "error";
                echo "<script>console.log('Erro: Produto ID $delete_id não encontrado ou sem permissão');</script>";
            }
            $stmt->close();
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Erro ao excluir produto: " . $e->getMessage();
            $message_type = "error";
            echo "<script>console.log('Erro ao excluir produto: " . addslashes($e->getMessage()) . "');</script>";
        }
    }

    // Atualizar produto
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
        $id_produto = filter_input(INPUT_POST, 'edit_id', FILTER_SANITIZE_NUMBER_INT);
        $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
        $preco = filter_input(INPUT_POST, 'preco', FILTER_SANITIZE_NUMBER_INT);
        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
        $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_SANITIZE_NUMBER_INT);
        $id_marca = filter_input(INPUT_POST, 'id_marca', FILTER_SANITIZE_NUMBER_INT);
        $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_SANITIZE_NUMBER_INT);
        $id_usuario_cadastro = $_SESSION['id_usuario'];

        $errors = [];

        if (empty($titulo)) $errors[] = "O título do produto é obrigatório.";
        if ($preco <= 0) $errors[] = "O preço deve ser maior que zero.";
        if (empty($descricao)) $errors[] = "A descrição do produto é obrigatória.";
        if ($quantidade < 0) $errors[] = "A quantidade em estoque não pode ser negativa.";

        // Verificar imagem (opcional na edição)
        $imagem_content = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
            $imagem = $_FILES['imagem'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($imagem['type'], $allowed_types)) {
                $errors[] = "A imagem deve ser JPEG, PNG ou GIF.";
            } elseif ($imagem['size'] > 5 * 1024 * 1024) {
                $errors[] = "A imagem não pode ser maior que 5MB.";
            } else {
                $imagem_content = file_get_contents($imagem['tmp_name']);
            }
        }

        $stmt = $conn->prepare("SELECT id_categoria FROM categorias WHERE id_categoria = ?");
        $stmt->bind_param("i", $id_categoria);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $errors[] = "Categoria inválida.";
        }
        $stmt->close();

        $stmt = $conn->prepare("SELECT id_marca FROM categoria_marcas WHERE id_marca = ? AND id_categoria = ?");
        $stmt->bind_param("ii", $id_marca, $id_categoria);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $errors[] = "Marca inválida para a categoria selecionada.";
        }
        $stmt->close();

        if (empty($errors)) {
            $conn->begin_transaction();
            try {
                if ($imagem_content) {
                    $stmt = $conn->prepare("UPDATE produto SET titulo_produto = ?, preco_produto = ?, descricao_produto = ?, image_produto = ?, id_categoria = ?, id_marca = ? WHERE id_produto = ? AND id_usuario_cadastro = ?");
                    $stmt->bind_param("sisbiiii", $titulo, $preco, $descricao, $imagem_content, $id_categoria, $id_marca, $id_produto, $id_usuario_cadastro);
                    $stmt->send_long_data(3, $imagem_content);
                } else {
                    $stmt = $conn->prepare("UPDATE produto SET titulo_produto = ?, preco_produto = ?, descricao_produto = ?, id_categoria = ?, id_marca = ? WHERE id_produto = ? AND id_usuario_cadastro = ?");
                    $stmt->bind_param("sisiiii", $titulo, $preco, $descricao, $id_categoria, $id_marca, $id_produto, $id_usuario_cadastro);
                }
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $stmt->close();

                    $stmt = $conn->prepare("UPDATE estoque SET quantidade = ? WHERE id_produto = ?");
                    $stmt->bind_param("ii", $quantidade, $id_produto);
                    $stmt->execute();
                    $stmt->close();

                    $conn->commit();
                    $message = "Produto atualizado com sucesso!";
                    $message_type = "success";
                    echo "<script>console.log('Produto ID $id_produto atualizado');</script>";
                } else {
                    $conn->rollback();
                    $message = "Erro: Produto não encontrado ou você não tem permissão.";
                    $message_type = "error";
                    echo "<script>console.log('Erro: Produto ID $id_produto não encontrado ou sem permissão');</script>";
                }
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Erro ao atualizar produto: " . $e->getMessage();
                $message_type = "error";
                echo "<script>console.log('Erro ao atualizar produto: " . addslashes($e->getMessage()) . "');</script>";
            }
        } else {
            $message = implode("<br>", $errors);
            $message_type = "error";
            echo "<script>console.log('Erros no formulário de edição: " . addslashes(implode(", ", $errors)) . "');</script>";
        }
    }

    // Listar produtos
    $id_usuario = $_SESSION['id_usuario'];
    $result = $conn->query("
        SELECT p.id_produto, p.titulo_produto, p.preco_produto, p.descricao_produto, p.image_produto, 
               c.nome_categoria, m.nome_marca, e.quantidade
        FROM produto p
        JOIN categorias c ON p.id_categoria = c.id_categoria
        JOIN marcas m ON p.id_marca = m.id_marca
        JOIN estoque e ON p.id_produto = e.id_produto
        WHERE p.id_usuario_cadastro = $id_usuario
    ");
    if (!$result) {
        $message = "Erro ao carregar produtos: " . $conn->error;
        $message_type = "error";
        echo "<script>console.log('Erro ao carregar produtos: " . addslashes($conn->error) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Usuário</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 2rem;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .nav-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .nav-links a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        img {
            max-width: 100px;
            height: auto;
        }
        .actions a {
            margin-right: 1rem;
            color: #007bff;
            text-decoration: none;
        }
        .actions a.delete {
            color: #dc3545;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            max-width: 500px;
            width: 100%;
        }
        .modal-content h3 {
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            font-weight: 500;
            color: #555;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 0.8rem;
            cursor: pointer;
            border-radius: 5px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Dashboard do Usuário</h2>
        <div class="nav-links">
            <a href="../produto/cadastro_produto.php">Cadastrar Produto</a>
            <a href="#" onclick="document.getElementById('products-section').style.display='block';return false;">Mostrar Produtos</a>
            <a href="../logout.php">Sair</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div id="products-section" style="display:none;">
            <h3>Meus Produtos</h3>
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Preço (R$)</th>
                            <th>Categoria</th>
                            <th>Marca</th>
                            <th>Quantidade</th>
                            <th>Imagem</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['titulo_produto']); ?></td>
                                <td><?php echo number_format($row['preco_produto'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($row['nome_categoria']); ?></td>
                                <td><?php echo htmlspecialchars($row['nome_marca']); ?></td>
                                <td><?php echo $row['quantidade']; ?></td>
                                <td><img src="data:image/jpeg;base64,<?php echo base64_encode($row['image_produto']); ?>" alt="Produto"></td>
                                <td class="actions">
                                    <a href="#" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Editar</a>
                                    <a href="?delete_id=<?php echo $row['id_produto']; ?>" class="delete" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Nenhum produto cadastrado.</p>
            <?php endif; ?>
        </div>

        <!-- Modal para edição -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <h3>Editar Produto</h3>
                <form id="editForm" action="dashboard_user.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_titulo">Título do Produto:</label>
                        <input type="text" id="edit_titulo" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_preco">Preço (R$):</label>
                        <input type="number" id="edit_preco" name="preco" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="edit_descricao">Descrição:</label>
                        <textarea id="edit_descricao" name="descricao" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_imagem">Imagem do Produto (opcional):</label>
                        <input type="file" id="edit_imagem" name="imagem" accept="image/jpeg,image/png,image/gif">
                    </div>
                    <div class="form-group">
                        <label for="edit_id_categoria">Categoria:</label>
                        <select id="edit_id_categoria" name="id_categoria" required>
                            <option value="">Selecione uma categoria</option>
                            <?php
                            $categorias = $conn->query("SELECT id_categoria, nome_categoria FROM categorias");
                            while ($cat = $categorias->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>"><?php echo htmlspecialchars($cat['nome_categoria']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_id_marca">Marca:</label>
                        <select id="edit_id_marca" name="id_marca" required>
                            <option value="">Selecione uma categoria primeiro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_quantidade">Quantidade em Estoque:</label>
                        <input type="number" id="edit_quantidade" name="quantidade" required min="0">
                    </div>
                    <input type="submit" value="Salvar Alterações">
                </form>
                <button onclick="document.getElementById('editModal').style.display='none'">Fechar</button>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(product) {
            console.log('Abrindo modal de edição para produto:', product);
            document.getElementById('edit_id').value = product.id_produto;
            document.getElementById('edit_titulo').value = product.titulo_produto;
            document.getElementById('edit_preco').value = product.preco_produto;
            document.getElementById('edit_descricao').value = product.descricao_produto;
            document.getElementById('edit_id_categoria').value = product.id_categoria;
            document.getElementById('edit_quantidade').value = product.quantidade;

            const marcaSelect = document.getElementById('edit_id_marca');
            marcaSelect.innerHTML = '<option value="">Carregando...</option>';

            fetch(`../produto/get_marcas.php?id_categoria=${product.id_categoria}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na requisição de marcas: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Marcas recebidas para edição:', data);
                    marcaSelect.innerHTML = '<option value="">Selecione uma marca</option>';
                    if (data.error) {
                        marcaSelect.innerHTML += `<option value="">Erro: ${data.error}</option>`;
                    } else if (data.length === 0) {
                        marcaSelect.innerHTML += '<option value="">Nenhuma marca disponível</option>';
                    } else {
                        data.forEach(marca => {
                            const option = document.createElement('option');
                            option.value = marca.id_marca;
                            option.textContent = marca.nome_marca;
                            if (marca.id_marca == product.id_marca) option.selected = true;
                            marcaSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar marcas para edição:', error);
                    marcaSelect.innerHTML = '<option value="">Erro ao carregar marcas</option>';
                });

            document.getElementById('editModal').style.display = 'flex';
        }

        // Atualizar marcas quando a categoria mudar
        document.getElementById('edit_id_categoria').addEventListener('change', function() {
            const idCategoria = this.value;
            console.log('Categoria de edição selecionada: ' + idCategoria);
            const marcaSelect = document.getElementById('edit_id_marca');
            marcaSelect.innerHTML = '<option value="">Carregando...</option>';

            if (idCategoria) {
                fetch(`../produto/get_marcas.php?id_categoria=${idCategoria}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro na requisição de marcas: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Marcas recebidas:', data);
                        marcaSelect.innerHTML = '<option value="">Selecione uma marca</option>';
                        if (data.error) {
                            marcaSelect.innerHTML += `<option value="">Erro: ${data.error}</option>`;
                        } else if (data.length === 0) {
                            marcaSelect.innerHTML += '<option value="">Nenhuma marca disponível</option>';
                        } else {
                            data.forEach(marca => {
                                const option = document.createElement('option');
                                option.value = marca.id_marca;
                                option.textContent = marca.nome_marca;
                                marcaSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao carregar marcas:', error);
                        marcaSelect.innerHTML = '<option value="">Erro ao carregar marcas</option>';
                    });
            } else {
                marcaSelect.innerHTML = '<option value="">Selecione uma categoria primeiro</option>';
            }
        });
    </script>
</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>