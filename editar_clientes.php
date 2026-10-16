<?php
// Inicia a sessão para acessar os dados do usuário logado
session_start();
// Verifica se o usuário está autenticado; caso contrário, redireciona para a página de login
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
// Inclui o arquivo de configuração para estabelecer a conexão com a base de dados
include 'config.php';
// Recebe o ID do cliente a ser editado via parâmetro GET na URL; se não informado, assume 0
$id = $_GET['id'] ?? 0;
// Prepara e executa uma consulta para buscar os dados do cliente com o ID informado
// AES_DECRYPT descriptografa o CPF para aparecer preenchido no formulário
$sql = "SELECT id, nome, CAST(AES_DECRYPT(cpf, '$chave') AS CHAR) AS cpf, telefone, email FROM clientes WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$cliente = $res->fetch_assoc();
// Se não encontrou o cliente, exibe mensagem e termina a execução
if (!$cliente) {
    echo "Cliente não encontrado.";
    exit();
}
// Processa o formulário quando enviado via POST para atualizar o cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados atualizados do formulário
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    // Validação simples: nome e CPF são obrigatórios
    if (!$nome || !$cpf) {
        echo "Nome e CPF são obrigatórios.";
    } else {
        // Atualiza os dados do cliente no banco de dados (o CPF é gravado criptografado de novo)
        $sql = "UPDATE clientes SET nome=?, cpf=AES_ENCRYPT(?, '$chave'), telefone=?, email=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $cpf, $telefone, $email, $id);
        $stmt->execute();
        // Redireciona o usuário de volta para a lista de clientes após atualizar
        header("Location: criar_clientes.php");
        exit();
    }
}
?>
<!-- Folha de estilo padrão do sistema (todas as telas usam o mesmo arquivo css/style.css) -->
<link rel="stylesheet" href="css/style.css">

<h2>Editar Cliente</h2>
<!-- Formulário para edição do cliente com dados já preenchidos -->
<form method="post">
    <input name="nome" placeholder="Nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
    <input name="cpf" placeholder="CPF (só números)" maxlength="11" pattern="[0-9]{11}" value="<?= htmlspecialchars($cliente['cpf'] ?? '') ?>" required>
    <input name="telefone" placeholder="Telefone" value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>">
    <input type="email" name="email" placeholder="E-mail" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
    <button>Salvar</button>
</form>
<!-- Link para voltar à página de listagem de clientes -->
<a href="criar_clientes.php">Voltar</a>
