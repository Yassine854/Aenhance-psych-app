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

function normalizePem(string $pem): string
{
    $pem = trim($pem);
    if ((str_starts_with($pem, '"') && str_ends_with($pem, '"')) || (str_starts_with($pem, "'") && str_ends_with($pem, "'"))) {
        $pem = substr($pem, 1, -1);
    }
    $pem = str_replace(['\\r\\n', '\\n', '\\r'], ["\n", "\n", "\n"], $pem);
    $pem = str_replace(["\r\n", "\r"], "\n", $pem);
    return $pem;
}

function pemFingerprintSha256(string $pem): string
{
    return hash('sha256', $pem);
}

$env = loadEnv(__DIR__ . '/../.env');
$app = $env['JAAS_APP_ID'] ?? '';
$kid = $env['JAAS_KID'] ?? '';
$keyPath = $env['JAAS_PRIVATE_KEY_PATH'] ?? '';

echo 'app=' . ($app !== '' ? $app : 'missing') . PHP_EOL;
echo 'kid=' . ($kid !== '' ? $kid : 'missing') . PHP_EOL;
echo 'key_path=' . ($keyPath !== '' ? $keyPath : 'missing') . PHP_EOL;

if ($keyPath === '') {
    echo "ERROR: JAAS_PRIVATE_KEY_PATH not set" . PHP_EOL;
    exit(2);
}

$candidate = $keyPath;
if (!is_file($candidate)) {
    $candidate = realpath(__DIR__ . '/../' . ltrim($keyPath, '/\\')) ?: $candidate;
}

if (!is_file($candidate)) {
    echo "ERROR: key file not found: {$candidate}" . PHP_EOL;
    exit(3);
}

$privatePem = file_get_contents($candidate);
if ($privatePem === false) {
    echo "ERROR: failed to read key file" . PHP_EOL;
    exit(4);
}

$privatePem = normalizePem($privatePem);

$pkey = openssl_pkey_get_private($privatePem);
if (!$pkey) {
    echo "ERROR: openssl_pkey_get_private failed" . PHP_EOL;
    while ($msg = openssl_error_string()) {
        echo "openssl_error: {$msg}" . PHP_EOL;
    }
    exit(5);
}

$details = openssl_pkey_get_details($pkey);
if (!$details || empty($details['key'])) {
    echo "ERROR: openssl_pkey_get_details failed" . PHP_EOL;
    exit(6);
}

$derivedPublicPem = normalizePem($details['key']);

echo 'derived_public_sha256=' . pemFingerprintSha256($derivedPublicPem) . PHP_EOL;

$pubPath = $argv[1] ?? '';
if ($pubPath !== '') {
    if (!is_file($pubPath)) {
        echo "ERROR: public key file not found: {$pubPath}" . PHP_EOL;
        exit(7);
    }
    $pubPem = normalizePem((string) file_get_contents($pubPath));
    echo 'provided_public_sha256=' . pemFingerprintSha256($pubPem) . PHP_EOL;
    echo 'public_keys_match=' . (hash_equals(pemFingerprintSha256($derivedPublicPem), pemFingerprintSha256($pubPem)) ? 'YES' : 'NO') . PHP_EOL;
}

// Optional: show the derived public key (safe) if requested
if (($argv[2] ?? '') === '--print-public') {
    echo PHP_EOL . $derivedPublicPem . PHP_EOL;
}

exit(0);
