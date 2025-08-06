<?php
include __DIR__ . '/get_values_from_dot_env.php';
include __DIR__ . '/Logger.php';
include __DIR__ . '/GeocoderManager.php';
include __DIR__ . '/GMaps.php';
include __DIR__ . '/Opencage.php';

// if (empty($_SERVER['HTTP_X_CRON_TOKEN']) || $_SERVER['HTTP_X_CRON_TOKEN'] !== getenv('SECURE_TOKEN_CRON_JOB')) {
//     echo "Unauthorized";
//     exit;
// }

// Configuração dos limites
$qtd_google = (int) getenv('QTD_COORDINATES_GOOGLE');
$qtd_opencage = (int) getenv('QTD_COORDINATES_OPENCAGE');

$response = curl(getenv('ENDPOINT_GET_COORDINATES'), ['qtd' => $qtd_google + $qtd_opencage]);
if (empty(json_decode($response, true))) {
    logger('Erro no endpoint ENDPOINT_GET_COORDINATES: ' . $response);
    exit;
}

// Configuração do gerenciador de geocodificação
$geocoderManager = new GeocoderManager();

try {
    // Adiciona Google Maps como primeiro provedor se configurado
    if (!empty(getenv('GMAPS_KEY'))) {
        $gmaps = new GMaps(getenv('GMAPS_KEY'));
        $geocoderManager->addProvider($gmaps, $qtd_google);
    }

    // Adiciona OpenCage como segundo provedor se configurado
    if (!empty(getenv('OPENCAGE_KEY'))) {
        $opencage = new Opencage(getenv('OPENCAGE_KEY'));
        $geocoderManager->addProvider($opencage, $qtd_opencage);
    }
} catch (Exception $e) {
    logger('Erro ao configurar provedores de geocodificação: ' . $e->getMessage());
    exit;
}

$postData = [];
$response = json_decode($response, true);
$i = 0;
foreach ($response['coordenadas'] as $coordenada) {
    try {
        $i++;
        $endereco = $geocoderManager->geoLocal($coordenada['lat'], $coordenada['lon']);
        
        if ($i > 20) {
            // Se já processou mais de 20 coordenadas, pode parar
            break;
        }
        
        if (empty($endereco)) {
            continue;
        }

        $postData[] = array(
            'descricao' => $endereco,
            'lat' => $coordenada['lat'],
            'lon' => $coordenada['lon']
        );
    } catch (Exception $e) {
        logger('Erro ao processar coordenada [' . $coordenada['lat'] . ', ' . $coordenada['lon'] . ']: ' . $e->getMessage());
        continue;
    }
}
print_r([$postData]);
exit;
if (!empty($postData)) {
    $response = curl(getenv('ENDPOINT_POST_ADDRESSES'), ['enderecos' => $postData]);
    if (empty(json_decode($response, true))) {
        logger('Erro no endpoint ENDPOINT_POST_ADDRESSES: ' . $response);
        exit;
    }

    $response = json_decode($response, true);
    if (!empty($response['message'])) {
        logger($response['message']);
    }
}

// Log das estatísticas de uso dos provedores
$stats = $geocoderManager->getUsageStats();
$statsMessage = 'Estatísticas de uso dos provedores: ' . json_encode($stats);
logger($statsMessage);

logger('Fim da execução. Tempo: ' . (microtime(true)-$_SERVER["REQUEST_TIME_FLOAT"]) . 's');

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
