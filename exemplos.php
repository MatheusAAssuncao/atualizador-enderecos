<?php

/**
 * Exemplo de uso do sistema de geocodificação refatorado
 */

require_once __DIR__ . '/GeocoderManager.php';
require_once __DIR__ . '/GMaps.php';
require_once __DIR__ . '/Opencage.php';
require_once __DIR__ . '/Logger.php';

// Exemplo 1: Uso básico com um único provedor
echo "=== Exemplo 1: Uso básico ===\n";

try {
    $gmaps = new GMaps('sua_chave_google_maps');
    $endereco = $gmaps->geoLocal(-23.550520, -46.633308); // São Paulo
    echo "Endereço: " . ($endereco ?: 'Não encontrado') . "\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

// Exemplo 2: Uso do GeocoderManager com múltiplos provedores
echo "\n=== Exemplo 2: Múltiplos provedores ===\n";

try {
    $manager = new GeocoderManager();
    
    // Adiciona Google Maps como primeiro provedor (limite de 100 requisições)
    $gmaps = new GMaps('sua_chave_google_maps');
    $manager->addProvider($gmaps, 100);
    
    // Adiciona OpenCage como segundo provedor (limite de 50 requisições)
    $opencage = new Opencage('sua_chave_opencage');
    $manager->addProvider($opencage, 50);
    
    // Tenta geocodificar usando o primeiro provedor disponível
    $endereco = $manager->geoLocal(-23.550520, -46.633308);
    echo "Endereço: " . ($endereco ?: 'Não encontrado') . "\n";
    
    // Mostra estatísticas de uso
    print_r($manager->getUsageStats());
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

// Exemplo 3: Testando fallback entre provedores
echo "\n=== Exemplo 3: Testando fallback ===\n";

try {
    $manager = new GeocoderManager();
    
    // Simulando um provedor com chave inválida (falhará)
    $gmapsInvalido = new GMaps('chave_invalida');
    $manager->addProvider($gmapsInvalido, 10);
    
    // Provedor válido como backup
    $opencage = new Opencage('sua_chave_opencage_valida');
    $manager->addProvider($opencage, 10);
    
    $endereco = $manager->geoLocal(-23.550520, -46.633308);
    echo "Endereço com fallback: " . ($endereco ?: 'Não encontrado') . "\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

// Exemplo 4: Verificando configuração dos provedores
echo "\n=== Exemplo 4: Verificação de configuração ===\n";

$providers = [
    new GMaps('chave_valida'),
    new Opencage(''),  // Chave vazia - inválida
];

foreach ($providers as $provider) {
    echo $provider->getProviderName() . ": " . 
         ($provider->isConfigured() ? "Configurado" : "Não configurado") . "\n";
}
