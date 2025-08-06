<?php

require_once __DIR__ . '/GeocoderInterface.php';

/**
 * Classe abstrata base para provedores de geocodificação
 * Implementa funcionalidades comuns como carregamento de URL
 */
abstract class AbstractGeocoder implements GeocoderInterface {
    protected string $apiKey;
    
    public function __construct(string $apiKey) {
        if (empty($apiKey)) {
            throw new InvalidArgumentException("A chave da API do {$this->getProviderName()} é obrigatória.");
        }
        $this->apiKey = $apiKey;
    }
    
    /**
     * Carrega uma URL usando cURL ou file_get_contents
     * 
     * @param string $url A URL a ser carregada
     * @return string O conteúdo da resposta
     * @throws RuntimeException Se falhar ao carregar a URL
     */
    protected function carregaUrl(string $url): string {
        if (function_exists('curl_init')) {
            return $this->loadUrlWithCurl($url);
        } else {
            return $this->loadUrlWithFileGetContents($url);
        }
    }
    
    /**
     * Carrega URL usando cURL
     * 
     * @param string $url A URL a ser carregada
     * @return string O conteúdo da resposta
     * @throws RuntimeException Se falhar ao carregar a URL
     */
    private function loadUrlWithCurl(string $url): string {
        $cURL = curl_init($url);
        curl_setopt_array($cURL, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 ATS',
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $resultado = curl_exec($cURL);
        $httpCode = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
        $error = curl_error($cURL);
        curl_close($cURL);
        
        if ($httpCode !== 200 || !$resultado || !empty($error)) {
            throw new RuntimeException("Falha ao carregar a URL: {$url}. HTTP Code: {$httpCode}. Error: {$error}");
        }
        
        return $resultado;
    }
    
    /**
     * Carrega URL usando file_get_contents
     * 
     * @param string $url A URL a ser carregada
     * @return string O conteúdo da resposta
     * @throws RuntimeException Se falhar ao carregar a URL
     */
    private function loadUrlWithFileGetContents(string $url): string {
        $resultado = @file_get_contents($url);
        if ($resultado === false) {
            throw new RuntimeException("Falha ao carregar a URL: {$url}");
        }
        
        return $resultado;
    }
    
    public function isConfigured(): bool {
        return !empty($this->apiKey);
    }
    
    /**
     * Método abstrato que deve ser implementado pelas classes filhas
     */
    abstract public function geoLocal(float $latitude, float $longitude): string|false;
    
    /**
     * Método abstrato que deve ser implementado pelas classes filhas
     */
    abstract public function getProviderName(): string;
}
