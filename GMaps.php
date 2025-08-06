<?php

require_once __DIR__ . '/AbstractGeocoder.php';

class GMaps extends AbstractGeocoder {
    
    public function getProviderName(): string {
        return 'Google Maps';
    }

    public function geoLocal(float $latitude, float $longitude): string|false {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?key={$this->apiKey}&language=en&latlng={$latitude},{$longitude}";
        $data = json_decode($this->carregaUrl($url));

        if ($data && $data->status === 'OK' && isset($data->results[0]->address_components)) {
            $array = json_decode(json_encode($data->results[0]->address_components),true);	
    
            $allInOne = $array[1]['long_name'];													 	
            $allInOne .= isset($array[0]['long_name']) ? ', ' . $array[0]['long_name'] : '';
            $allInOne .= isset($array[2]['long_name']) ? ' - ' . $array[2]['long_name'] : '';
            $allInOne .= isset($array[3]['long_name']) ? ' - ' . $array[3]['long_name'] : '';
            $allInOne .= isset($array[4]['short_name']) ? ' - ' . $array[4]['short_name'] : '';

            return $allInOne;
        }

        
        return false;
    }
}