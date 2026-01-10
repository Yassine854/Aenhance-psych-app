<?php

function loadEnv(string $path): array
{
    if (!is_file($path)) {
        throw new RuntimeException("Missing .env at {$path}");
    }

    $vars = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = substr($line, 0, $pos);
        $value = substr($line, $pos + 1);
        $vars[$key] = $value;
    }

    return $vars;
}

$env = loadEnv(__DIR__ . '/../.env');
$kid = $env['JAAS_KID'] ?? '';
$app = $env['JAAS_APP_ID'] ?? '';
$keyPath = $env['JAAS_PRIVATE_KEY_PATH'] ?? '';
$key = $env['JAAS_PRIVATE_KEY'] ?? '';

echo 'kid=' . ($kid !== '' ? 'set' : 'missing') . PHP_EOL;
echo 'app=' . ($app !== '' ? 'set' : 'missing') . PHP_EOL;
echo 'key_path=' . ($keyPath !== '' ? $keyPath : 'missing') . PHP_EOL;

if ($keyPath !== '') {
    $candidate = $keyPath;
    if (!is_file($candidate)) {
        $candidate = __DIR__ . '/../' . ltrim($keyPath, '/\\');
    }
    if (is_file($candidate)) {
        $contents = file_get_contents($candidate);
        if ($contents !== false) {
            $key = $contents;
        }
    } else {
        echo "key_path_exists=no" . PHP_EOL;
    }
}

echo 'key=' . ($key !== '' ? 'set' : 'missing') . PHP_EOL;
if ($key !== '') {
    echo 'key_len_raw=' . strlen($key) . PHP_EOL;
}

if ($key === '') {
    exit(2);
}

// Remove surrounding quotes if present.
if ($key[0] === '"' && substr($key, -1) === '"') {
    $key = substr($key, 1, -1);
}

$key = str_replace(['\\r\\n', '\\n', '\\r'], ["\n", "\n", "\n"], $key);
$key = str_replace(["\r\n", "\r"], "\n", $key);

echo 'key_len_norm=' . strlen($key) . PHP_EOL;
if (str_contains($key, 'BEGIN ENCRYPTED PRIVATE KEY')) {
    echo "key_type=ENCRYPTED" . PHP_EOL;
} elseif (str_contains($key, 'BEGIN RSA PRIVATE KEY')) {
    echo "key_type=RSA" . PHP_EOL;
} elseif (str_contains($key, 'BEGIN PRIVATE KEY')) {
    echo "key_type=PKCS8" . PHP_EOL;
} else {
    echo "key_type=UNKNOWN" . PHP_EOL;
}

$pkey = openssl_pkey_get_private($key);
if (!$pkey) {
    echo "openssl_pkey_get_private: FAIL" . PHP_EOL;
    while ($msg = openssl_error_string()) {
        echo "openssl_error: {$msg}" . PHP_EOL;
    }
    exit(3);
}

echo "openssl_pkey_get_private: OK" . PHP_EOL;

$sig = '';
$ok = openssl_sign('test', $sig, $pkey, OPENSSL_ALGO_SHA256);

echo 'openssl_sign: ' . ($ok ? 'OK' : 'FAIL') . PHP_EOL;
exit($ok ? 0 : 4);
