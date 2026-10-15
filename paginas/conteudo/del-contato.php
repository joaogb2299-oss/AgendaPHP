                    <?php

// Conexão com o banco de dados
include('../../config/conexao.php');

// Verifica se o ID foi enviado pela URL
if (isset($_GET['idDel'])) {

    $id = $_GET['idDel'];

    // Busca a foto do contato
    $select = "SELECT foto_contatos FROM tb_contatos WHERE id_contatos = :id";

    try {

        $result = $conect->prepare($select);
        $result->bindValue(':id', $id, PDO::PARAM_INT);
        $result->execute();

        // Verifica se o contato existe
        if ($result->rowCount() > 0) {

            $show = $result->fetch(PDO::FETCH_OBJ);
            $foto = $show->foto_contatos;

            // Exclui a foto, caso não seja a imagem padrão
            if ($foto != 'avatar-padrao.png') {

                $filePath = "../../img/cont/" . $foto;

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Exclui o contato do banco de dados
            $delete = "DELETE FROM tb_contatos WHERE id_contatos = :id";

            try {

                $result = $conect->prepare($delete);
                $result->bindValue(':id', $id, PDO::PARAM_INT);
                $result->execute();

                // Volta para a página inicial
                header("Location: ../home.php");
                exit;

            } catch (PDOException $e) {

                echo "<strong>ERRO DE DELETE: </strong>" . $e->getMessage();
            }

        } else {

            // Contato não encontrado
            header("Location: ../home.php");
            exit;
        }

    } catch (PDOException $e) {

        echo "<strong>ERRO DE SELECT: </strong>" . $e->getMessage();
    }
}
?>
```
