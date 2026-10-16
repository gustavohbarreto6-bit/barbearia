<?php
// Inicia a sessão para manter o estado do usuário logado
session_start();
// Verifica se o usuário está logado acessando a variável de sessão 'usuario'
// Se não estiver logado, redireciona para a página de login e interrompe o script
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
// Verifica o tempo de expiração da sessão: se ficou mais de 30 minutos sem usar o sistema,
// encerra a sessão e volta para o login avisando que expirou
if (time() - $_SESSION['ultimo_acesso'] > 30 * 60) {
    session_destroy();
    header('Location: login.php?expirou=1');
    exit();
}
// Ainda está no prazo: renova o horário do último acesso
$_SESSION['ultimo_acesso'] = time();
// Armazena o nome do usuário logado em uma variável para exibir na página
$nome = $_SESSION['usuario']['nome'];
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">
<!-- Exibe uma mensagem de boas-vindas com o nome do usuário -->
<p>Bem-vindo, <?= htmlspecialchars($nome) ?></p>
<!-- Links para páginas de funcionalidades do sistema -->
<a href="criar_clientes.php">Clientes</a>
<a href="criar_agendamento.php">Agendamentos</a>
<a href="criar_serviço_pagamento.php">Serviços e pagamento</a>
<a href="historico.php">Histórico</a>
<!-- Link para efetuar logout, encerrando a sessão -->
<a href="logout.php">Logout</a>
