<?php

require_once __DIR__ . '/AbstractGeocoder.php';

class Opencage extends AbstractGeocoder {
    
    public function getProviderName(): string {
        return 'OpenCage';
    }

    public function geoLocal(float $latitude, float $longitude): string|false {
        $url = "https://api.opencagedata.com/geocode/v1/json?q={$latitude},{$longitude}&key={$this->apiKey}&address_only=1&language=pt";
        $data = json_decode($this->carregaUrl($url));

        if (empty(json_decode($data, true))) {
            if (function_exists('logger')) {
                logger("Empty response for coordinates: $latitude, $longitude - URL: $url");
            }
            return false;
        }

        $results = json_decode($data, true);
        if (empty($results['results']) || !isset($results['results'][0]['formatted'])) {
            return false;
        }

        $address = $results['results'][0]['formatted'];
        $address = str_replace("'", "", $address);
        $address = str_replace('"', '', $address);
        $address = str_replace("  ", " ", $address);
        $address = trim($address);
        
        if (strpos($address, 'unnamed') !== false) {
            if (function_exists('logger')) {
                logger("Address contains 'unnamed': $address - Coordinates: $latitude, $longitude");
            }
            return false;
        }

        if (empty($address)) {
            if (function_exists('logger')) {
                logger("Empty address for coordinates: $latitude, $longitude - Response: " . json_encode($results));
            }
            return false;
        }
        
        return $address;                    
    }
}