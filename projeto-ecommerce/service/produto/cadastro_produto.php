<?php
session_start(['cookie_path' => '/Projetos/nexcommerce']);

if (!isset($_SESSION['id_usuario'])) {
    $message = "Você precisa estar logado para cadastrar um produto.";
    $message_type = "error";
} else {
    include '../db_connect.php';

    if (!isset($conn) || !($conn instanceof mysqli)) {
        $message = "Erro: Falha na conexão com o banco de dados.";
        $message_type = "error";
    } else {
        $message = '';
        $message_type = '';

        $categorias = $conn->query("SELECT id_categoria, nome_categoria FROM categorias");
        if (!$categorias) {
            $message = "Erro ao carregar categorias: " . $conn->error;
            $message_type = "error";
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
            $preco = filter_input(INPUT_POST, 'preco', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
            $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_SANITIZE_NUMBER_INT);
            $id_marca = filter_input(INPUT_POST, 'id_marca', FILTER_SANITIZE_NUMBER_INT);
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_SANITIZE_NUMBER_INT);
            $atributos_selecionados = isset($_POST['atributos']) ? $_POST['atributos'] : [];
            $id_usuario_cadastro = $_SESSION['id_usuario'];

            $errors = [];

            if (empty($titulo)) {
                $errors[] = "O título do produto é obrigatório.";
            }
            if ($preco <= 0) {
                $errors[] = "O preço do produto deve ser maior que zero.";
            }
            if (empty($descricao)) {
                $errors[] = "A descrição do produto é obrigatória.";
            }
            if ($quantidade < 0) {
                $errors[] = "A quantidade em estoque não pode ser negativa.";
            }
            if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] == UPLOAD_ERR_NO_FILE) {
                $errors[] = "A imagem do produto é obrigatória.";
            } elseif ($_FILES['imagem']['error'] != UPLOAD_ERR_OK) {
                $errors[] = "Erro ao fazer upload da imagem.";
            } else {
                $imagem = $_FILES['imagem'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($imagem['type'], $allowed_types)) {
                    $errors[] = "A imagem deve ser JPEG, PNG ou GIF.";
                }
                if ($imagem['size'] > 5 * 1024 * 1024) {
                    $errors[] = "A imagem não pode ser maior que 5MB.";
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
                $imagem_content = file_get_contents($imagem['tmp_name']);
                $conn->begin_transaction();

                try {
                    $stmt = $conn->prepare("INSERT INTO produto (titulo_produto, preco_produto, descricao_produto, image_produto, id_categoria, id_marca, id_usuario_cadastro) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sdsbiii", $titulo, $preco, $descricao, $imagem_content, $id_categoria, $id_marca, $id_usuario_cadastro);
                    $stmt->send_long_data(3, $imagem_content);
                    $stmt->execute();
                    $id_produto = $conn->insert_id;
                    $stmt->close();

                    $stmt = $conn->prepare("INSERT INTO estoque (id_produto, quantidade) VALUES (?, ?)");
                    $stmt->bind_param("ii", $id_produto, $quantidade);
                    $stmt->execute();
                    $stmt->close();

                    foreach ($atributos_selecionados as $id_atributo => $valores) {
                        foreach ($valores as $id_valor) {
                            if (!empty($id_valor)) {
                                $stmt = $conn->prepare("INSERT INTO produto_atributos (id_produto, id_atributo, id_valor) VALUES (?, ?, ?)");
                                $stmt->bind_param("iii", $id_produto, $id_atributo, $id_valor);
                                $stmt->execute();
                                $stmt->close();
                            }
                        }
                    }

                    $conn->commit();
                    $message = "Produto cadastrado com sucesso!";
                    $message_type = "success";
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "Erro ao cadastrar produto: " . $e->getMessage();
                    $message_type = "error";
                }
                $conn->close();
            } else {
                $message = implode("<br>", $errors);
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produto</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }
        h2 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        input[type="text"], input[type="number"], select, textarea, input[type="file"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        .checkbox-group {
            margin-bottom: 1rem;
        }
        .checkbox-group label {
            display: block;
            margin-bottom: 0.3rem;
        }
        button {
            width: 100%;
            padding: 0.8rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 5px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        a {
            display: inline-block;
            margin-top: 1rem;
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function loadMarcas() {
            const idCategoria = document.getElementById('id_categoria').value;
            const marcaSelect = document.getElementById('id_marca');
            const atributosDiv = document.getElementById('atributos');

            // Load brands
            fetch(`get_marcas.php?id_categoria=${idCategoria}`)
                .then(response => response.json())
                .then(data => {
                    marcaSelect.innerHTML = '<option value="">Selecione uma marca</option>';
                    data.forEach(marca => {
                        marcaSelect.innerHTML += `<option value="${marca.id_marca}">${marca.nome_marca}</option>`;
                    });
                })
                .catch(() => {
                    marcaSelect.innerHTML = '<option value="">Erro ao carregar marcas</option>';
                });

            // Load attributes with checkboxes
            fetch(`get_atributos.php?id_categoria=${idCategoria}`)
                .then(response => response.json())
                .then(data => {
                    atributosDiv.innerHTML = '';
                    if (data.length === 0) {
                        atributosDiv.innerHTML = 'Nenhum atributo disponível para esta categoria.';
                        return;
                    }
                    data.forEach(atributo => {
                        let html = `<div class="form-group"><label>${atributo.nome_atributo}:</label><div class="checkbox-group">`;
                        atributo.valores.forEach(valor => {
                            html += `<label><input type="checkbox" name="atributos[${atributo.id_atributo}][]" value="${valor.id_valor}"> ${valor.valor}</label>`;
                        });
                        html += `</div></div>`;
                        atributosDiv.innerHTML += html;
                    });
                })
                .catch(() => {
                    atributosDiv.innerHTML = 'Erro ao carregar atributos.';
                });
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Cadastro de Produto</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título do Produto:</label>
                <input type="text" id="titulo" name="titulo" value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="preco">Preço (R$):</label>
                <input type="number" id="preco" name="preco" step="0.01" min="0" value="<?php echo isset($_POST['preco']) ? htmlspecialchars($_POST['preco']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao" required><?php echo isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="imagem">Imagem do Produto:</label>
                <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif" required>
            </div>
            <div class="form-group">
                <label for="id_categoria">Categoria:</label>
                <select id="id_categoria" name="id_categoria" onchange="loadMarcas()" required>
                    <option value="">Selecione uma categoria</option>
                    <?php while ($categoria = $categorias->fetch_assoc()): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>" <?php echo isset($_POST['id_categoria']) && $_POST['id_categoria'] == $categoria['id_categoria'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_marca">Marca:</label>
                <select id="id_marca" name="id_marca" required>
                    <option value="">Selecione uma categoria primeiro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="quantidade">Quantidade em Estoque:</label>
                <input type="number" id="quantidade" name="quantidade" min="0" value="<?php echo isset($_POST['quantidade']) ? htmlspecialchars($_POST['quantidade']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Atributos:</label>
                <div id="atributos">Selecione uma categoria para carregar os atributos.</div>
            </div>
            <button type="submit">Cadastrar Produto</button>
        </form>
        <a href="../dashboard_admin.php">Voltar ao Dashboard</a>
    </div>
</body>
</html>