<?php
// 1. Conexão com o banco de dados
include_once(__DIR__ . '/../../config/conexao.php');

$mensagem = '';

// 2. PROCESSAMENTO DO FORMULÁRIO (INSERT)
if (isset($_POST['botao'])) {
    $nome       = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $telefone   = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $email      = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $id_usuario = filter_input(INPUT_POST, 'id_user', FILTER_SANITIZE_NUMBER_INT);

    $foto = 'avatar-padrao.png'; // Imagem padrão
    $formatP = array("png", "jpg", "jpeg", "gif");

    // Upload de imagem
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (in_array($extensao, $formatP)) {
            $pasta = "../../img/cont/";
            
            if (!file_exists($pasta)) {
                mkdir($pasta, 0755, true);
            }

            $novoNome = uniqid() . "." . $extensao;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $pasta . $novoNome)) {
                $foto = $novoNome;
            }
        }
    }

    // Gravação no Banco de Dados
    if ($nome && $id_usuario) {
        $cadastro = "INSERT INTO tb_contatos (nome_contatos, fone_contatos, email_contatos, foto_contatos, id_user) 
                     VALUES (:nome, :telefone, :email, :foto, :id_user)";

        try {
            $result = $conect->prepare($cadastro);
            $result->bindParam(':nome', $nome, PDO::PARAM_STR);
            $result->bindParam(':telefone', $telefone, PDO::PARAM_STR);
            $result->bindParam(':email', $email, PDO::PARAM_STR);
            $result->bindParam(':foto', $foto, PDO::PARAM_STR);
            $result->bindParam(':id_user', $id_usuario, PDO::PARAM_INT);

            $result->execute();

            if ($result->rowCount() > 0) {
                $mensagem = '<div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-check"></i> OK!</h5> Contato inserido com sucesso!
                </div>';
            } else {
                $mensagem = '<div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Erro!</h5> Dados não inseridos.
                </div>';
            }
        } catch (PDOException $e) {
            error_log("ERRO DE PDO: " . $e->getMessage());
            $mensagem = '<div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5><i class="icon fas fa-ban"></i> Erro!</h5> Falha ao registrar contato.
            </div>';
        }
    }
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Cadastro de Contatos</h1>
        </div>
      </div>
    </div>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <!-- Exibe mensagem de alerta do PHP se houver -->
      <?php if (!empty($mensagem)) echo $mensagem; ?>

      <div class="row">
        <!-- Coluna do Formulário -->
        <div class="col-md-4">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Cadastrar contato</h3>
            </div>
            
            <form role="form" action="" method="post" enctype="multipart/form-data">
              <div class="card-body">
                <div class="form-group">
                  <label for="nome">Nome</label>
                  <input type="text" class="form-control" name="nome" id="nome" required placeholder="Digite o nome de contato">
                </div>
                
                <div class="form-group">
                  <label for="telefone">Telefone</label>
                  <input type="text" class="form-control" name="telefone" id="telefone" required placeholder="(00) 00000-0000">
                </div>
                
                <div class="form-group">
                  <label for="email">Endereço de E-mail</label>
                  <input type="email" class="form-control" name="email" id="email" required placeholder="Digite um e-mail">
                </div>
                
                <div class="form-group">
                  <label for="foto">Foto do contato</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" name="foto" id="foto">
                      <label class="custom-file-label" for="foto">Arquivo de imagem</label>
                    </div>
                  </div>
                </div>

                <input type="hidden" name="id_user" id="id_user" value="<?php echo htmlspecialchars($id_user ?? ''); ?>">

                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="exampleCheck1" required>
                  <label class="form-check-label" for="exampleCheck1">Autorizo o cadastro do meu contato</label>
                </div>
              </div>

              <div class="card-footer">
                <button type="submit" name="botao" class="btn btn-primary">Cadastrar Contato</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Coluna da Tabela de Contatos -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Contatos Recentes</h3>
            </div>
            
            <div class="card-body p-0">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th style="width: 10px">#</th>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th style="width: 40px">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Consulta SQL para selecionar os contatos do usuário atual
                  $select = "SELECT * FROM tb_contatos WHERE id_user = :id_user ORDER BY id_contatos DESC LIMIT 6";

                  try {
                      $result = $conect->prepare($select);
                      $cont = 1;

                      $result->bindParam(':id_user', $id_user, PDO::PARAM_INT);
                      $result->execute();

                      $contar = $result->rowCount();
                      if ($contar > 0) {
                          while ($show = $result->fetch(PDO::FETCH_OBJ)) {
                  ?>
                              <tr>
                                  <td><?php echo $cont++; ?></td>
                                  <td><?php echo htmlspecialchars($show->nome_contatos); ?></td>
                                  <td><?php echo htmlspecialchars($show->fone_contatos); ?></td>
                                  <td><?php echo htmlspecialchars($show->email_contatos); ?></td>
                                  <td>
                                      <div class="btn-group">
                                          <!-- Botão para editar o contato -->
                                          <a href="home.php?acao=editar&id=<?php echo $show->id_contatos; ?>" class="btn btn-success btn-sm" title="Editar Contato"><i class="fas fa-user-edit"></i></a>

                                          <!-- Botão para remover o contato -->
                                          <a href="conteudo/del-contato.php?idDel=<?php echo $show->id_contatos; ?>" onclick="return confirm('Deseja remover o contato?')" class="btn btn-danger btn-sm" title="Remover Contato"><i class="fas fa-user-times"></i></a>
                                      </div>
                                  </td>
                              </tr>
                  <?php
                          }
                      } else {
                          echo '<tr><td colspan="5" class="text-center">Nenhum contato encontrado.</td></tr>';
                      }
                  } catch (PDOException $e) {
                      echo '<tr><td colspan="5"><div class="alert alert-danger mb-0"><strong>ERRO DE PDO: </strong>' . $e->getMessage() . '</div></td></tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
</div>