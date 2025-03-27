<?php
if (file_exists(__DIR__ . '/.env')) {
    $file = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($file as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);

        $value = trim($value, ' \\"\'');
        putenv(trim($key) . '=' . $value);
    }
}