<?php
include __DIR__ . '/get_values_from_dot_env.php';
include __DIR__ . '/GMaps.php';

if (empty($_SERVER['x-cron-token']) || $_SERVER['x-cron-token'] !== getenv('SECURE_TOKEN_CRON_JOB')) {
    echo "Unauthorized";
    exit;
}

$qtd = 100;
$response = curl(getenv('ENDPOINT_GET_COORDINATES'), ['qtd' => $qtd]);
if (empty(json_decode($response, true))) {
    file_put_contents(getenv('LOG_PATH') . '/' . date('Y-m') . '-atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . '] Erro no endpoint ENDPOINT_GET_COORDINATES: ' . $response . PHP_EOL, FILE_APPEND);
    exit;
}

$gmaps = new GMaps(getenv('GMAPS_KEY'));

$response = json_decode($response, true);
foreach ($response['coordenadas'] as $coordenada) {
    $endereco = $gmaps->geoLocal($coordenada['lat'], $coordenada['lon']);

    $array = json_decode(json_encode($endereco),true);	
    
    $allInOne = $array[1]['long_name'];													 	
    $allInOne .= isset($array[0]['long_name']) ? ', ' . $array[0]['long_name'] : '';
    $allInOne .= isset($array[2]['long_name']) ? ' - ' . $array[2]['long_name'] : '';
    $allInOne .= isset($array[3]['long_name']) ? ' - ' . $array[3]['long_name'] : '';
    $allInOne .= isset($array[4]['short_name']) ? ' - ' . $array[4]['short_name'] : '';

    if (empty($allInOne)) {
        continue;
    }

    $postData[] = array(
        'descricao' => $allInOne,
        'lat' => $coordenada['lat'],
        'lon' => $coordenada['lon']
    );
}

if (!empty($postData)) {
    $response = curl(getenv('ENDPOINT_POST_ADDRESSES'), ['enderecos' => $postData]);
    if (empty(json_decode($response, true))) {
        file_put_contents(getenv('LOG_PATH') . '/' . date('Y-m') . '-atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . '] Erro no endpoint ENDPOINT_POST_ADDRESSES: ' . $response . PHP_EOL, FILE_APPEND);
        exit;
    }

    $response = json_decode($response, true);
    if (!empty($response['message'])) {
        file_put_contents(getenv('LOG_PATH') . '/' . date('Y-m') . '-atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . '] ' . $response['message'] . PHP_EOL, FILE_APPEND);
    }
}

file_put_contents(getenv('LOG_PATH') . '/' . date('Y-m') . '-atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . '] Fim da execução. Tempo: ' . (microtime(true)-$_SERVER["REQUEST_TIME_FLOAT"]) . PHP_EOL, FILE_APPEND);

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
            file_put_contents(getenv('LOG_PATH') . '/' . date('Y-m') . '-atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . '] Erro: ' . curl_error($ch) . PHP_EOL, FILE_APPEND);
        }

        curl_close($ch);

        return $response;
    } catch (Exception $e) {
        file_put_contents(getenv('LOG_PATH') . '/' . date('Y-m') . '-atualizador-enderecos.log', '[' . date('Y-m-d H:i:s') . '] Erro: ' . $e->getMessage() . ' ' . $e->getTraceAsString() . PHP_EOL, FILE_APPEND);
    }
}
