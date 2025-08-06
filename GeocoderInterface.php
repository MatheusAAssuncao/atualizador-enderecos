<?php

/**
 * Interface para serviços de geocodificação reversa
 * Define o contrato que todos os provedores de geocodificação devem seguir
 */
interface GeocoderInterface {
    /**
     * Realiza geocodificação reversa a partir de coordenadas
     * 
     * @param float $latitude A latitude da coordenada
     * @param float $longitude A longitude da coordenada
     * @return string|false O endereço formatado ou false em caso de erro
     */
    public function geoLocal(float $latitude, float $longitude): string|false;
    
    /**
     * Retorna o nome do provedor de geocodificação
     * 
     * @return string Nome do provedor
     */
    public function getProviderName(): string;
    
    /**
     * Verifica se o provedor está configurado corretamente
     * 
     * @return bool True se está configurado, false caso contrário
     */
    public function isConfigured(): bool;
}
