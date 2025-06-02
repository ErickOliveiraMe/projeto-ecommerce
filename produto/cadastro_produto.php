<DOCUMENT filename="cadastro_produto.php">
<?php
session_start(['cookie_path' => '/Projetos/nexcommerce']);

if (!isset($_SESSION['id_usuario'])) {
    $message = "Você precisa estar logado para cadastrar um produto.";
    $message_type = "error";
    echo "<script>console.log('Erro: Usuário não logado');</script>";
} else {
    include '../db_connect.php';

    if (!isset($conn) || !($conn instanceof mysqli)) {
        $message = "Erro: Falha na conexão com o banco de dados.";
        $message_type = "error";
        echo "<script>console.log('Erro: Falha na conexão com o banco: " . addslashes($conn->connect_error ?? 'Desconhecido') . "');</script>";
    } else {
        $message = '';
        $message_type = '';

        // Verificar se o arquivo de destino existe (para depuração)
        if (!file_exists(__FILE__)) {
            $message = "Erro: O arquivo cadastro_produto.php não foi encontrado.";
            $message_type = "error";
            echo "<script>console.log('Erro: Arquivo cadastro_produto.php não encontrado');</script>";
        }

        $categorias = $conn->query("SELECT id_categoria, nome_categoria FROM categorias");
        if (!$categorias) {
            $message = "Erro ao carregar categorias: " . $conn->error;
            $message_type = "error";
            echo "<script>console.log('Erro ao carregar categorias: " . addslashes($conn->error) . "');</script>";
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            echo "<script>console.log('POST recebido em cadastro_produto.php');</script>";

            $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
            $preco = filter_input(INPUT_POST, 'preco', FILTER_SANITIZE_NUMBER_INT);
            $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
            $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_SANITIZE_NUMBER_INT);
            $id_marca = filter_input(INPUT_POST, 'id_marca', FILTER_SANITIZE_NUMBER_INT);
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_SANITIZE_NUMBER_INT);
            $atributos_selecionados = isset($_POST['atributos']) ? $_POST['atributos'] : [];
            $id_usuario_cadastro = $_SESSION['id_usuario'];

            $errors = [];

            if (empty($titulo)) {
                $errors[] = "O título do produto é obrigatório.";
                echo "<script>console.log('Erro: Título vazio');</script>";
            }
            if ($preco <= 0) {
                $errors[] = "O preço deve ser maior que zero.";
                echo "<script>console.log('Erro: Preço inválido');</script>";
            }
            if (empty($descricao)) {
                $errors[] = "A descrição do produto é obrigatória.";
                echo "<script>console.log('Erro: Descrição vazia');</script>";
            }
            if ($quantidade < 0) {
                $errors[] = "A quantidade em estoque não pode ser negativa.";
                echo "<script>console.log('Erro: Quantidade negativa');</script>";
            }
            if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] == UPLOAD_ERR_NO_FILE) {
                $errors[] = "A imagem do produto é obrigatória.";
                echo "<script>console.log('Erro: Imagem não enviada');</script>";
            } elseif ($_FILES['imagem']['error'] != UPLOAD_ERR_OK) {
                $errors[] = "Erro ao fazer upload da imagem.";
                echo "<script>console.log('Erro: Falha no upload da imagem, código: " . $_FILES['imagem']['error'] . "');</script>";
            } else {
                $imagem = $_FILES['imagem'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($imagem['type'], $allowed_types)) {
                    $errors[] = "A imagem deve ser JPEG, PNG ou GIF.";
                    echo "<script>console.log('Erro: Tipo de imagem inválido');</script>";
                }
                if ($imagem['size'] > 5 * 1024 * 1024) {
                    $errors[] = "A imagem não pode ser maior que 5MB.";
                    echo "<script>console.log('Erro: Imagem maior que 5MB');</script>";
                }
            }

            $stmt = $conn->prepare("SELECT id_categoria FROM categorias WHERE id_categoria = ?");
            $stmt->bind_param("i", $id_categoria);
            $stmt->execute();
            if ($stmt->get_result()->num_rows == 0) {
                $errors[] = "Categoria inválida.";
                echo "<script>console.log('Erro: Categoria inválida');</script>";
            }
            $stmt->close();

            $stmt = $conn->prepare("SELECT id_marca FROM categoria_marcas WHERE id_marca = ? AND id_categoria = ?");
            $stmt->bind_param("ii", $id_marca, $id_categoria);
            $stmt->execute();
            if ($stmt->get_result()->num_rows == 0) {
                $errors[] = "Marca inválida para a categoria selecionada.";
                echo "<script>console.log('Erro: Marca inválida para a categoria');</script>";
            }
            $stmt->close();

            if (empty($errors)) {
                $imagem_content = file_get_contents($imagem['tmp_name']);
                $conn->begin_transaction();

                try {
                    $stmt = $conn->prepare("INSERT INTO produto (titulo_produto, preco_produto, descricao_produto, image_produto, id_categoria, id_marca, id_usuario_cadastro) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sisbiii", $titulo, $preco, $descricao, $imagem_content, $id_categoria, $id_marca, $id_usuario_cadastro);
                    $stmt->send_long_data(3, $imagem_content);
                    $stmt->execute();
                    $id_produto = $conn->insert_id;
                    $stmt->close();
                    echo "<script>console.log('Produto inserido, ID: $id_produto');</script>";

                    $stmt = $conn->prepare("INSERT INTO estoque (id_produto, quantidade) VALUES (?, ?)");
                    $stmt->bind_param("ii", $id_produto, $quantidade);
                    $stmt->execute();
                    $stmt->close();
                    echo "<script>console.log('Estoque inserido para produto ID: $id_produto');</script>";

                    foreach ($atributos_selecionados as $id_atributo => $id_valor) {
                        if (!empty($id_valor)) {
                            $stmt = $conn->prepare("INSERT INTO produto_atributos (id_produto, id_atributo, id_valor) VALUES (?, ?, ?)");
                            $stmt->bind_param("iii", $id_produto, $id_atributo, $id_valor);
                            $stmt->execute();
                            $stmt->close();
                            echo "<script>console.log('Atributo inserido: id_atributo=$id_atributo, id_valor=$id_valor');</script>";
                        }
                    }

                    $conn->commit();
                    $message = "Produto cadastrado com sucesso!";
                    $message_type = "success";
                    echo "<script>console.log('Sucesso: Produto cadastrado');</script>";
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "Erro ao cadastrar produto: " . $e->getMessage();
                    $message_type = "error";
                    echo "<script>console.log('Erro ao cadastrar produto: " . addslashes($e->getMessage()) . "');</script>";
                }
                $conn->close();
            } else {
                $message = implode("<br>", $errors);
                $message_type = "error";
                echo "<script>console.log('Erros no formulário: " . addslashes(implode(", ", $errors)) . "');</script>";
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
            max-width: 500px;
            width: 100%;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        label {
            display: block;
            font-weight: 500;
            color: #555;
            margin-bottom: 0.5rem;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 0.8rem;
            cursor: pointer;
            font-weight: 500;
            border-radius: 5px;
            transition: background-color 0.3s;
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
        a {
            display: block;
            text-align: center;
            color: #007bff;
            text-decoration: none;
            margin-top: 1rem;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Cadastro de Produto</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (empty($message) || $message_type != "success"): ?>
            <form id="productForm" action="cadastro_produto.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título do Produto:</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>
                <div class="form-group">
                    <label for="preco">Preço (R$):</label>
                    <input type="number" id="preco" name="preco" required min="1">
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao" required></textarea>
                </div>
                <div class="form-group">
                    <label for="imagem">Imagem do Produto:</label>
                    <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif" required>
                </div>
                <div class="form-group">
                    <label for="id_categoria">Categoria:</label>
                    <select id="id_categoria" name="id_categoria" required>
                        <option value="">Selecione uma categoria</option>
                        <?php while ($row = $categorias->fetch_assoc()): ?>
                            <option value="<?php echo $row['id_categoria']; ?>"><?php echo htmlspecialchars($row['nome_categoria']); ?></option>
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
                    <input type="number" id="quantidade" name="quantidade" required min="0">
                </div>
                <div class="form-group" id="atributos-container">
                    <label>Atributos:</label>
                    <p>Selecione uma categoria para carregar os atributos.</p>
                </div>
                <input type="submit" value="Cadastrar">
            </form>
            <a href="../usuario/dashboard_user.php">Voltar ao Dashboard</a>
        <?php else: ?>
            <a href="../usuario/dashboard_user.php">Voltar ao Dashboard</a>
        <?php endif; ?>
    </div>
    <script>
        const categoriaSelect = document.getElementById('id_categoria');
        const marcaSelect = document.getElementById('id_marca');
        const atributosContainer = document.getElementById('atributos-container');

        categoriaSelect.addEventListener('change', function() {
            const idCategoria = this.value;

            console.log('Categoria selecionada: ' + idCategoria);

            marcaSelect.innerHTML = '<option value="">Carregando...</option>';
            atributosContainer.innerHTML = '<p>Carregando...</p>';

            if (idCategoria) {
                // Carregar marcas
                fetch(`./get_marcas.php?id_categoria=${idCategoria}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro na requisição de marcas: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Dados de marcas recebidos:', data);
                        marcaSelect.innerHTML = '<option value="">Selecione uma marca</option>';
                        if (data.error) {
                            marcaSelect.innerHTML += `<option value="">Erro: ${data.error}</option>`;
                            console.log('Erro retornado pela API de marcas:', data.error);
                        } else if (data.length === 0) {
                            marcaSelect.innerHTML += '<option value="">Nenhuma marca disponível</option>';
                            console.log('Nenhuma marca encontrada para a categoria');
                        } else {
                            data.forEach(marca => {
                                const option = document.createElement('option');
                                option.value = marca.id_marca;
                                option.textContent = marca.nome_marca;
                                marcaSelect.appendChild(option);
                            });
                            console.log('Marcas carregadas com sucesso');
                        }
                    })
                    .catch(error => {
                        marcaSelect.innerHTML = '<option value="">Erro ao carregar marcas</option>';
                        console.error('Erro ao carregar marcas:', error);
                    });

                // Carregar atributos
                fetch(`./get_atributos.php?id_categoria=${idCategoria}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro na requisição de atributos: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Dados de atributos recebidos:', data);
                        atributosContainer.innerHTML = '';
                        if (data.error) {
                            atributosContainer.innerHTML = `<p>Erro: ${data.error}</p>`;
                            console.log('Erro retornado pela API de atributos:', data.error);
                        } else if (data.length === 0) {
                            atributosContainer.innerHTML = '<p>Nenhum atributo disponível</p>';
                            console.log('Nenhum atributo encontrado para a categoria');
                        } else {
                            data.forEach(atributo => {
                                const div = document.createElement('div');
                                div.className = 'form-group';
                                div.innerHTML = `
                                    <label for="atributos_${atributo.id_atributo}">${atributo.nome_atributo}:</label>
                                    <select id="atributos_${atributo.id_atributo}" name="atributos[${atributo.id_atributo}]">
                                        <option value="">Selecione</option>
                                        ${atributo.valores.map(valor => `
                                            <option value="${valor.id_valor}">${valor.valor}</option>
                                        `).join('')}
                                    </select>
                                `;
                                atributosContainer.appendChild(div);
                            });
                            console.log('Atributos carregados com sucesso');
                        }
                    })
                    .catch(error => {
                        atributosContainer.innerHTML = '<p>Erro ao carregar atributos</p>';
                        console.error('Erro ao carregar atributos:', error);
                    });
            } else {
                marcaSelect.innerHTML = '<option value="">Selecione uma categoria primeiro</option>';
                atributosContainer.innerHTML = '<p>Selecione uma categoria para carregar os atributos.</p>';
                console.log('Nenhuma categoria selecionada');
            }
        });
    </script>
</body>
</html>
</DOCUMENT>