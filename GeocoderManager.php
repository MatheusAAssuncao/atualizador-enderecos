<?php

require_once __DIR__ . '/GeocoderInterface.php';

/**
 * Gerenciador de provedores de geocodificação
 * Implementa o padrão Strategy e Chain of Responsibility
 */
class GeocoderManager {
    private array $providers = [];
    private array $usage = [];
    private array $limits = [];
    
    /**
     * Adiciona um provedor de geocodificação
     * 
     * @param GeocoderInterface $provider O provedor a ser adicionado
     * @param int $dailyLimit Limite diário de requisições para este provedor
     * @return void
     */
    public function addProvider(GeocoderInterface $provider, int $dailyLimit = 0): void {
        if (!$provider->isConfigured()) {
            throw new InvalidArgumentException("O provedor {$provider->getProviderName()} não está configurado corretamente.");
        }
        
        $providerName = $provider->getProviderName();
        $this->providers[$providerName] = $provider;
        $this->limits[$providerName] = $dailyLimit;
        
        if (!isset($this->usage[$providerName])) {
            $this->usage[$providerName] = 0;
        }
    }
    
    /**
     * Obtém endereço usando o primeiro provedor disponível
     * 
     * @param float $latitude A latitude da coordenada
     * @param float $longitude A longitude da coordenada
     * @return string|false O endereço formatado ou false se todos falharam
     */
    public function geoLocal(float $latitude, float $longitude): string|false {
        foreach ($this->providers as $providerName => $provider) {
            // Verifica se ainda pode usar este provedor (limite diário)
            if ($this->limits[$providerName] > 0 && $this->usage[$providerName] >= $this->limits[$providerName]) {
                $this->logMessage("Limite diário atingido para o provedor: {$providerName}");
                continue;
            }
            
            try {
                $this->usage[$providerName]++;
                $result = $provider->geoLocal($latitude, $longitude);
                
                if ($result !== false) {
                    $this->logMessage("Endereço obtido com sucesso usando {$providerName}: {$result}");
                    return $result;
                }
                
                $this->logMessage("Provedor {$providerName} retornou false para coordenadas: {$latitude}, {$longitude}");
            } catch (Exception $e) {
                $this->logMessage("Erro no provedor {$providerName}: " . $e->getMessage());
                continue;
            }
        }
        
        $this->logMessage("Todos os provedores falharam para as coordenadas: {$latitude}, {$longitude}");
        return false;
    }
    
    /**
     * Obtém estatísticas de uso dos provedores
     * 
     * @return array Array com estatísticas de uso
     */
    public function getUsageStats(): array {
        $stats = [];
        foreach ($this->providers as $providerName => $provider) {
            $stats[$providerName] = [
                'used' => $this->usage[$providerName],
                'limit' => $this->limits[$providerName],
                'remaining' => $this->limits[$providerName] > 0 ? 
                    max(0, $this->limits[$providerName] - $this->usage[$providerName]) : 'Ilimitado'
            ];
        }
        return $stats;
    }
    
    /**
     * Reseta os contadores de uso (útil para testes ou reset diário)
     * 
     * @return void
     */
    public function resetUsage(): void {
        foreach ($this->usage as $providerName => &$count) {
            $count = 0;
        }
    }
    
    /**
     * Obtém a lista de provedores configurados
     * 
     * @return array Lista dos nomes dos provedores
     */
    public function getProviders(): array {
        return array_keys($this->providers);
    }
    
    /**
     * Registra mensagem de log
     * 
     * @param string $message A mensagem a ser registrada
     * @return void
     */
    private function logMessage(string $message): void {
        if (function_exists('logger')) {
            logger($message);
        } else {
            error_log('[GeocoderManager] ' . $message);
        }
    }
}
