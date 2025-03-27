<?php

class GMaps {
    private string $mapsKey;

    public function __construct(string $key) {
        if (empty($key)) {
            throw new InvalidArgumentException('A chave da API do Google Maps é obrigatória.');
        }
        $this->mapsKey = $key;
    }

    private function carregaUrl(string $url): string {
        if (function_exists('curl_init')) {
            $cURL = curl_init($url);
            curl_setopt_array($cURL, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 10
            ]);
            $resultado = curl_exec($cURL);
            $httpCode = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
            curl_close($cURL);

            if ($httpCode !== 200 || !$resultado) {
                throw new RuntimeException("Falha ao carregar a URL: {$url}");
            }
        } else {
            $resultado = @file_get_contents($url);
            if ($resultado === false) {
                throw new RuntimeException("Falha ao carregar a URL: {$url}");
            }
        }

        return $resultado;
    }

    public function geoLocal(float $latitude, float $longitude): array|false {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?key={$this->mapsKey}&language=en&latlng={$latitude},{$longitude}";
        $data = json_decode($this->carregaUrl($url));

        if ($data && $data->status === 'OK' && isset($data->results[0]->address_components)) {
            return $data->results[0]->address_components;
        }

        return false;
    }
}