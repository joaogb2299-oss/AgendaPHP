<?php
// 1. Configuração de segurança dos cookies ANTES do session_start()
session_set_cookie_params([
    'lifetime' => 3600,             // Duração de 1 hora
    'path' => '/',
    'domain' => '',
    'secure' => false,              // Mude para true apenas se estiver usando HTTPS
    'httponly' => true,             // Impede acesso aos cookies via JavaScript (Proteção contra XSS)
    'samesite' => 'Strict'          // Proteção contra requisições forçadas (CSRF)
]);

session_start();

// 2. Se o usuário JÁ ESTIVER LOGADO, redireciona direto para a home
if (isset($_SESSION['loginUser']) && isset($_SESSION['senhaUser'])) {
    header("Location: paginas/home.php");
    exit();
}

include_once('config/conexao.php');

// PROCESSAMENTO DO FORMULÁRIO DE LOGIN
if (isset($_POST['login'])) {

    $login = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senha = $_POST['senha'] ?? '';

    if (!$login || empty($senha)) {
        header("Location: index.php?acao=campos");
        exit();
    }

    $select = "SELECT * FROM tb_user WHERE email_user = :emailLogin LIMIT 1";

    try {
        $resultLogin = $conect->prepare($select);
        $resultLogin->bindValue(':emailLogin', $login, PDO::PARAM_STR);
        $resultLogin->execute();

        $user = $resultLogin->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verifica a senha digitada com o hash salvo
            if (password_verify($senha, $user['senha_user'])) {
                
                // Regenera o ID da sessão para prevenir Session Fixation
                session_regenerate_id(true);

                // Define as variáveis de sessão
                $_SESSION['loginUser'] = $user['email_user'];
                $_SESSION['senhaUser'] = $user['id_user'];
                $_SESSION['last_activity'] = time(); // Grava o horário da autenticação

                // Redireciona para a home
                header("Location: paginas/home.php?acao=bemvindo");
                exit();
            } else {
                header("Location: index.php?acao=senha");
                exit();
            }
        } else {
            header("Location: index.php?acao=usuario");
            exit();
        }

    } catch (PDOException $e) {
        error_log("ERRO DE LOGIN DO PDO: " . $e->getMessage());
        header("Location: index.php?acao=erro");
        exit();
    }
}

// DEFINIÇÃO DAS MENSAGENS DE ALERTA (GET)
$mensagem = '';

if (isset($_GET['acao'])) {
    switch ($_GET['acao']) {
        case 'negado':
            $mensagem = '<div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Erro ao acessar o sistema!</strong> Efetue o login.
            </div>';
            break;

        case 'sair':
            $mensagem = '<div class="alert alert-warning alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Você acabou de sair da Agenda Eletrônica!</strong>
            </div>';
            break;

        case 'senha':
            $mensagem = '<div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Erro!</strong> Senha incorreta, tente novamente.
            </div>';
            break;

        case 'usuario':
            $mensagem = '<div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Erro!</strong> E-mail não encontrado.
            </div>';
            break;

        case 'campos':
            $mensagem = '<div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Erro!</strong> Todos os campos são obrigatórios.
            </div>';
            break;

        case 'erro':
            $mensagem = '<div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Erro!</strong> Ocorreu um erro ao tentar fazer login.
            </div>';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>New Agenda 2.0 | Log in</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition login-page">

<div class="login-box">
  <div class="login-logo">
    <a href="index.php"><b>New Agenda</b> 2.0</a>
  </div>

  <div class="card">
    <div class="card-body login-card-body">

      <!-- Mensagem do sistema -->
      <?php echo $mensagem; ?>

      <p class="login-box-msg">Para acessar entre com E-mail e Senha</p>

      <form action="index.php" method="post">
        <!-- E-MAIL -->
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Digite seu E-mail..." required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>

        <!-- SENHA -->
        <div class="input-group mb-3">
          <input type="password" name="senha" class="form-control" placeholder="Digite sua Senha..." required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        <!-- BOTÃO -->
        <div class="row">
          <div class="col-12">
            <button type="submit" name="login" class="btn btn-primary btn-block">Acessar a Agenda</button>
          </div>
        </div>
      </form>

      <!-- CADASTRO -->
      <p style="text-align: center; padding-top: 25px">
        <a href="cad_user.php" class="text-center">Se ainda não tem cadastro clique aqui!</a>
      </p>

    </div>
  </div>
</div>

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="dist/js/adminlte.min.js"></script>

</body>
</html>