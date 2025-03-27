<?php

// get values from .env file

file_put_contents('/home1/farfal07/logs/atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . ']  Executado' . PHP_EOL, FILE_APPEND);

define('USER_AUTH', 'admin');
define('PASS_AUTH', '123456');
define('API_URL', 'https://api.com.br');
define('ENDPOINT_GET_COORDINATES', '/get-coordinates');
define('ENDPOINT_POST_ADDRESS', '/set-address');