-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 30-Maio-2020 às 23:31
-- Versão do servidor: 5.7.30-0ubuntu0.18.04.1
-- versão do PHP: 7.4.6

--
-- Banco de dados: `infortcms`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `arquivo`
--

CREATE TABLE `arquivo` (
  `id` int(11) NOT NULL,
  `nome` varchar(200) DEFAULT NULL,
  `nome_web` varchar(200) NOT NULL,
  `descricao` varchar(200) DEFAULT NULL,
  `formato` char(1) NOT NULL COMMENT 'Ex: imagem: F, audio: A, video: V',
  `dt_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `destaque` char(1) NOT NULL DEFAULT 'f',
  `artigo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de arquivos multimídia (fotos, audio e vídeo)';

-- --------------------------------------------------------

--
-- Estrutura da tabela `artigo`
--

CREATE TABLE `artigo` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `url` varchar(100) NOT NULL,
  `resumo` text,
  `artigo` text NOT NULL,
  `metadesc` varchar(250) DEFAULT NULL,
  `metakey` varchar(250) DEFAULT NULL,
  `midias` text COMMENT 'Campo serializado para: Audio:{},Video:(),Foto:{}',
  `dt_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dt_post` timestamp NULL DEFAULT NULL COMMENT 'Data de postagem',
  `dt_edicao` timestamp NULL DEFAULT NULL COMMENT 'Data da última edição',
  `visitas` int(11) NOT NULL DEFAULT '0',
  `usuario_id` int(11) NOT NULL,
  `destaque` char(1) NOT NULL DEFAULT 'f',
  `ativo` char(1) NOT NULL DEFAULT 't' COMMENT '''t'' ou ''f''',
  `modo` char(1) NOT NULL DEFAULT 'a' COMMENT 'Indica se é um artigo ou uma categoria: ''a''- artigo, ''c''- categoria',
  `tipo_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de artigos e blogs do sistema';

-- --------------------------------------------------------

--
-- Estrutura da tabela `click`
--

CREATE TABLE `click` (
  `id` int(11) NOT NULL,
  `dt_clique` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pagina` varchar(255) NOT NULL,
  `ip` varchar(60) DEFAULT NULL,
  `cidade` varchar(150) DEFAULT NULL,
  `regiao` varchar(150) DEFAULT NULL,
  `pais` varchar(150) DEFAULT NULL,
  `navegador` varchar(100) DEFAULT NULL,
  `plataforma` varchar(100) DEFAULT NULL,
  `midia_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `comentario`
--

CREATE TABLE `comentario` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `titulo` varchar(80) NOT NULL,
  `comentario` text NOT NULL,
  `dt_post` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resposta_id` int(11) DEFAULT NULL,
  `artigo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de comentários de um artigo\n';

-- --------------------------------------------------------

--
-- Estrutura da tabela `formulario`
--

CREATE TABLE `formulario` (
  `id` int(11) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `html_email` text NOT NULL COMMENT 'HTML base do email a ser enviado',
  `html_site` text NOT NULL COMMENT 'HTML a ser renderizado no site',
  `msg_erro` varchar(255) NOT NULL COMMENT 'Mensagem de erro ao tentar enviar',
  `msg_sucesso` varchar(255) NOT NULL COMMENT 'Mensagem de sucesso ao enviar',
  `email_destino` varchar(200) NOT NULL,
  `ativo` varchar(1) NOT NULL DEFAULT 't'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de formulários customizados';

-- --------------------------------------------------------

--
-- Estrutura da tabela `form_mensagem`
--

CREATE TABLE `form_mensagem` (
  `id` int(11) NOT NULL,
  `assunto` varchar(200) NOT NULL,
  `mensagem` text NOT NULL,
  `email_origem` varchar(200) NOT NULL,
  `email_destino` varchar(200) NOT NULL,
  `dt_mensagem` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enviada` char(1) NOT NULL DEFAULT 'f' COMMENT 'Indica se a mensagem foi enviada ou não.',
  `formulario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `link`
--

CREATE TABLE `link` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL COMMENT 'URL full ''https://www.dominio.com/...''',
  `lastmod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changefreq` varchar(10) NOT NULL DEFAULT 'yearly' COMMENT 'never, yearly, monthly, weekly, daily, hourly, always',
  `priority` varchar(5) NOT NULL DEFAULT '0.70' COMMENT '1.00, 0.90, 0.80 ... 0.10, 0.00, none',
  `destino` varchar(45) DEFAULT NULL,
  `artigo_id` int(11) NOT NULL,
  `tipo_id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de Links do site';

-- --------------------------------------------------------

--
-- Estrutura da tabela `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `ordem` varchar(2) NOT NULL,
  `inicial` char(1) NOT NULL DEFAULT 'f' COMMENT 'Página inicial',
  `icone` varchar(45) DEFAULT NULL,
  `header_class` varchar(100) DEFAULT NULL,
  `menu_pai_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Indica se é um submenu',
  `ativo` char(1) NOT NULL DEFAULT 't',
  `artigo_id` int(11) DEFAULT NULL COMMENT 'Este campo, obrigatoriamente, será igual ao artigo que esteja relacionado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `midia`
--

CREATE TABLE `midia` (
  `id` int(11) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icone` varchar(45) NOT NULL,
  `ativo` char(1) NOT NULL DEFAULT 'f'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela das midias sociais ativas (facebook, whatsapp, instagram,etc)';

-- --------------------------------------------------------

--
-- Estrutura da tabela `modelo_modulo`
--

CREATE TABLE `modelo_modulo` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `parametros` text NOT NULL,
  `html` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `modulo`
--

CREATE TABLE `modulo` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `variavel` varchar(100) DEFAULT NULL,
  `parametros` text NOT NULL,
  `ordem` varchar(2) NOT NULL,
  `ativo` char(1) NOT NULL DEFAULT 't',
  `modelo_modulo_id` int(11) NOT NULL,
  `artigo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_document`
--

CREATE TABLE `system_document` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `title` text,
  `description` text,
  `category_id` int(11) DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `archive_date` date DEFAULT NULL,
  `filename` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_document_category`
--

CREATE TABLE `system_document_category` (
  `id` int(11) NOT NULL,
  `name` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `system_document_category`
--

INSERT INTO `system_document_category` (`id`, `name`) VALUES
(1, 'Documentação');

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_document_group`
--

CREATE TABLE `system_document_group` (
  `id` int(11) NOT NULL,
  `document_id` int(11) DEFAULT NULL,
  `system_group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_document_user`
--

CREATE TABLE `system_document_user` (
  `id` int(11) NOT NULL,
  `document_id` int(11) DEFAULT NULL,
  `system_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_group`
--

CREATE TABLE `system_group` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `system_group`
--

INSERT INTO `system_group` (`id`, `name`) VALUES
(1, 'Administrador'),
(2, 'Padrão'),
(3, 'Gestão');

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_program`
--

CREATE TABLE `system_program` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `controller` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `system_program`
--

INSERT INTO `system_program` (`id`, `name`, `controller`) VALUES
(1, 'System Group Form', 'SystemGroupForm'),
(2, 'System Group List', 'SystemGroupList'),
(3, 'System Program Form', 'SystemProgramForm'),
(4, 'System Program List', 'SystemProgramList'),
(5, 'System User Form', 'SystemUserForm'),
(6, 'System User List', 'SystemUserList'),
(7, 'Common Page', 'CommonPage'),
(8, 'System PHP Info', 'SystemPHPInfoView'),
(9, 'System ChangeLog View', 'SystemChangeLogView'),
(10, 'Welcome View', 'WelcomeView'),
(11, 'System Sql Log', 'SystemSqlLogList'),
(12, 'System Profile View', 'SystemProfileView'),
(13, 'System Profile Form', 'SystemProfileForm'),
(14, 'System SQL Panel', 'SystemSQLPanel'),
(15, 'System Access Log', 'SystemAccessLogList'),
(16, 'System Message Form', 'SystemMessageForm'),
(17, 'System Message List', 'SystemMessageList'),
(18, 'System Message Form View', 'SystemMessageFormView'),
(19, 'System Notification List', 'SystemNotificationList'),
(20, 'System Notification Form View', 'SystemNotificationFormView'),
(21, 'System Document Category List', 'SystemDocumentCategoryFormList'),
(22, 'System Document Form', 'SystemDocumentForm'),
(23, 'System Document Upload Form', 'SystemDocumentUploadForm'),
(24, 'System Document List', 'SystemDocumentList'),
(25, 'System Shared Document List', 'SystemSharedDocumentList'),
(26, 'System Unit Form', 'SystemUnitForm'),
(27, 'System Unit List', 'SystemUnitList'),
(28, 'System Access stats', 'SystemAccessLogStats'),
(29, 'System Preference form', 'SystemPreferenceForm'),
(30, 'System Support form', 'SystemSupportForm'),
(31, 'System PHP Error', 'SystemPHPErrorLogView'),
(32, 'System Database Browser', 'SystemDatabaseExplorer'),
(33, 'System Table List', 'SystemTableList'),
(34, 'System Data Browser', 'SystemDataBrowser'),
(35, 'System Menu Editor', 'SystemMenuEditor'),
(36, 'System Request Log', 'SystemRequestLogList'),
(37, 'System Request Log View', 'SystemRequestLogView'),
(38, 'System Administration Dashboard', 'SystemAdministrationDashboard'),
(39, 'System Log Dashboard', 'SystemLogDashboard'),
(40, 'System Session dump', 'SystemSessionDumpView'),
(41, 'Dashboard', 'Dashboard'),
(42, 'Preferencias Form', 'PreferenciasForm'),
(45, 'Template List', 'TemplateList'),
(46, 'Template Form', 'TemplateForm'),
(47, 'Template File Form', 'TemplateFileForm'),
(48, 'Template File', 'TemplateFile'),
(49, 'Navegador View', 'NavegadorView'),
(50, 'Midia List', 'MidiaList'),
(51, 'Midia Form', 'MidiaForm'),
(52, 'Link List', 'LinkList'),
(53, 'Link Form', 'LinkForm'),
(54, 'Click List', 'ClickList'),
(55, 'Click Stats', 'ClickStats'),
(56, 'Menu Form', 'MenuForm'),
(57, 'Menu List', 'MenuList'),
(58, 'Gerador Senha Form', 'GeradorSenhaForm'),
(59, 'Midia Form View', 'MidiaFormView'),
(60, 'Pagina Form', 'PaginaForm'),
(61, 'Pagina List', 'PaginaList'),
(62, 'Tipo Form', 'TipoForm'),
(63, 'Tipo List', 'TipoList'),
(64, 'Categoria Form', 'CategoriaForm'),
(65, 'Categoria List', 'CategoriaList'),
(66, 'Blog Categoria Form', 'BlogCategoriaForm'),
(67, 'Blog Categoria Form List', 'BlogCategoriaFormList'),
(68, 'Blog Categoria List', 'BlogCategoriaList'),
(69, 'Blog Post Form', 'BlogPostForm'),
(70, 'Blog Post List', 'BlogPostList'),
(71, 'Arquivo Form List', 'ArquivoFormList'),
(72, 'Modelo Modulo Form', 'ModeloModuloForm'),
(73, 'Modelo Modulo List', 'ModeloModuloList'),
(74, 'Arquivos List', 'ArquivosList'),
(75, 'Arquivos Form View', 'ArquivosFormView'),
(76, 'Template File View', 'TemplateFileView'),
(77, 'Selecao Imagem', 'SelecaoImagem');

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_group_program`
--

CREATE TABLE `system_group_program` (
  `id` int(11) NOT NULL,
  `system_group_id` int(11) DEFAULT NULL,
  `system_program_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `system_group_program`
--

INSERT INTO `system_group_program` (`id`, `system_group_id`, `system_program_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 8),
(8, 1, 9),
(9, 1, 11),
(10, 1, 14),
(11, 1, 15),
(12, 2, 10),
(13, 2, 12),
(14, 2, 13),
(15, 2, 16),
(16, 2, 17),
(17, 2, 18),
(18, 2, 19),
(19, 2, 20),
(20, 1, 21),
(21, 2, 22),
(22, 2, 23),
(23, 2, 24),
(24, 2, 25),
(25, 1, 26),
(26, 1, 27),
(27, 1, 28),
(28, 1, 29),
(29, 2, 30),
(30, 1, 31),
(31, 1, 32),
(32, 1, 33),
(33, 1, 34),
(34, 1, 35),
(36, 1, 36),
(37, 1, 37),
(38, 1, 38),
(39, 1, 39),
(40, 1, 40),
(41, 1, 41),
(42, 2, 41),
(43, 3, 41),
(44, 3, 42),
(47, 3, 45),
(48, 3, 46),
(49, 3, 47),
(50, 3, 48),
(51, 3, 49),
(52, 3, 50),
(53, 3, 51),
(54, 3, 52),
(55, 3, 53),
(56, 3, 54),
(57, 3, 55),
(58, 3, 56),
(59, 3, 57),
(60, 2, 58),
(61, 3, 59),
(62, 3, 60),
(63, 3, 61),
(64, 1, 62),
(65, 1, 63),
(66, 3, 64),
(67, 3, 65),
(68, 3, 66),
(69, 3, 67),
(70, 3, 68),
(71, 3, 69),
(72, 3, 70),
(73, 3, 71),
(74, 3, 72),
(75, 3, 73),
(76, 3, 74),
(77, 3, 75),
(78, 3, 76),
(79, 3, 77);

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_message`
--

CREATE TABLE `system_message` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `system_user_to_id` int(11) DEFAULT NULL,
  `subject` text,
  `message` text,
  `dt_message` text,
  `checked` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_notification`
--

CREATE TABLE `system_notification` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `system_user_to_id` int(11) DEFAULT NULL,
  `subject` text,
  `message` text,
  `dt_message` text,
  `action_url` text,
  `action_label` text,
  `icon` text,
  `checked` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_preference`
--

CREATE TABLE `system_preference` (
  `id` text,
  `value` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `system_preference`
--

INSERT INTO `system_preference` (`id`, `value`) VALUES
('pref_site_nome', 'Empresa'),
('pref_site_manutencao', '0'),
('pref_site_language', 'pt-br'),
('pref_site_mensagem', 'Este site está em manutenção.'),
('cache_control', '0'),
('pref_emp_nome', 'Empresa');

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_unit`
--

CREATE TABLE `system_unit` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `connection_name` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_user`
--

CREATE TABLE `system_user` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `login` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `frontpage_id` int(11) DEFAULT NULL,
  `system_unit_id` int(11) DEFAULT NULL,
  `active` char(1) DEFAULT NULL,
  `reset_pass` char(1) DEFAULT NULL,
  `data_pass` varchar(50) DEFAULT NULL,
  `uid_pass` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `system_user`
--

INSERT INTO `system_user` (`id`, `name`, `login`, `password`, `email`, `frontpage_id`, `system_unit_id`, `active`, `reset_pass`, `data_pass`, `uid_pass`) VALUES
(1, 'Administrator', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@admin.net', 41, NULL, 'Y', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_user_group`
--

CREATE TABLE `system_user_group` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `system_group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `system_user_group`
--

INSERT INTO `system_user_group` (`id`, `system_user_id`, `system_group_id`) VALUES
(8, 1, 1),
(9, 1, 2),
(10, 1, 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_user_program`
--

CREATE TABLE `system_user_program` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `system_program_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `system_user_unit`
--

CREATE TABLE `system_user_unit` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `system_unit_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `template`
--

CREATE TABLE `template` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `nome_fisico` varchar(100) NOT NULL,
  `script_head` text COMMENT 'Script antes do /head',
  `script_body` text COMMENT 'Script antes do /body',
  `dt_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `padrao` char(1) NOT NULL DEFAULT 't' COMMENT 'Indica se é a template padrão'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `template`
--

INSERT INTO `template` (`id`, `nome`, `nome_fisico`, `script_head`, `script_body`, `dt_cadastro`, `padrao`) VALUES
(1, 'Tic Tac', 'tictac', NULL, NULL, '2020-04-17 03:57:21', 't');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipo`
--

CREATE TABLE `tipo` (
  `id` int(11) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `parametros` text,
  `ativo` varchar(45) NOT NULL DEFAULT 't'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tabela de tipos de artigos vinculada a categoria: Site, Blog, News, etc.';

--
-- Extraindo dados da tabela `tipo`
--

INSERT INTO `tipo` (`id`, `nome`, `parametros`, `ativo`) VALUES
(1, 'Site', NULL, 't'),
(2, 'Blog', NULL, 't');

-- --------------------------------------------------------

--
-- Estrutura da tabela `trafego`
--

CREATE TABLE `trafego` (
  `id` int(11) NOT NULL,
  `dt_acesso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pagina` varchar(255) NOT NULL,
  `ip` varchar(60) DEFAULT NULL,
  `cidade` varchar(150) DEFAULT NULL,
  `regiao` varchar(150) DEFAULT NULL,
  `pais` varchar(150) DEFAULT NULL,
  `navegador` varchar(100) DEFAULT NULL,
  `referencia` varchar(150) DEFAULT NULL,
  `plataforma` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `arquivo`
--
ALTER TABLE `arquivo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_arquivos_artigo1_idx` (`artigo_id`);

--
-- Índices para tabela `artigo`
--
ALTER TABLE `artigo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `url_UNIQUE` (`url`),
  ADD KEY `fk_artigo_tipo1_idx` (`tipo_id`),
  ADD KEY `titulo_idx` (`titulo`) USING BTREE,
  ADD KEY `fk_artigo_artigo1_idx` (`categoria_id`);

--
-- Índices para tabela `click`
--
ALTER TABLE `click`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_click_midia1_idx` (`midia_id`),
  ADD KEY `click_data_idx` (`dt_clique`);

--
-- Índices para tabela `comentario`
--
ALTER TABLE `comentario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comentario_artigo1_idx` (`artigo_id`);

--
-- Índices para tabela `formulario`
--
ALTER TABLE `formulario`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `form_mensagem`
--
ALTER TABLE `form_mensagem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_form_mensagem_formulario1_idx` (`formulario_id`);

--
-- Índices para tabela `link`
--
ALTER TABLE `link`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `url_UNIQUE` (`url`),
  ADD KEY `fk_link_artigo1_idx` (`artigo_id`),
  ADD KEY `fk_link_tipo1_idx` (`tipo_id`),
  ADD KEY `fk_link_template1_idx` (`template_id`);

--
-- Índices para tabela `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `url_UNIQUE` (`url`),
  ADD KEY `fk_menu_artigo1_idx` (`artigo_id`);

--
-- Índices para tabela `midia`
--
ALTER TABLE `midia`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `modelo_modulo`
--
ALTER TABLE `modelo_modulo`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `modulo`
--
ALTER TABLE `modulo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ioasu_modulo_ioasu_modelo_modulo1_idx` (`modelo_modulo_id`),
  ADD KEY `fk_modulo_artigo1_idx` (`artigo_id`);

--
-- Índices para tabela `system_document`
--
ALTER TABLE `system_document`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Índices para tabela `system_document_category`
--
ALTER TABLE `system_document_category`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `system_document_group`
--
ALTER TABLE `system_document_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`);

--
-- Índices para tabela `system_document_user`
--
ALTER TABLE `system_document_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`);

--
-- Índices para tabela `system_group`
--
ALTER TABLE `system_group`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `system_group_program`
--
ALTER TABLE `system_group_program`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_group_program_program_idx` (`system_program_id`),
  ADD KEY `system_group_program_group_idx` (`system_group_id`);

--
-- Índices para tabela `system_message`
--
ALTER TABLE `system_message`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `system_notification`
--
ALTER TABLE `system_notification`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `system_program`
--
ALTER TABLE `system_program`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `system_unit`
--
ALTER TABLE `system_unit`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `system_user`
--
ALTER TABLE `system_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_unit_id` (`system_unit_id`),
  ADD KEY `system_user_program_idx` (`frontpage_id`);

--
-- Índices para tabela `system_user_group`
--
ALTER TABLE `system_user_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_user_group_user_idx` (`system_user_id`),
  ADD KEY `system_user_group_group_idx` (`system_group_id`);

--
-- Índices para tabela `system_user_program`
--
ALTER TABLE `system_user_program`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_user_program_user_idx` (`system_user_id`),
  ADD KEY `system_user_program_program_idx` (`system_program_id`);

--
-- Índices para tabela `system_user_unit`
--
ALTER TABLE `system_user_unit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_user_id` (`system_user_id`),
  ADD KEY `system_unit_id` (`system_unit_id`);

--
-- Índices para tabela `template`
--
ALTER TABLE `template`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_UNIQUE` (`nome`),
  ADD UNIQUE KEY `nome_fisico_UNIQUE` (`nome_fisico`);

--
-- Índices para tabela `tipo`
--
ALTER TABLE `tipo`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `trafego`
--
ALTER TABLE `trafego`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trafego_data_idx` (`dt_acesso`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `artigo`
--
ALTER TABLE `artigo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `click`
--
ALTER TABLE `click`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `comentario`
--
ALTER TABLE `comentario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `formulario`
--
ALTER TABLE `formulario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `form_mensagem`
--
ALTER TABLE `form_mensagem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `link`
--
ALTER TABLE `link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `midia`
--
ALTER TABLE `midia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `modelo_modulo`
--
ALTER TABLE `modelo_modulo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `modulo`
--
ALTER TABLE `modulo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `template`
--
ALTER TABLE `template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `tipo`
--
ALTER TABLE `tipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `trafego`
--
ALTER TABLE `trafego`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `arquivo`
--
ALTER TABLE `arquivo`
  ADD CONSTRAINT `fk_arquivos_artigo1` FOREIGN KEY (`artigo_id`) REFERENCES `artigo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `artigo`
--
ALTER TABLE `artigo`
  ADD CONSTRAINT `fk_artigo_artigo1` FOREIGN KEY (`categoria_id`) REFERENCES `artigo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_artigo_tipo1` FOREIGN KEY (`tipo_id`) REFERENCES `tipo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `click`
--
ALTER TABLE `click`
  ADD CONSTRAINT `fk_click_midia1` FOREIGN KEY (`midia_id`) REFERENCES `midia` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `comentario`
--
ALTER TABLE `comentario`
  ADD CONSTRAINT `fk_comentario_artigo1` FOREIGN KEY (`artigo_id`) REFERENCES `artigo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `form_mensagem`
--
ALTER TABLE `form_mensagem`
  ADD CONSTRAINT `fk_form_mensagem_formulario1` FOREIGN KEY (`formulario_id`) REFERENCES `formulario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `link`
--
ALTER TABLE `link`
  ADD CONSTRAINT `fk_link_artigo1` FOREIGN KEY (`artigo_id`) REFERENCES `artigo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_link_template1` FOREIGN KEY (`template_id`) REFERENCES `template` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_link_tipo1` FOREIGN KEY (`tipo_id`) REFERENCES `tipo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `fk_menu_artigo1` FOREIGN KEY (`artigo_id`) REFERENCES `artigo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `modulo`
--
ALTER TABLE `modulo`
  ADD CONSTRAINT `fk_ioasu_modulo_ioasu_modelo_modulo1` FOREIGN KEY (`modelo_modulo_id`) REFERENCES `modelo_modulo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_modulo_artigo1` FOREIGN KEY (`artigo_id`) REFERENCES `artigo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `system_document`
--
ALTER TABLE `system_document`
  ADD CONSTRAINT `system_document_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `system_document_category` (`id`);

--
-- Limitadores para a tabela `system_document_group`
--
ALTER TABLE `system_document_group`
  ADD CONSTRAINT `system_document_group_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `system_document` (`id`);

--
-- Limitadores para a tabela `system_document_user`
--
ALTER TABLE `system_document_user`
  ADD CONSTRAINT `system_document_user_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `system_document` (`id`);

--
-- Limitadores para a tabela `system_group_program`
--
ALTER TABLE `system_group_program`
  ADD CONSTRAINT `system_group_program_ibfk_1` FOREIGN KEY (`system_group_id`) REFERENCES `system_group` (`id`),
  ADD CONSTRAINT `system_group_program_ibfk_2` FOREIGN KEY (`system_program_id`) REFERENCES `system_program` (`id`);

--
-- Limitadores para a tabela `system_user`
--
ALTER TABLE `system_user`
  ADD CONSTRAINT `system_user_ibfk_1` FOREIGN KEY (`frontpage_id`) REFERENCES `system_program` (`id`),
  ADD CONSTRAINT `system_user_ibfk_2` FOREIGN KEY (`system_unit_id`) REFERENCES `system_unit` (`id`);

--
-- Limitadores para a tabela `system_user_group`
--
ALTER TABLE `system_user_group`
  ADD CONSTRAINT `system_user_group_ibfk_1` FOREIGN KEY (`system_group_id`) REFERENCES `system_group` (`id`),
  ADD CONSTRAINT `system_user_group_ibfk_2` FOREIGN KEY (`system_user_id`) REFERENCES `system_user` (`id`);

--
-- Limitadores para a tabela `system_user_program`
--
ALTER TABLE `system_user_program`
  ADD CONSTRAINT `system_user_program_ibfk_1` FOREIGN KEY (`system_user_id`) REFERENCES `system_user` (`id`),
  ADD CONSTRAINT `system_user_program_ibfk_2` FOREIGN KEY (`system_program_id`) REFERENCES `system_program` (`id`);

--
-- Limitadores para a tabela `system_user_unit`
--
ALTER TABLE `system_user_unit`
  ADD CONSTRAINT `system_user_unit_ibfk_1` FOREIGN KEY (`system_user_id`) REFERENCES `system_user` (`id`),
  ADD CONSTRAINT `system_user_unit_ibfk_2` FOREIGN KEY (`system_unit_id`) REFERENCES `system_unit` (`id`);
COMMIT;

