<?php

// =====================================================
// 1. CONEXÃO COM O BANCO DE DADOS
// =====================================================

include_once(__DIR__ . '/../../config/conexao.php');

$mensagem = '';


// =====================================================
// 2. VERIFICA SE O ID DO CONTATO FOI INFORMADO
// =====================================================

if (!isset($_GET['id']) || empty($_GET['id'])) {

    header("Location: home.php");
    exit();

}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);


// =====================================================
// 3. PROCESSAMENTO DA ATUALIZAÇÃO
// =====================================================

if (isset($_POST['upContato'])) {

    // Obtém os dados enviados pelo formulário
    $nome  = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $fone  = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);


    // =================================================
    // BUSCA A FOTO ATUAL DO CONTATO
    // =================================================

    $selectFoto = "SELECT foto_contatos
                   FROM tb_contatos
                   WHERE id_contatos = :id";

    $stmtFoto = $conect->prepare($selectFoto);

    $stmtFoto->bindParam(':id', $id, PDO::PARAM_INT);

    $stmtFoto->execute();

    $fotoAtual = $stmtFoto->fetchColumn();

    if (!$fotoAtual) {
        $fotoAtual = 'avatar-padrao.png';
    }


    // Por padrão, mantém a foto antiga
    $novoNome = $fotoAtual;


    // =================================================
    // 4. VERIFICA SE FOI ENVIADA UMA NOVA FOTO
    // =================================================

    if (
        !empty($_FILES['foto']['name']) &&
        $_FILES['foto']['error'] === UPLOAD_ERR_OK
    ) {

        // Formatos permitidos
        $formatP = array(
            "png",
            "jpg",
            "jpeg",
            "gif"
        );

        // Pega a extensão
        $extensao = strtolower(
            pathinfo(
                $_FILES['foto']['name'],
                PATHINFO_EXTENSION
            )
        );


        // Verifica se o formato é permitido
        if (in_array($extensao, $formatP)) {

            // Pasta onde as fotos serão salvas
            $pasta = "../../img/cont/";

            // Cria um novo nome para a imagem
            $novoNome = uniqid() . "." . $extensao;


            // Move a imagem para a pasta
            if (
                move_uploaded_file(
                    $_FILES['foto']['tmp_name'],
                    $pasta . $novoNome
                )
            ) {

                // Apaga a foto antiga,
                // mas não apaga o avatar padrão
                if (
                    $fotoAtual != 'avatar-padrao.png' &&
                    file_exists($pasta . $fotoAtual)
                ) {

                    unlink($pasta . $fotoAtual);

                }

            } else {

                // Se não conseguiu enviar,
                // mantém a foto antiga
                $novoNome = $fotoAtual;

                $mensagem = '<div class="alert alert-danger">
                                Erro, não foi possível fazer o upload da foto.
                             </div>';
            }

        } else {

            $mensagem = '<div class="alert alert-danger">
                            Formato de imagem inválido.
                            Use PNG, JPG, JPEG ou GIF.
                         </div>';

        }

    }


    // =================================================
    // 5. ATUALIZA OS DADOS NO BANCO
    // =================================================

    $update = "UPDATE tb_contatos
               SET nome_contatos = :nome,
                   fone_contatos = :fone,
                   email_contatos = :email,
                   foto_contatos = :foto
               WHERE id_contatos = :id";

    try {

        $result = $conect->prepare($update);

        $result->bindParam(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $result->bindParam(
            ':nome',
            $nome,
            PDO::PARAM_STR
        );

        $result->bindParam(
            ':fone',
            $fone,
            PDO::PARAM_STR
        );

        $result->bindParam(
            ':email',
            $email,
            PDO::PARAM_STR
        );

        $result->bindParam(
            ':foto',
            $novoNome,
            PDO::PARAM_STR
        );

        $result->execute();


        // Mensagem de sucesso
        $mensagem = '<div class="alert alert-success alert-dismissible fade show">

                        <button type="button"
                                class="close"
                                data-dismiss="alert">
                            &times;
                        </button>

                        <h5>
                            <i class="icon fas fa-check"></i>
                            Sucesso!
                        </h5>

                        Os dados foram atualizados com sucesso.

                     </div>';


    } catch (PDOException $e) {

        error_log(
            "ERRO PDO UPDATE: " . $e->getMessage()
        );

        $mensagem = '<div class="alert alert-danger alert-dismissible fade show">

                        <button type="button"
                                class="close"
                                data-dismiss="alert">
                            &times;
                        </button>

                        <h5>
                            <i class="icon fas fa-ban"></i>
                            Erro!
                        </h5>

                        Não foi possível atualizar os dados.

                     </div>';
    }

}


// =====================================================
// 6. BUSCA OS DADOS ATUAIS DO CONTATO
// =====================================================

$nome  = '';
$fone  = '';
$email = '';
$foto  = 'avatar-padrao.png';


try {

    $select = "SELECT *
               FROM tb_contatos
               WHERE id_contatos = :id
               LIMIT 1";

    $resultado = $conect->prepare($select);

    $resultado->bindParam(
        ':id',
        $id,
        PDO::PARAM_INT
    );

    $resultado->execute();


    if ($resultado->rowCount() > 0) {

        $show = $resultado->fetch(PDO::FETCH_OBJ);

        $nome  = $show->nome_contatos;
        $fone  = $show->fone_contatos;
        $email = $show->email_contatos;
        $foto  = $show->foto_contatos;

    } else {

        $mensagem = '<div class="alert alert-danger">
                        Não há dados com o ID informado!
                     </div>';

    }


} catch (PDOException $e) {

    error_log(
        "ERRO PDO SELECT: " . $e->getMessage()
    );

}

?>


<!-- =====================================================
     7. PÁGINA DE EDIÇÃO
===================================================== -->

<div class="content-wrapper">


    <!-- CABEÇALHO -->

    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>Editar Contato</h1>

                </div>

            </div>

        </div>

    </section>


    <!-- CONTEÚDO -->

    <section class="content">

        <div class="container-fluid">


            <!-- MENSAGEM -->

            <?php

            if (!empty($mensagem)) {
                echo $mensagem;
            }

            ?>


            <!-- LINHA COM OS DOIS CARDS -->

            <div class="row">


                <!-- =================================================
                     CARD 1 - FORMULÁRIO DE EDIÇÃO
                ================================================== -->

                <div class="col-md-6">

                    <div class="card card-primary">


                        <div class="card-header">

                            <h3 class="card-title">
                                Editar Contato
                            </h3>

                        </div>


                        <form
                            role="form"
                            action=""
                            method="post"
                            enctype="multipart/form-data"
                        >


                            <div class="card-body">


                                <!-- NOME -->

                                <div class="form-group">

                                    <label for="nome">
                                        Nome
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="nome"
                                        id="nome"
                                        required
                                        value="<?php echo htmlspecialchars($nome); ?>"
                                    >

                                </div>


                                <!-- TELEFONE -->

                                <div class="form-group">

                                    <label for="telefone">
                                        Telefone
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        name="telefone"
                                        id="telefone"
                                        required
                                        value="<?php echo htmlspecialchars($fone); ?>"
                                    >

                                </div>


                                <!-- E-MAIL -->

                                <div class="form-group">

                                    <label for="email">
                                        Endereço de E-mail
                                    </label>

                                    <input
                                        type="email"
                                        class="form-control"
                                        name="email"
                                        id="email"
                                        required
                                        value="<?php echo htmlspecialchars($email); ?>"
                                    >

                                </div>


                                <!-- FOTO -->

                                <div class="form-group">

                                    <label for="foto">
                                        Foto do contato
                                    </label>

                                    <div class="input-group">

                                        <div class="custom-file">

                                            <input
                                                type="file"
                                                class="custom-file-input"
                                                name="foto"
                                                id="foto"
                                                accept=".png,.jpg,.jpeg,.gif"
                                            >

                                            <label
                                                class="custom-file-label"
                                                for="foto"
                                            >
                                                Escolher nova imagem...
                                            </label>

                                        </div>

                                    </div>

                                </div>


                            </div>


                            <!-- BOTÃO -->

                            <div class="card-footer">

                                <button
                                    type="submit"
                                    name="upContato"
                                    class="btn btn-primary"
                                >

                                    Finalizar edição do contato

                                </button>

                            </div>


                        </form>

                    </div>

                </div>



                <!-- =================================================
                     CARD 2 - DADOS DO CONTATO
                ================================================== -->

                <div class="col-md-6">


                    <!-- Card de Exibição dos Dados do Contato -->

                    <div class="card">


                        <div class="card-header">

                            <h3 class="card-title">
                                Dados do Contato
                            </h3>

                        </div>


                        <!-- /.card-header -->


                        <div
                            class="card-body p-0"
                            style="text-align: center; margin-bottom: 98px"
                        >


                            <?php

                            // Verifica se a foto é igual ao avatar padrão

                            if ($show->foto_contatos == 'avatar-padrao.png') {


                                // Exibe o avatar padrão

                                echo '<img
                                        src="../img/avatar_p/' . $show->foto_contatos . '"
                                        alt="' . $show->foto_contatos . '"
                                        title="' . $show->foto_contatos . '"
                                        style="
                                            width: 200px;
                                            border-radius: 100%;
                                            margin-top: 30px;
                                        "
                                      >';


                            } else {


                                // Exibe a foto personalizada

                                echo '<img
                                        src="../img/cont/' . $show->foto_contatos . '"
                                        alt="' . $show->foto_contatos . '"
                                        title="' . $show->foto_contatos . '"
                                        style="
                                            width: 200px;
                                            border-radius: 100%;
                                            margin-top: 30px;
                                        "
                                      >';

                            }

                            ?>


                            <h1>
                                <?php echo htmlspecialchars($nome); ?>
                            </h1>


                            <strong>
                                <?php echo htmlspecialchars($fone); ?>
                            </strong>


                            <p>
                                <?php echo htmlspecialchars($email); ?>
                            </p>


                        </div>


                        <!-- /.card-body -->


                    </div>


                </div>


            </div>

            <!-- /.row -->


        </div>

        <!-- /.container-fluid -->

    </section>

    <!-- /.content -->


</div>

<!-- /.content-wrapper -->