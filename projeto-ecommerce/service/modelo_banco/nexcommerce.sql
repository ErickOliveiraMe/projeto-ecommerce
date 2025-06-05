-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 29/05/2025 às 04:17
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `nexcommerce`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `atributos`
--

CREATE TABLE `atributos` (
  `id_atributo` int(11) NOT NULL,
  `nome_atributo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `atributos`
--

INSERT INTO `atributos` (`id_atributo`, `nome_atributo`) VALUES
(1, 'Cor'),
(2, 'Tamanho'),
(3, 'Memória RAM'),
(4, 'Processador'),
(5, 'Armazenamento'),
(6, 'Tipo de Tela'),
(7, 'Taxa de Atualização'),
(8, 'Material'),
(9, 'Peso'),
(10, 'Garantia');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cadastro_user`
--

CREATE TABLE `cadastro_user` (
  `id_usuario` int(11) NOT NULL,
  `username` varchar(220) NOT NULL,
  `email` varchar(220) NOT NULL,
  `password` varchar(220) NOT NULL,
  `category` varchar(220) NOT NULL,
  `CPF` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `caracteristicas`
--

CREATE TABLE `caracteristicas` (
  `id_caracteristica` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `tipo` enum('armazenamento','processador','placa_video','ram','sistema_operacional','tipo_produto') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `caracteristicas`
--

INSERT INTO `caracteristicas` (`id_caracteristica`, `nome`, `tipo`) VALUES
(1, 'Armazenamento', 'armazenamento'),
(2, 'Processador', 'processador'),
(3, 'Tipo de Produto', 'tipo_produto');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nome_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nome_categoria`) VALUES
(1, 'Computadores'),
(2, 'Cadeiras'),
(3, 'Acessórios'),
(4, 'Monitores'),
(5, 'Periféricos');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria_atributos`
--

CREATE TABLE `categoria_atributos` (
  `id_categoria` int(11) NOT NULL,
  `id_atributo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categoria_atributos`
--

INSERT INTO `categoria_atributos` (`id_categoria`, `id_atributo`) VALUES
(1, 1),
(1, 3),
(1, 4),
(1, 5),
(1, 9),
(1, 10),
(2, 1),
(2, 2),
(2, 8),
(2, 9),
(2, 10),
(4, 1),
(4, 2),
(4, 6),
(4, 7),
(4, 10),
(5, 1),
(5, 9),
(5, 10);

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `id_estoque` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `marcas`
--

CREATE TABLE `marcas` (
  `id_marca` int(11) NOT NULL,
  `nome_marca` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `marcas`
--

INSERT INTO `marcas` (`id_marca`, `nome_marca`) VALUES
(1, 'Dell'),
(2, 'HP'),
(3, 'Apple'),
(4, 'Samsung'),
(5, 'Logitech'),
(6, 'Razer'),
(7, 'Corsair'),
(8, 'Noblechairs');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id_pagamento` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `data_pagamento` datetime DEFAULT current_timestamp(),
  `valor_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamento_produto`
--

CREATE TABLE `pagamento_produto` (
  `id_pagamento` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto`
--

CREATE TABLE `produto` (
  `id_produto` int(11) NOT NULL,
  `titulo_produto` varchar(220) NOT NULL,
  `preco_produto` int(11) NOT NULL,
  `descricao_produto` text NOT NULL,
  `image_produto` blob NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `id_marca` int(11) DEFAULT NULL,
  `id_usuario_cadastro` int(11) NOT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_atributos`
--

CREATE TABLE `produto_atributos` (
  `id_produto` int(11) NOT NULL,
  `id_atributo` int(11) NOT NULL,
  `id_valor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_caracteristica`
--

CREATE TABLE `produto_caracteristica` (
  `id_produto` int(11) NOT NULL,
  `id_caracteristica` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `valores_atributos`
--

CREATE TABLE `valores_atributos` (
  `id_valor` int(11) NOT NULL,
  `id_atributo` int(11) NOT NULL,
  `valor` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `valores_atributos`
--

INSERT INTO `valores_atributos` (`id_valor`, `id_atributo`, `valor`) VALUES
(1, 1, 'Preto'),
(2, 1, 'Branco'),
(3, 1, 'Prata'),
(4, 1, 'Azul'),
(5, 1, 'Vermelho'),
(6, 1, 'Verde'),
(7, 1, 'Rosa'),
(8, 2, 'Pequeno'),
(9, 2, 'Médio'),
(10, 2, 'Grande'),
(11, 2, 'Extra Grande'),
(12, 3, '4GB'),
(13, 3, '8GB'),
(14, 3, '16GB'),
(15, 3, '32GB'),
(16, 3, '64GB'),
(17, 4, 'Intel Core i3'),
(18, 4, 'Intel Core i5'),
(19, 4, 'Intel Core i7'),
(20, 4, 'Intel Core i9'),
(21, 4, 'AMD Ryzen 3'),
(22, 4, 'AMD Ryzen 5'),
(23, 4, 'AMD Ryzen 7'),
(24, 4, 'AMD Ryzen 9'),
(25, 4, 'Apple M1'),
(26, 4, 'Apple M2'),
(27, 5, '128GB SSD'),
(28, 5, '256GB SSD'),
(29, 5, '512GB SSD'),
(30, 5, '1TB SSD'),
(31, 5, '2TB SSD'),
(32, 5, '1TB HDD'),
(33, 5, '2TB HDD'),
(34, 6, 'LED'),
(35, 6, 'OLED'),
(36, 6, 'IPS'),
(37, 6, 'TN'),
(38, 6, 'VA'),
(39, 7, '60Hz'),
(40, 7, '75Hz'),
(41, 7, '120Hz'),
(42, 7, '144Hz'),
(43, 7, '240Hz'),
(44, 7, '360Hz'),
(45, 8, 'Couro'),
(46, 8, 'Tecido'),
(47, 8, 'Mesh'),
(48, 8, 'Plástico'),
(49, 8, 'Metal'),
(50, 9, 'Até 1kg'),
(51, 9, '1-3kg'),
(52, 9, '3-5kg'),
(53, 9, '5-10kg'),
(54, 9, '10-20kg'),
(55, 9, 'Acima de 20kg'),
(56, 10, '6 meses'),
(57, 10, '1 ano'),
(58, 10, '2 anos'),
(59, 10, '3 anos'),
(60, 10, '5 anos');

-- --------------------------------------------------------

--
-- Estrutura para tabela `valores_caracteristica`
--

CREATE TABLE `valores_caracteristica` (
  `id_valor` int(11) NOT NULL,
  `id_caracteristica` int(11) NOT NULL,
  `valor` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `valores_caracteristica`
--

INSERT INTO `valores_caracteristica` (`id_valor`, `id_caracteristica`, `valor`) VALUES
(2, 1, '16GB'),
(1, 1, '8GB'),
(4, 2, 'AMD Ryzen 5'),
(3, 2, 'Intel i7-12700'),
(6, 3, 'Chromebook'),
(7, 3, 'Desktop'),
(5, 3, 'Notebook');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `atributos`
--
ALTER TABLE `atributos`
  ADD PRIMARY KEY (`id_atributo`);

--
-- Índices de tabela `cadastro_user`
--
ALTER TABLE `cadastro_user`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Índices de tabela `caracteristicas`
--
ALTER TABLE `caracteristicas`
  ADD PRIMARY KEY (`id_caracteristica`),
  ADD KEY `idx_caracteristica_tipo` (`tipo`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Índices de tabela `categoria_atributos`
--
ALTER TABLE `categoria_atributos`
  ADD PRIMARY KEY (`id_categoria`,`id_atributo`),
  ADD KEY `id_atributo` (`id_atributo`);

--
-- Índices de tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id_estoque`),
  ADD KEY `id_produto` (`id_produto`);

--
-- Índices de tabela `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id_marca`);

--
-- Índices de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id_pagamento`),
  ADD KEY `pagamentos_ibfk_1` (`id_usuario`);

--
-- Índices de tabela `pagamento_produto`
--
ALTER TABLE `pagamento_produto`
  ADD PRIMARY KEY (`id_pagamento`,`id_produto`),
  ADD KEY `pagamento_produto_ibfk_2` (`id_produto`);

--
-- Índices de tabela `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`id_produto`),
  ADD KEY `produto_fk_usuario` (`id_usuario_cadastro`),
  ADD KEY `produto_ibfk_1` (`id_categoria`),
  ADD KEY `produto_ibfk_2` (`id_marca`);

--
-- Índices de tabela `produto_atributos`
--
ALTER TABLE `produto_atributos`
  ADD PRIMARY KEY (`id_produto`,`id_atributo`),
  ADD KEY `id_atributo` (`id_atributo`),
  ADD KEY `id_valor` (`id_valor`);

--
-- Índices de tabela `produto_caracteristica`
--
ALTER TABLE `produto_caracteristica`
  ADD PRIMARY KEY (`id_produto`,`id_caracteristica`),
  ADD KEY `produto_caracteristica_fk2` (`id_caracteristica`);

--
-- Índices de tabela `valores_atributos`
--
ALTER TABLE `valores_atributos`
  ADD PRIMARY KEY (`id_valor`),
  ADD KEY `id_atributo` (`id_atributo`);

--
-- Índices de tabela `valores_caracteristica`
--
ALTER TABLE `valores_caracteristica`
  ADD PRIMARY KEY (`id_valor`),
  ADD KEY `idx_valor_caracteristica` (`id_caracteristica`,`valor`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `atributos`
--
ALTER TABLE `atributos`
  MODIFY `id_atributo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `cadastro_user`
--
ALTER TABLE `cadastro_user`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `caracteristicas`
--
ALTER TABLE `caracteristicas`
  MODIFY `id_caracteristica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id_estoque` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id_pagamento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produto`
--
ALTER TABLE `produto`
  MODIFY `id_produto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `valores_atributos`
--
ALTER TABLE `valores_atributos`
  MODIFY `id_valor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de tabela `valores_caracteristica`
--
ALTER TABLE `valores_caracteristica`
  MODIFY `id_valor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `categoria_atributos`
--
ALTER TABLE `categoria_atributos`
  ADD CONSTRAINT `categoria_atributos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`),
  ADD CONSTRAINT `categoria_atributos_ibfk_2` FOREIGN KEY (`id_atributo`) REFERENCES `atributos` (`id_atributo`);

--
-- Restrições para tabelas `estoque`
--
ALTER TABLE `estoque`
  ADD CONSTRAINT `estoque_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`);

--
-- Restrições para tabelas `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD CONSTRAINT `pagamentos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `cadastro_user` (`id_usuario`);

--
-- Restrições para tabelas `pagamento_produto`
--
ALTER TABLE `pagamento_produto`
  ADD CONSTRAINT `pagamento_produto_ibfk_1` FOREIGN KEY (`id_pagamento`) REFERENCES `pagamentos` (`id_pagamento`),
  ADD CONSTRAINT `pagamento_produto_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`);

--
-- Restrições para tabelas `produto`
--
ALTER TABLE `produto`
  ADD CONSTRAINT `produto_fk_usuario` FOREIGN KEY (`id_usuario_cadastro`) REFERENCES `cadastro_user` (`id_usuario`),
  ADD CONSTRAINT `produto_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`),
  ADD CONSTRAINT `produto_ibfk_2` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`);

--
-- Restrições para tabelas `produto_atributos`
--
ALTER TABLE `produto_atributos`
  ADD CONSTRAINT `produto_atributos_ibfk_1` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`),
  ADD CONSTRAINT `produto_atributos_ibfk_2` FOREIGN KEY (`id_atributo`) REFERENCES `atributos` (`id_atributo`),
  ADD CONSTRAINT `produto_atributos_ibfk_3` FOREIGN KEY (`id_valor`) REFERENCES `valores_atributos` (`id_valor`);

--
-- Restrições para tabelas `produto_caracteristica`
--
ALTER TABLE `produto_caracteristica`
  ADD CONSTRAINT `produto_caracteristica_fk1` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`),
  ADD CONSTRAINT `produto_caracteristica_fk2` FOREIGN KEY (`id_caracteristica`) REFERENCES `caracteristicas` (`id_caracteristica`);

--
-- Restrições para tabelas `valores_atributos`
--
ALTER TABLE `valores_atributos`
  ADD CONSTRAINT `valores_atributos_ibfk_1` FOREIGN KEY (`id_atributo`) REFERENCES `atributos` (`id_atributo`);

--
-- Restrições para tabelas `valores_caracteristica`
--
ALTER TABLE `valores_caracteristica`
  ADD CONSTRAINT `valores_caracteristica_fk` FOREIGN KEY (`id_caracteristica`) REFERENCES `caracteristicas` (`id_caracteristica`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;