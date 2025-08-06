<?php

/**
 * Função de logging simples
 * 
 * @param string $message A mensagem a ser registrada
 * @return void
 */
function logger(string $message): void {
    if (function_exists('getenv') && getenv('LOG_PATH')) {
        $logFile = getenv('LOG_PATH') . '/' . date('Y-m') . '-atualizador-enderecos.log';
        $timestamp = '[' . date('Y-m-d H:i:s') . ']';
        file_put_contents($logFile, $timestamp . ' ' . $message . PHP_EOL, FILE_APPEND);
    } else {
        error_log('[' . date('Y-m-d H:i:s') . '] ' . $message);
    }
}
