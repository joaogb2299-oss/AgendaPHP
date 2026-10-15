<?php
// Inclui o arquivo de conexão com o banco de dados
include_once('../config/conexao.php');

// PASSO 1: Seleciona todos os contatos em ordem decrescente
$select = "SELECT * FROM tb_contatos ORDER BY id_contatos DESC";

try {

    $result = $conect->prepare($select);

    $cont = 1;

    $result->execute();

    // Verifica se existem registros
    $contar = $result->rowCount();

} catch (PDOException $e) {

    echo '<strong>ERRO DE PDO= </strong>' . $e->getMessage();

}
?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">


    <!-- Content Header -->
    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

            </div>

        </div>

    </section>


    <!-- Main content -->
    <section class="content">

        <div class="container-fluid">


            <!-- CARD DA LISTA DE CONTATOS -->

            <div class="card">


                <!-- Cabeçalho -->

                <div class="card-header">

                    <h3 class="card-title">
                        Lista de contatos
                    </h3>

                </div>


                <!-- Corpo do card -->

                <div class="card-body">


                    <!-- TABELA -->

                    <table
                        id="example1"
                        class="table table-bordered table-hover"
                    >


                        <!-- Cabeçalho da tabela -->

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Foto</th>

                                <th>Nome</th>

                                <th>Telefone</th>

                                <th>E-mail</th>

                                <th class="col-acoes">Ações</th>

                            </tr>

                        </thead>


                        <!-- CONTATOS -->

                        <tbody>

                            <?php

                            // Verifica se existem contatos

                            if ($contar > 0) {

                                // Percorre todos os contatos

                                while ($show = $result->fetch(PDO::FETCH_OBJ)) {

                            ?>

                                <tr>


                                    <!-- Número -->

                                    <td>

                                        <?php echo $cont++; ?>

                                    </td>


                                    <!-- Foto -->

                                    <td>

                                        <?php

                                        // Verifica se é o avatar padrão

                                        if ($show->foto_contatos == 'avatar-padrao.png') {

                                            echo '
                                            <img
                                                src="../img/avatar_p/' . htmlspecialchars($show->foto_contatos) . '"
                                                alt="Avatar"
                                                title="Avatar"
                                                style="width: 50px; border-radius: 100%;"
                                            >';

                                        } else {

                                            echo '
                                            <img
                                                src="../img/cont/' . htmlspecialchars($show->foto_contatos) . '"
                                                alt="Foto"
                                                title="Foto"
                                                style="width: 50px; border-radius: 100%;"
                                            >';

                                        }

                                        ?>

                                    </td>


                                    <!-- Nome -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars($show->nome_contatos);
                                        ?>

                                    </td>


                                    <!-- Telefone -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars($show->fone_contatos);
                                        ?>

                                    </td>


                                    <!-- E-mail -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars($show->email_contatos);
                                        ?>

                                    </td>


                                    <!-- Ações -->

                                    <td class="col-acoes">

                                        <div class="btn-group">


                                            <!-- Editar -->

                                            <a
                                                href="home.php?acao=editar&id=<?php echo $show->id_contatos; ?>"
                                                class="btn btn-success"
                                                title="Editar Contato"
                                            >

                                                <i class="fas fa-user-edit"></i>

                                            </a>


                                            <!-- Excluir -->

                                            <a
                                                href="conteudo/del-contato.php?idDel=<?php echo $show->id_contatos; ?>"
                                                onclick="return confirm('Deseja remover o contato')"
                                                class="btn btn-danger"
                                                title="Remover Contato"
                                            >

                                                <i class="fas fa-user-times"></i>

                                            </a>


                                        </div>

                                    </td>


                                </tr>


                            <?php

                                }

                            } else {

                                echo '
                                <tr>
                                    <td colspan="6" class="text-center">
                                        Nenhum contato cadastrado.
                                    </td>
                                </tr>';

                            }

                            ?>

                        </tbody>


                        <!-- Rodapé da tabela -->

                        <tfoot>

                            <tr>

                                <th>#</th>

                                <th>Foto</th>

                                <th>Nome</th>

                                <th>Telefone</th>

                                <th>E-mail</th>

                                <th class="col-acoes">Ações</th>

                            </tr>

                        </tfoot>


                    </table>


                    <!-- BOTÃO GERAR RELATÓRIO -->

                    <div class="col-lg-12 d-flex justify-content-center mt-3 no-print">

                        <button
                            type="button"
                            onclick="window.print()"
                            class="btn btn-lg btn-primary"
                        >

                            Gerar relatório completo

                        </button>

                    </div>


                </div>
                <!-- /.card-body -->


            </div>
            <!-- /.card -->


        </div>
        <!-- /.container-fluid -->

    </section>
    <!-- /.content -->


</div>
<!-- /.content-wrapper -->


<!-- CONFIGURAÇÃO DA IMPRESSÃO -->

<style>

@media print {

    /* Esconde partes do sistema */
    .main-sidebar,
    .main-header,
    .content-header,
    .no-print,
    .btn-group {
        display: none !important;
    }


    /* Faz o conteúdo ocupar toda a folha */
    .content-wrapper {
        margin-left: 0 !important;
        width: 100% !important;
    }


    .content {
        margin: 0 !important;
        padding: 0 !important;
    }


    .container-fluid {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }


    /* Remove aparência do card */
    .card {
        border: none !important;
        box-shadow: none !important;
    }


    .card-header {
        display: none !important;
    }


    .card-body {
        padding: 0 !important;
    }


    /* Título do relatório */
    .card::before {

        content: "RELATÓRIO COMPLETO DE CONTATOS";

        display: block;

        text-align: center;

        font-size: 24px;

        font-weight: bold;

        margin-bottom: 20px;

    }


    /* Esconde a coluna Ações */
    .col-acoes {
        display: none !important;
    }


    /* Tabela */
    table {

        width: 100% !important;

        border-collapse: collapse !important;

    }


    th,
    td {

        border: 1px solid #000 !important;

        padding: 8px !important;

    }


    /* Evita quebra estranha das linhas */
    tr {

        page-break-inside: avoid;

    }

}

</style>