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
 *                 oauth_scope, api_base_url, location, books_api_base_url
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
        return static::resolverValue($organizationId, $key)
            ?? (config('zoho-one.' . $key) ?: null);
    }

    /**
     * Resolve a credential key purely from the registered resolver, WITHOUT
     * the config fallback. Returns null when there is no resolver or the
     * resolver yields an empty value. Callers use this when they need to know
     * whether an organization has an explicit override of its own.
     */
    private static function resolverValue(int $organizationId, string $key): ?string
    {
        if (static::$resolver !== null) {
            $value = (static::$resolver)($organizationId, $key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Resolve the Zoho Books API base URL for an organization's datacenter.
     *
     * Zoho Books hosts are datacenter-specific (EU = https://www.zohoapis.eu,
     * US = https://www.zohoapis.com, etc.). Different organizations can live on
     * different datacenters, so this MUST be resolved per-organization — a
     * single global value would send an org's access token (minted on its own
     * datacenter) to the wrong datacenter, where Zoho rejects it with
     * 401 "you are not authorized to perform this operation" (code 57).
     *
     * Resolution order (mirrors the per-org pattern used for accounts_url /
     * api_base_url in ZohoCustomTokenStore::refreshToken):
     *   1) explicit per-org Books override  (books_api_base_url)
     *   2) the org's datacenter location     (location: us/eu/in/...)
     *   3) the global package default        (config zoho-one.books_api_base_url)
     *
     * The organization id defaults to the ambient
     * current_internal_organization_id — the same source getToken() uses — so
     * callers that have already set org context need pass nothing.
     *
     * @return string Scheme-prefixed host, no trailing slash, ready for
     *                "/books/v3/..." to be appended (e.g. "https://www.zohoapis.com").
     */
    public static function booksApiBaseUrl(?int $organizationId = null): string
    {
        $orgId = (int) ($organizationId ?: config('zoho-one.current_internal_organization_id'));

        if ($orgId > 0) {
            $override = static::resolverValue($orgId, 'books_api_base_url');
            if ($override !== null) {
                return static::normalizeBooksHost($override);
            }

            $location = static::resolverValue($orgId, 'location');
            if ($location !== null) {
                return static::booksHostForLocation($location);
            }
        }

        return static::normalizeBooksHost((string) config('zoho-one.books_api_base_url'));
    }

    /**
     * Map a Zoho datacenter location code to its Books API base URL.
     * Unknown codes fall back to the global configured Books host.
     */
    private static function booksHostForLocation(string $location): string
    {
        return match (strtolower(trim($location))) {
            'us', 'com'   => 'https://www.zohoapis.com',
            'eu'          => 'https://www.zohoapis.eu',
            'in'          => 'https://www.zohoapis.in',
            'au', 'com.au' => 'https://www.zohoapis.com.au',
            'jp'          => 'https://www.zohoapis.jp',
            'ca'          => 'https://www.zohoapis.ca',
            'sa'          => 'https://www.zohoapis.sa',
            default       => static::normalizeBooksHost((string) config('zoho-one.books_api_base_url')),
        };
    }

    /**
     * Normalize a host/URL into a scheme-prefixed base URL with no trailing
     * slash, so "/books/v3/..." can be appended and Guzzle receives an
     * absolute URL.
     *
     * Accepts "zohoapis.com", "www.zohoapis.eu" and
     * "https://www.zohoapis.com" alike. An empty value falls back to the EU
     * datacenter (Zoho's most common host for this app).
     */
    private static function normalizeBooksHost(string $host): string
    {
        $host = trim(rtrim($host, '/'));

        if ($host === '') {
            return 'https://www.zohoapis.eu';
        }

        // Already scheme-qualified — leave as-is.
        if (preg_match('#^https?://#i', $host)) {
            return $host;
        }

        // Bare "zohoapis.eu" -> add the conventional "www." subdomain.
        if (preg_match('#^zohoapis\.#i', $host)) {
            $host = 'www.' . $host;
        }

        return 'https://' . $host;
    }
}
