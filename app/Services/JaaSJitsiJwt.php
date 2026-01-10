<?php

namespace App\Services;

use RuntimeException;

class JaaSJitsiJwt
{
    public function makeModeratorJwt(array $opts): string
    {
        $kid = (string) ($opts['kid'] ?? '');
        $appId = (string) ($opts['app_id'] ?? '');
        $privateKeyPem = (string) ($opts['private_key_pem'] ?? '');

        if ($kid === '' || $appId === '' || $privateKeyPem === '') {
            throw new RuntimeException('Missing JaaS JWT configuration (kid/app_id/private_key).');
        }

        $now = time();
        $ttl = (int) ($opts['ttl_seconds'] ?? 60 * 60);
        if ($ttl < 60) {
            $ttl = 60;
        }

        $displayName = (string) ($opts['display_name'] ?? 'User');
        $userId = (string) ($opts['user_id'] ?? '');
        $email = (string) ($opts['email'] ?? '');
        $room = (string) ($opts['room'] ?? '*');
        if ($room === '') {
            $room = '*';
        }

        $header = [
            'alg' => 'RS256',
            'kid' => $kid,
            'typ' => 'JWT',
        ];

        $payload = [
            'aud' => 'jitsi',
            'iss' => 'chat',
            'sub' => $appId,
            // Prefer scoping to the actual room; '*' can be rejected depending on tenant settings.
            'room' => $room,
            'iat' => $now,
            'nbf' => $now - 5,
            'exp' => $now + $ttl,
            'jti' => bin2hex(random_bytes(16)),
            'context' => [
                'user' => [
                    'id' => $userId !== '' ? $userId : null,
                    'name' => $displayName,
                    'email' => $email !== '' ? $email : null,
                    'moderator' => true,
                ],
                'features' => new \stdClass(),
            ],
        ];

        // Remove nulls to keep token compact.
        $payload['context']['user'] = array_filter($payload['context']['user'], fn ($v) => $v !== null);

        return $this->encodeRs256($header, $payload, $privateKeyPem);
    }

    private function encodeRs256(array $header, array $payload, string $privateKeyPem): string
    {
        $privateKeyPem = $this->normalizePrivateKeyPem($privateKeyPem);

        $segments = [];
        $segments[] = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $segments[] = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));

        $signingInput = implode('.', $segments);
        $signature = '';

        $key = openssl_pkey_get_private($privateKeyPem);
        if (! $key) {
            $details = [];
            while ($msg = openssl_error_string()) {
                $details[] = $msg;
            }
            $suffix = $details ? (' OpenSSL: ' . implode(' | ', $details)) : '';
            throw new RuntimeException('Invalid JaaS private key (could not be parsed).' . $suffix);
        }

        $ok = openssl_sign($signingInput, $signature, $key, OPENSSL_ALGO_SHA256);
        openssl_free_key($key);

        if (! $ok) {
            throw new RuntimeException('Failed to sign JaaS JWT.');
        }

        $segments[] = $this->base64UrlEncode($signature);
        return implode('.', $segments);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function normalizePrivateKeyPem(string $pem): string
    {
        $pem = trim($pem);

        // Some env setups wrap the key in quotes; remove them.
        if ((str_starts_with($pem, '"') && str_ends_with($pem, '"')) || (str_starts_with($pem, "'") && str_ends_with($pem, "'"))) {
            $pem = substr($pem, 1, -1);
        }

        // Convert escaped newlines from .env (e.g. "\n") into real newlines.
        $pem = str_replace(['\\r\\n', '\\n', '\\r'], ["\n", "\n", "\n"], $pem);

        // Normalize actual newlines too.
        $pem = str_replace(["\r\n", "\r"], "\n", $pem);

        return $pem;
    }
}
