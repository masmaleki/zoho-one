<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;

/**
 * Batches (lots) of one batch-tracked Zoho Books item.
 *
 * UNVERIFIED ENDPOINT SHAPE. Neither the Books nor the Inventory API reference documents a
 * stable "list an item's batches" endpoint. Strategy, in order:
 *
 *  1. GET /books/v3/items/{item_id}/batches — returns the body as-is (expected key `batches`).
 *  2. When (1) answers HTTP 404 or Zoho code 5 ("Invalid URL Passed") — i.e. the endpoint does
 *     not exist — fall back to GET /books/v3/items/{item_id} and return `item.batches` when the
 *     item detail carries it, else an empty list.
 *
 * The return always carries `batches_source`: `batches_endpoint`, `item_detail` or
 * `item_detail_without_batches`, so the caller can tell "no batches" from "not exposed".
 * Any other failure of (1) (auth, rate limit, 5xx) is returned unchanged — no fallback.
 */
class ZohoItemBatchController
{
    public static function getItemBatches($item_id, $organization_id, $internal_organization_id = null)
    {
        $path = '/items/' . rawurlencode((string) $item_id);

        $response = ZohoBooksInventoryRequest::send('GET', $path . '/batches', $organization_id, null, [], $internal_organization_id);

        if (! self::endpointMissing($response)) {
            if (($response['code'] ?? null) === 0 || ! isset($response['zoho_one_error'])) {
                $response['batches'] = is_array($response['batches'] ?? null) ? $response['batches'] : [];
                $response['batches_source'] = 'batches_endpoint';
            }

            return $response;
        }

        $item = ZohoBooksInventoryRequest::send('GET', $path, $organization_id, null, [], $internal_organization_id);
        if (isset($item['zoho_one_error'])) {
            return $item;
        }

        $batches = $item['item']['batches'] ?? null;

        return [
            'code' => 0,
            'message' => 'success',
            'batches' => is_array($batches) ? $batches : [],
            'batches_source' => is_array($batches) ? 'item_detail' : 'item_detail_without_batches',
        ];
    }

    /** @param array<string, mixed> $response */
    private static function endpointMissing(array $response): bool
    {
        if (! isset($response['zoho_one_error'])) {
            return false;
        }

        return ($response['http_status'] ?? null) === 404 || (int) ($response['code'] ?? -1) === 5;
    }
}
