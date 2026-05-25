<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Auth;

/**
 * Bridge between the package's static credential lookups and the host
 * application's dynamic per-organization credential storage.
 *
 * The host app registers a resolver callback once at boot time (e.g. in a
 * service provider or AppServiceProvider::boot):
 *
 *   ZohoOrgCredentials::resolveUsing(function (int $organizationId, string $key): ?string {
 *       return app(ZohoOrganizationConfigService::class)->get($key, $organizationId);
 *   });
 *
 * Supported keys: client_id, client_secret, accounts_url, redirect_uri,
 *                 oauth_scope, api_base_url, location
 */
class ZohoOrgCredentials
{
    /** @var callable|null */
    private static $resolver = null;

    /**
     * Register the application-level credential resolver.
     *
     * @param callable(int $organizationId, string $key): ?string $resolver
     */
    public static function resolveUsing(callable $resolver): void
    {
        static::$resolver = $resolver;
    }

    /**
     * Resolve a credential key for the given organization.
     * Falls back to config('zoho-one.<key>') when no resolver is registered
     * or the resolver returns null.
     */
    public static function get(int $organizationId, string $key): ?string
    {
        if (static::$resolver !== null) {
            $value = (static::$resolver)($organizationId, $key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return config('zoho-one.' . $key) ?: null;
    }
}
