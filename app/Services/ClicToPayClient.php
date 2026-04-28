<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ClicToPayClient
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $language;
    private ?string $caBundle;
    private bool $sslVerify;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.clictopay.base_url'), '/');
        $this->username = (string) config('services.clictopay.username');
        $this->password = (string) config('services.clictopay.password');
        $this->language = (string) (config('services.clictopay.language') ?: 'en');
        $this->caBundle = $this->resolveCaBundlePath((string) config('services.clictopay.ca_bundle'));
        $this->sslVerify = (bool) config('services.clictopay.ssl_verify', true);
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->username !== '' && $this->password !== '';
    }

    /**
     * @return array{orderId?:string,formUrl?:string,errorCode?:int|string,errorMessage?:string,orderNumber?:string}
     */
    public function register(array $params): array
    {
        $this->assertConfigured();

        $payload = array_merge([
            'userName' => $this->username,
            'password' => $this->password,
            'language' => $this->language,
        ], $this->normalizeRegisterParams($params));

        // ClickToPay supports GET and POST; we use POST form-encoded.
        $response = Http::asForm()
            ->withOptions($this->requestOptions())
            ->timeout(20)
            ->post($this->baseUrl.'/register.do', $payload);

        return $this->decodeJson($response->throw());
    }

    /**
     * @return array{OrderStatus?:int|string,ErrorCode?:int|string,ErrorMessage?:string,OrderNumber?:string,Amount?:int|string}
     */
    public function getOrderStatus(string $orderId): array
    {
        $this->assertConfigured();

        $payload = [
            'userName' => $this->username,
            'password' => $this->password,
            'language' => $this->language,
            'orderId' => $orderId,
        ];

        $response = Http::asForm()
            ->withOptions($this->requestOptions())
            ->timeout(20)
            ->post($this->baseUrl.'/getOrderStatus.do', $payload);

        return $this->decodeJson($response->throw());
    }

    /**
     * Extended order status includes actionCode/actionCodeDescription.
     *
     * @return array<string,mixed>
     */
    public function getOrderStatusExtended(string $orderId): array
    {
        $this->assertConfigured();

        $payload = [
            'userName' => $this->username,
            'password' => $this->password,
            'language' => $this->language,
            'orderId' => $orderId,
        ];

        $response = Http::asForm()
            ->withOptions($this->requestOptions())
            ->timeout(20)
            ->post($this->baseUrl.'/getOrderStatusExtended.do', $payload);

        return $this->decodeJson($response->throw());
    }

    public function makeSafeOrderNumber(string $prefix = 'APT'): string
    {
        // Keep it short and avoid forbidden characters.
        return $prefix.'-'.Str::lower(Str::random(8)).'-'.now()->format('YmdHis');
    }

    /**
     * ClickToPay expects ISO-4217 numeric currency code (e.g., 788 for TND).
     */
    public function currencyToIso4217Numeric(string $currency): string
    {
        $c = strtoupper(trim($currency));
        if ($c === '') {
            return '788';
        }

        if (preg_match('/^\d{3}$/', $c)) {
            return $c;
        }

        return match ($c) {
            'TND' => '788',
            'EUR' => '978',
            'USD' => '840',
            default => throw new \InvalidArgumentException('Unsupported currency for ClickToPay: '.$currency),
        };
    }

    /**
     * Convert a decimal amount to minor units as required by ClickToPay.
     *
     * Note: Some currencies (e.g. TND) use 3 decimal minor units (millimes).
     */
    public function amountToMinorUnits($amount, string $currency): int
    {
        $c = strtoupper(trim($currency ?: ''));

        $exponent = match ($c) {
            'TND' => 3,
            'EUR' => 2,
            'USD' => 2,
            default => 2,
        };

        $multiplier = 10 ** $exponent;
        $v = (float) $amount;

        return (int) round($v * $multiplier);
    }

    private function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('ClickToPay is not configured. Set CLICTOPAY_BASE_URL/USERNAME/PASSWORD.');
        }
    }

    private function requestOptions(): array
    {
        if (! $this->sslVerify) {
            return ['verify' => false];
        }

        if ($this->caBundle) {
            return ['verify' => $this->caBundle];
        }

        return ['verify' => true];
    }

    private function normalizeRegisterParams(array $params): array
    {
        $normalized = $params;

        if (array_key_exists('orderNumber', $normalized)) {
            $normalized['orderNumber'] = $this->sanitizeOrderNumber((string) $normalized['orderNumber']);
        }

        if (array_key_exists('description', $normalized)) {
            $normalized['description'] = $this->sanitizeDescription((string) $normalized['description']);
        }

        if (array_key_exists('pageView', $normalized)) {
            $normalized['pageView'] = $this->normalizePageView((string) $normalized['pageView']);
        }

        if (! array_key_exists('sessionTimeoutSecs', $normalized) && ! array_key_exists('expirationDate', $normalized)) {
            $normalized['sessionTimeoutSecs'] = 1200;
        }

        return $normalized;
    }

    private function sanitizeOrderNumber(string $value): string
    {
        $value = $this->stripForbiddenBankCharacters($value);
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('ClickToPay orderNumber cannot be empty.');
        }

        return Str::limit($value, 32, '');
    }

    private function sanitizeDescription(string $value): string
    {
        $value = $this->stripForbiddenBankCharacters($value);
        $value = trim($value);

        return Str::limit($value, 99, '');
    }

    private function stripForbiddenBankCharacters(string $value): string
    {
        return str_replace(["%", "+", "\r", "\n"], ' ', $value);
    }

    private function normalizePageView(string $value): string
    {
        return strtoupper(trim($value)) === 'MOBILE' ? 'MOBILE' : 'DESKTOP';
    }

    private function resolveCaBundlePath(string $configuredPath): ?string
    {
        $candidates = array_filter([
            trim($configuredPath),
            (string) ini_get('curl.cainfo'),
            (string) ini_get('openssl.cafile'),
            'C:/xampp/apache/bin/curl-ca-bundle.crt',
            'C:/xampp/php/extras/ssl/cacert.pem',
        ]);

        foreach ($candidates as $candidate) {
            $normalized = str_replace('\\', '/', trim($candidate));
            if ($normalized !== '' && is_file($normalized) && is_readable($normalized)) {
                return $normalized;
            }
        }

        return null;
    }

    private function decodeJson($response): array
    {
        try {
            $json = $response->json();
            return is_array($json) ? $json : [];
        } catch (RequestException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Some gateways return non-JSON; keep failure explicit.
            throw new \RuntimeException('Unexpected ClickToPay response format: '.$e->getMessage(), 0, $e);
        }
    }
}
