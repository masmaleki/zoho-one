<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials;
use Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoTokenCheck;
use Psr\Http\Message\ResponseInterface;

/**
 * Shared request helper for the Zoho Books fulfilment endpoints (packages, shipment orders,
 * purchase receives, transfer orders, inventory adjustments, item batches).
 *
 * Same shape as the other Books helpers (`ZohoBooksWebhookController::request()`): the saved
 * access token, the per-organization Books host from `ZohoOrgCredentials::booksApiBaseUrl()`,
 * `organization_id` (the Zoho Books organization) as a query parameter, and an array return —
 * the decoded Zoho body on success, `['code' => ..., 'message' => ...]` on failure.
 *
 * Two differences, both additive:
 *  - A failure keeps what the caller needs to act on it: Zoho's own error `code` and `message`
 *    (decoded from the error body, not Guzzle's exception text), the HTTP status in `http_status`
 *    and, on HTTP 429, the `Retry-After` seconds in `retry_after`. `zoho_one_error` is true on
 *    every failure this helper builds. The access token is never part of a failure.
 *  - `$internal_organization_id` (the application's own organization id) is passed through to
 *    the token lookup and to the datacenter resolution; null keeps the ambient
 *    `zoho-one.current_internal_organization_id` behaviour of the older helpers.
 *
 * `useHttpClient()` / `resolveTokenUsing()` exist so an application test can replace the
 * transport (a Guzzle MockHandler) and the token source without any network or token table;
 * they mirror `ZohoOrgCredentials::resolveUsing()`. Pass null to restore the defaults.
 */
class ZohoBooksInventoryRequest
{
    /** @var callable|null fn(): ClientInterface */
    private static $clientFactory = null;

    /** @var callable|null fn(?int $internalOrganizationId): ?object (object with access_token) */
    private static $tokenResolver = null;

    public static function useHttpClient(?callable $factory): void
    {
        self::$clientFactory = $factory;
    }

    public static function resolveTokenUsing(?callable $resolver): void
    {
        self::$tokenResolver = $resolver;
    }

    /**
     * Turn the legacy string condition ("&status=shipped&date_start=2026-01-01") or an array
     * into a query array.
     *
     * @param  string|array<string, mixed>|null  $condition
     * @return array<string, mixed>
     */
    public static function conditionToQuery($condition): array
    {
        if (is_array($condition)) {
            return $condition;
        }

        $condition = ltrim((string) $condition, '&?');
        if ($condition === '') {
            return [];
        }

        parse_str($condition, $query);

        return is_array($query) ? $query : [];
    }

    /**
     * @param  string  $method  GET | POST | PUT | DELETE
     * @param  string  $endpoint  Path under /books/v3, e.g. "/packages/123"
     * @param  string|int|null  $organization_id  The Zoho Books organization id
     * @param  array<string, mixed>|null  $data  JSON body for POST / PUT (null sends no body)
     * @param  array<string, mixed>  $query
     * @param  int|null  $internal_organization_id  The application organization id (token + datacenter)
     * @return array<string, mixed>
     */
    public static function send($method, $endpoint, $organization_id, $data = null, $query = [], $internal_organization_id = null)
    {
        if (! $organization_id) {
            return self::failure(498, 'Invalid/missing token or organization ID.', null);
        }

        $token = self::$tokenResolver !== null
            ? (self::$tokenResolver)($internal_organization_id)
            : ZohoTokenCheck::getToken($internal_organization_id);

        if (! $token || empty($token->access_token)) {
            return self::failure(498, 'Invalid/missing token or organization ID.', null);
        }

        $query['organization_id'] = $organization_id;
        $apiURL = ZohoOrgCredentials::booksApiBaseUrl($internal_organization_id) . '/books/v3' . $endpoint;

        $options = [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
                'Accept' => 'application/json',
            ],
            'query' => $query,
            'http_errors' => true,
            'timeout' => 60,
        ];

        if (in_array($method, ['POST', 'PUT'], true) && $data !== null) {
            $options['json'] = $data;
        }

        try {
            $client = self::$clientFactory !== null ? (self::$clientFactory)() : new Client();
            $response = $client->request($method, $apiURL, $options);

            $decoded = json_decode((string) $response->getBody(), true);

            return is_array($decoded) ? $decoded : ['code' => 0, 'message' => 'success'];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            if ($response !== null) {
                return self::failureFromResponse($response);
            }

            return self::failure($e->getCode() ?: 0, self::scrub($e->getMessage()), null);
        } catch (\Throwable $e) {
            return self::failure($e->getCode() ?: 0, self::scrub($e->getMessage()), null);
        }
    }

    /** @return array<string, mixed> */
    private static function failureFromResponse(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $body = json_decode((string) $response->getBody(), true);

        $code = is_array($body) && isset($body['code']) && is_numeric($body['code']) ? (int) $body['code'] : $status;
        $message = is_array($body) && isset($body['message']) && is_string($body['message'])
            ? $body['message']
            : ($response->getReasonPhrase() ?: 'Zoho Books request failed.');

        $retryAfter = null;
        $header = $response->getHeaderLine('Retry-After');
        if ($header !== '' && is_numeric($header)) {
            $retryAfter = (int) $header;
        }

        return self::failure($code, self::scrub($message), $status, $retryAfter);
    }

    /** @return array<string, mixed> */
    private static function failure($code, $message, $httpStatus, $retryAfter = null): array
    {
        return [
            'code' => $code,
            'message' => $message,
            'http_status' => $httpStatus,
            'retry_after' => $retryAfter,
            'zoho_one_error' => true,
        ];
    }

    /** Remove anything that looks like a credential from a message before it is returned. */
    private static function scrub(string $message): string
    {
        $message = preg_replace('/Zoho-oauthtoken\s+\S+/i', 'Zoho-oauthtoken [redacted]', $message) ?? $message;
        $message = preg_replace('/((?:access|refresh)_token["\']?\s*[=:]\s*["\']?)[^&"\'\s]+/i', '$1[redacted]', $message) ?? $message;

        return mb_substr($message, 0, 1000);
    }
}
