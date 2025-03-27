<?php

file_put_contents('~/logs/atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . ']  Executado' . PHP_EOL, FILE_APPEND);

define('USER_AUTH', 'admin');
define('PASS_AUTH', '123456');
define('API_URL', 'https://api.com.br');
