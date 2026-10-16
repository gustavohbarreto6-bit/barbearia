<?php
// Inicia a sessão para gerenciar autenticação do usuário
session_start();
// Verifica se o usuário está logado, caso contrário redireciona para a página de login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
// Verifica o tempo de expiração da sessão: se ficou mais de 30 minutos sem usar o sistema,
// encerra a sessão e volta para o login avisando que expirou
// (clínica: este bloco é igual)
if (time() - $_SESSION['ultimo_acesso'] > 30 * 60) {
    session_destroy();
    header('Location: login.php?expirou=1');
    exit();
}
// Ainda está no prazo: renova o horário do último acesso
$_SESSION['ultimo_acesso'] = time();
// Inclui o arquivo de configuração para conexão com o banco de dados
include 'config.php';
// Obtém o ID do agendamento a ser excluído passado via URL
$id = $_GET['id'] ?? 0;
// Prepara a instrução SQL para excluir o agendamento com o ID especificado
$sql = "DELETE FROM agendamentos WHERE id=?";
// Prepara o comando para execução
$stmt = $conn->prepare($sql);
// Associa o parâmetro ID à instrução SQL
$stmt->bind_param('i', $id);
// Executa a instrução de exclusão
$stmt->execute();
// Após excluir, volta à lista de agendamentos
header("Location: criar_agendamento.php");
// Encerra o script para garantir que o redirecionamento ocorra imediatamente
exit();
