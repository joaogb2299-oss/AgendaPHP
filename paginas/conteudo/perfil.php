<?php
// 1. Conexão com o banco de dados
include_once(__DIR__ . '/../../config/conexao.php');

$mensagem = '';

// Garante que o ID do usuário existe na sessão
$id_user = $_SESSION['senhaUser'] ?? null;

if (!$id_user) {
    header("Location: ../index.php?acao=negado");
    exit();
}

// 2. PROCESSAMENTO DO FORMULÁRIO DE EDIÇÃO DO PERFIL
if (isset($_POST['upPerfil'])) {
    $nome       = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email      = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senha_nova = $_POST['senha'] ?? '';

    // Busca os dados antigos do usuário no banco de dados
    $query = "SELECT foto_user, email_user, senha_user FROM tb_user WHERE id_user = :id LIMIT 1";
    $stmt = $conect->prepare($query);
    $stmt->bindParam(':id', $id_user, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $foto_antiga  = $row['foto_user'] ?? 'avatar-padrao.png';
    $email_antigo = $row['email_user'] ?? '';
    $senha_antiga = $row['senha_user'] ?? '';

    $novoNomeFoto = $foto_antiga;

    // Upload da foto de perfil com caminho absoluto seguro
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $formatP = array("png", "jpg", "jpeg", "gif");
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (in_array($extensao, $formatP)) {
            // Mapeia o caminho absoluto do servidor até a pasta img/user
            $pastaServidor = realpath(__DIR__ . '/../../img') . '/user/';

            // Cria o diretório caso não exista no servidor
            if (!file_exists($pastaServidor)) {
                @mkdir($pastaServidor, 0777, true);
            }

            $novoNomeFoto = uniqid() . "." . $extensao;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $pastaServidor . $novoNomeFoto)) {
                // Remove a foto antiga se não for a padrão
                if ($foto_antiga != 'avatar-padrao.png' && file_exists($pastaServidor . $foto_antiga)) {
                    @unlink($pastaServidor . $foto_antiga);
                }
            } else {
                $novoNomeFoto = $foto_antiga;
                $mensagem = '<div class="alert alert-warning alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Atenção!</h5> Não foi possível salvar a nova imagem por permissão do servidor. Execute o comando "sudo chmod -R 777 img" no terminal.
                </div>'; 
                
            }
        }
    }

    // Criptografia da nova senha (se fornecida)
    if (!empty($senha_nova)) {
        $senha_final = password_hash($senha_nova, PASSWORD_DEFAULT);
    } else {
        $senha_final = $senha_antiga;
    }

    // Atualiza os dados no banco
    $update = "UPDATE tb_user SET foto_user = :foto, nome_user = :nome, email_user = :email, senha_user = :senha WHERE id_user = :id";
    try {
        $result = $conect->prepare($update);
        $result->bindParam(':id', $id_user, PDO::PARAM_INT);
        $result->bindParam(':foto', $novoNomeFoto, PDO::PARAM_STR);
        $result->bindParam(':nome', $nome, PDO::PARAM_STR);
        $result->bindParam(':email', $email, PDO::PARAM_STR);
        $result->bindParam(':senha', $senha_final, PDO::PARAM_STR);
        $result->execute();

        // Se alterou e-mail ou senha, força o logout por segurança
        if ($email !== $email_antigo || !empty($senha_nova)) {
            header("Location: ../includes/sair.php");
            exit();
        }

        if (empty($mensagem)) {
            $mensagem = '<div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5><i class="icon fas fa-check"></i> Ok!</h5> Perfil atualizado com sucesso.
            </div>';
        }
    } catch (PDOException $e) {
        error_log("ERRO PDO PERFIL: " . $e->getMessage());
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Erro!</h5> Não foi possível atualizar o perfil.
        </div>';
    }
}

// 3. SELEÇÃO DOS DADOS ATUAIS PARA PREENCHER A TELA
try {
    $select = "SELECT * FROM tb_user WHERE id_user = :id LIMIT 1";
    $stmtUser = $conect->prepare($select);
    $stmtUser->bindParam(':id', $id_user, PDO::PARAM_INT);
    $stmtUser->execute();
    $userData = $stmtUser->fetch(PDO::FETCH_OBJ);

    $nome_user  = $userData->nome_user ?? '';
    $email_user = $userData->email_user ?? '';
    $foto_user  = $userData->foto_user ?? 'avatar-padrao.png';
} catch (PDOException $e) {
    error_log("ERRO SELECT USER: " . $e->getMessage());
}
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Editar Perfil</h1>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      
      <?php if (!empty($mensagem)) echo $mensagem; ?>

      <div class="row">
        <div class="col-md-6">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Editar Perfil</h3>
            </div>
            
            <form role="form" action="" method="post" enctype="multipart/form-data">
              <div class="card-body">
                <div class="form-group">
                  <label for="nome">Nome</label>
                  <input type="text" class="form-control" name="nome" id="nome" required value="<?php echo htmlspecialchars($nome_user); ?>">
                </div>
                
                <div class="form-group">
                  <label for="email">Endereço de E-mail</label>
                  <input type="email" class="form-control" name="email" id="email" required value="<?php echo htmlspecialchars($email_user); ?>">
                </div>

                <div class="form-group">
                  <label for="senha">Nova Senha (deixe em branco se não quiser alterar)</label>
                  <input type="password" class="form-control" name="senha" id="senha" placeholder="**************************">
                </div>

                <div class="form-group">
                  <label for="foto">Avatar do usuário</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" name="foto" id="foto">
                      <label class="custom-file-label" for="foto">Arquivo de imagem</label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card-footer">
                <button type="submit" name="upPerfil" class="btn btn-primary">Alterar dados do usuário</button>
              </div>
            </form>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Dados do Usuário</h3>
            </div>
            
            <div class="card-body p-0" style="text-align: center; margin-bottom: 30px;">
              <?php
                if ($foto_user == 'avatar-padrao.png' || empty($foto_user)) {
                    echo '<img src="img/avatar_p/avatar-padrao.png" alt="Avatar" style="width: 180px; height: 180px; object-fit: cover; border-radius: 50%; margin-top: 30px;">';
                } else {
                    echo '<img src="img/user/' . htmlspecialchars($foto_user) . '" alt="User Foto" style="width: 180px; height: 180px; object-fit: cover; border-radius: 50%; margin-top: 30px;">';
                }
              ?>

              <h1 class="mt-3"><?php echo htmlspecialchars($nome_user); ?></h1>
              <strong><?php echo htmlspecialchars($email_user); ?></strong>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
</div>