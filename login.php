<?php
// (clínica: o login.php não muda)
// Inicia a sessão para manter estados de login
session_start();


// Se o usuário já estiver logado, redireciona para a página principal
if (isset($_SESSION['usuario'])) {
    header('Location: principal.php');
    exit();
}


// Variável para armazenar mensagem de erro
$erro = "";


// Se a sessão expirou, as outras páginas mandam para cá com ?expirou=1
if (isset($_GET['expirou'])) {
    $erro = "Sua sessão expirou. Faça login novamente.";
}


// Verifica se o formulário foi submetido via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Inclui o arquivo config.php para usar $conn e conectar ao banco
    include 'config.php';


    // Recebe e sanitiza os dados enviados via formulário
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';


    // Prepara uma consulta segura para evitar SQL Injection
    // Busca só pelo nome: a senha está criptografada (hash) no banco e é conferida depois
    $sql = "SELECT * FROM usuarios WHERE nome=?";
    $stmt = $conn->prepare($sql);


    // Associa o parâmetro recebido ao statement SQL
    $stmt->bind_param('s', $nome);


    // Executa a consulta
    $stmt->execute();


    // Obtém o resultado da consulta
    $res = $stmt->get_result();
    $usuario = $res->fetch_assoc();


    // Se encontrou o usuário e a senha digitada confere com o hash gravado, login é válido
    // password_verify compara a senha digitada com o hash criado por password_hash
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Salva os dados do usuário na sessão para uso nas demais páginas
        $_SESSION['usuario'] = $usuario;


        // Guarda o horário do login para controlar o tempo de expiração da sessão
        $_SESSION['ultimo_acesso'] = time();


        // Redireciona para a página principal do sistema
        header("Location: principal.php");
        exit();
    } else {
        // Caso contrário, prepara a mensagem de erro para exibir ao usuário
        $erro = "Usuário ou senha inválidos.";
    }
}
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">


<!-- Formulário HTML de login -->
<form method="post">
    Usuário: <input type="text" name="nome" required>
    Senha: <input type="password" name="senha" required>
    <button type="submit">Login</button>
    <!-- Exibe mensagem de erro se houver -->
    <?php if ($erro) echo "<div style='color:red;'>$erro</div>"; ?>
</form>
