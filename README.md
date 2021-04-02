# inFORTCMS
Um CMS opensource criado por <a href="https://www.infort.eti.br" target="_blank" rel="noopener">infort.eti.br</a> com Adianti Framework.

<p align="center">
<img src="https://img.shields.io/badge/VERSÃO-1.1.0-green">
<img src="https://img.shields.io/badge/Licença-GNU 3.0-success">
<img src="https://img.shields.io/badge/PHP-inFORT-blue">
<img src="https://img.shields.io/badge/PHP-Adianti-blue">
<img src="https://img.shields.io/badge/PHP->7.2-blueviolet">
<img src="https://img.shields.io/badge/PHP-MySQL-blueviolet">
</p>


O inFORTCMS foi construído pensando no usuário final, aquela pessoa que não tem conhecimento algum de programação ou de técnicas de SEO. O inFORTCMS é capas de gerar telas intuitivas dinamicamente, permitindo que qualquer pessoa seja capaz de operar e fazer as modificações em seu site com facilidade.

Toda essa facilidade só será permitida através da tela de criação de "Modelos HTML", onde o programador prepara trechos de código HTML que pode ser usado na criação das páginas ou dos módulos.

O inFORTCMS foi desenvolvido utilizando como base o Adianti Template, que é um grande gabarito para a criação de novos projetos. Para saber mais sobre o Adianti Framework e sobre o Adianti Template, acesse:<br>
    * <a href="https://adianti.com.br/framework" target="_blank" rel="noopener">Adianti Framework</a><br>
    * <a href="https://adianti.com.br/framework-template" target="_blank" rel="noopener">Adianti Template</a>


## Recursos

	* Gerenciador de menus
	* Gerenciador de páginas
	* Gerenciador de blog
	* Gerenciador de templates HTML
	* Gerenciador de módulos
	* Gerenciador de modelos de HTML
	* Gerenciador de Formulários e Mensagens
	* Gerenciador de arquivos de imagem
	* SEO
		* Geração automática de sitemap (XML) e do robots.txt
		* Geração automática de meta tags
	* Geração de Cache com minificação do código HTML
	* Tela de bloqueio
    * Gestção de mí­dias sociais, com geração de estatí­sticas
    * Dashboard com estatí­sticas de acesso


## Histórico (ChangeLog)

### [1.1.0] - 2021-04-01
    * [Secured] - Adianti Framework atualizado para a versão 7.3.0;
    * [Secured] - Homologado para a versão 8.0 DO PHP;
    * [Added] - Gerenciador de formulários com armazenamento das mensagens em banco de dados para consulta e reenvio de e-mails;
    * [Added] - em "Preferências Globais", foi adicionado 2 campos para definição da "logo" e do "favicon";
    * [Added] - em "Formulário de Página", foi adicionado 2 campos para a inserção de scripts e css exclusivos para a página;
    * [Changed] - em "Formulário de Página", os campos de SEO (meta descrição e meta keys) agora estão em outra aba;
    * [Changed] - em "Gestão de Imagens" agora é possí­vel fazer upload de multiplas imagens e já convertêlas para o formato "webp";
    * [Changed] - em "Gestão de Imagens", todas as mensagens agora são exibidas usando a classe "TToast" no lugar do "TMessage", com isso melhoramos a usabilidade da tela;
    * [Changed] - em "Preferências Globais", os campos referênte às "Configurações de E-mail" agora são exatamente os mesmos campos presentes na tela de "Preferências" da template do Adianti, com excessão do campo "E-mail de destino";
    * [Changed] - Para melhor agilidade durante a inserção de imagens, os diálogos de avisos agora são exibidos por Toast;
    * [Removed] - foram removidas dependências não utilizadas do composer;

### [1.0.0] - 2020-08-25
    * Primeira versão (first release)
    * [Security] - Adianti Framework atualizado para a versão 7.2.2
    * [Added] - tela para gerar e manipular o arquivo "robots.txt".
    * [Added] - adicionado botão para esconder os campos de pesquisa em todas as telas de listagem.
    * [Added] - em "Preferências Globais", foi adicionado 2 novos parâmetros para manipular o upload de imagens: "Largura" e "Altura".
    * [Added] - em "Preferências Globais", foi adicionado 2 novos parâmetros para geo localização: "latitude" e "longitude".
    * [Added] - em "Preferências Globais", ao sair do campo "Postal", o CEP será consultado e os campos de "Cidade", "Estado", "Pais", "latitude" e "longitude" serão preenchidos automáticamente.
    * [Added] - tela de "Módulos", para inserção de conteúdo em posições pré definidas da template.
    * [Added] - tela de "Modelos HTML", para criação de trechos de HTML que podem ser usados para criar um módulo ou uma página do site.
    * [Added] - dados estatí­sticos de acesso agora estão no Dashboard.
    * [Changed] - alterado o algoritimo de encriptação da senha, agora fazendo uso de salt-key.
    * [Changed] - o menu foi reorganizado.
    * [Changed] - em Templates agora é possí­vel criar e administrar "posições" que serão usadas na tela de "Módulos".
	* [Changed] - na tela de "Gestão de Imagens", ao fazer um upload será possivel ajustar o formato da imagem (4:3, 16:9, 1:1, etc), e escolher se deve converter para o formato ".webp" ou não.
    * [Changed] - adicionado mais um serviço para busca de dados por IP.
    * [Changed] - BD: 2 novas tabelas criadas, sendo: "posicao" e "modelo_html". A tabela "modelo_modulo" foi removida.
    * [Changed] - BD: campo "variavel"(VARCHAR), da tabela "modulo" foi alterado para "html"(TEXT). O campo "posicao"(VARCHAR) foi adicionado.
    * [Fixed] - tela de "Galerida de Fotos", corrigido bug que, ao tentar mudar uma foto, todos os campos eram perdidos.
    * [Fixed] - corrigido bug no menu que não gerava um link externo corretamente.
    * [Fixed] - verificação correta do sitemap, para que não faça cache do xml.
    * [Fixed] - em "Preferencias do Sistema", propriedade name do campo Cache alterado: de "cache_control" para "pref_cache_control".
    * [Fixed] - corrigido bug que registrava o carregamento de um arquivo css, js, etc, como sendo um acesso novo.
    * [Removed] - todos os arquivos referente a tabela "modelo_modulo" foram retirados.

### [0.5.0] - 2020-05-30
    * Lançamento beta do projeto



## Meta

inFORT - [@inforthub] - contato@infort.eti.br<br>
Distribuído sob a Licença Pública Geral GNU (GPLv3)
