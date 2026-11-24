<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | List of proxy IP addresses or CIDR ranges whose X-Forwarded-* headers
    | Laravel may use to determine the original client IP, host, port and
    | protocol. Leave empty to retain Laravel's default behavior. Set
    | TRUSTED_PROXIES to a comma-separated list in the environment when the
    | application is deployed behind specific proxies.
    |
    */
    'proxies' => (static function (): ?array {
        $proxies = array_values(array_filter(
            array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))),
            static fn (string $proxy): bool => $proxy !== ''
        ));

        return $proxies !== [] ? $proxies : null;
    })(),
];
