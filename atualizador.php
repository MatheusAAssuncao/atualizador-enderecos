<?php
include __DIR__ . '/get_values_from_dot_env.php';

$qtd = 10;
$response = curl(getenv('ENDPOINT_GET_COORDINATES'), ['qtd' => $qtd]);

print_r($response);
exit;

file_put_contents(getenv('LOG_PATH') . '/atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . ']  Executado' . PHP_EOL, FILE_APPEND);

function curl($endpoint, $postData) {
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, getenv('API_URL') . '/' . $endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, getenv('USER_AUTH') . ':' . getenv('PASS_AUTH'));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            file_put_contents(getenv('LOG_PATH') . '/atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . ']  Erro: ' . curl_error($ch) . PHP_EOL, FILE_APPEND);
        }

        curl_close($ch);

        return $response;
    } catch (Exception $e) {
        file_put_contents(getenv('LOG_PATH') . '/atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . ']  Erro: ' . $e->getMessage() . ' ' . $e->getTraceAsString() . PHP_EOL, FILE_APPEND);
    }
}