<?php

try {

    defined('HOST') || define('HOST', 'localhost');
    defined('BD') || define('BD', 'new_agenda');
    defined('USER') || define('USER', 'agenda_user');
    defined('PASS') || define('PASS', 'bdjmf');

    $conect = new PDO(
        'mysql:host=' . HOST . ';dbname=' . BD . ';charset=utf8mb4',
        USER,
        PASS
    );

    $conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("ERRO DE CONEXÃO COM O BANCO DE DADOS: " . $e->getMessage());

}