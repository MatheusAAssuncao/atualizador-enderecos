# Atualizador de Endereços

PHP script para consumir uma API REST solicitando uma lista de coordenadas, buscar endereços usando serviços de geocodificação (Google Maps e OpenCage), e enviar os endereços encontrados de volta via POST.

## Arquitetura

O projeto foi refatorado seguindo princípios de Orientação a Objetos e design patterns para melhor reutilização de código e manutenibilidade:

### Padrões Implementados

- **Interface Segregation**: `GeocoderInterface` define o contrato para todos os provedores
- **Strategy Pattern**: Diferentes provedores de geocodificação podem ser usados intercambiavelmente
- **Chain of Responsibility**: O `GeocoderManager` tenta cada provedor em sequência até encontrar um resultado
- **Template Method**: `AbstractGeocoder` fornece funcionalidade comum para carregamento de URLs
- **Dependency Injection**: Provedores são injetados no manager, facilitando testes e configuração

### Estrutura de Classes

```
GeocoderInterface
├── AbstractGeocoder (classe abstrata)
│   ├── GMaps (Google Maps)
│   └── Opencage (OpenCage Data)
└── GeocoderManager (gerenciador de provedores)
```

### Arquivos Principais

- `GeocoderInterface.php` - Interface que define o contrato para provedores
- `AbstractGeocoder.php` - Classe base com funcionalidades comuns
- `GMaps.php` - Implementação para Google Maps API
- `Opencage.php` - Implementação para OpenCage Data API
- `GeocoderManager.php` - Gerenciador que coordena múltiplos provedores
- `Logger.php` - Sistema de logging centralizado
- `atualizador.php` - Script principal atualizado para usar a nova arquitetura

## Funcionalidades

### Múltiplos Provedores
- Suporte automático para Google Maps e OpenCage Data
- Fallback automático entre provedores em caso de falha
- Controle de limites diários por provedor

### Controle de Uso
- Monitoramento de requisições por provedor
- Limites configuráveis por provedor
- Estatísticas detalhadas de uso

### Logging Melhorado
- Sistema centralizado de logs
- Rastreamento de erros e sucessos por provedor
- Informações detalhadas para debugging

## Uso

### Configuração Básica

```php
// Crie o gerenciador
$manager = new GeocoderManager();

// Adicione provedores com limites opcionais
$manager->addProvider(new GMaps('sua_chave_google'), 1000);
$manager->addProvider(new Opencage('sua_chave_opencage'), 500);

// Use para geocodificação
$endereco = $manager->geoLocal(-23.550520, -46.633308);
```

### Configuração no .env

```
# Chaves de API
GMAPS_KEY=sua_chave_google_maps
OPENCAGE_KEY=sua_chave_opencage

# Limites diários
QTD_COORDINATES_GOOGLE=1000
QTD_COORDINATES_OPENCAGE=500
```

## Vantagens da Nova Arquitetura

1. **Reutilização**: Código comum centralizado na classe abstrata
2. **Extensibilidade**: Fácil adição de novos provedores
3. **Flexibilidade**: Configuração dinâmica de provedores e limites
4. **Robustez**: Fallback automático entre provedores
5. **Monitoramento**: Estatísticas detalhadas de uso
6. **Testabilidade**: Injeção de dependências facilita testes unitários

## Exemplos

Consulte `exemplos.php` para exemplos detalhados de uso das diferentes funcionalidades.
