<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;

/**
 * Zoho Books / Inventory transfer orders (stock moved between two warehouses).
 *
 * Documented in the Zoho Inventory reference; UNVERIFIED on live Books (/books/v3). Requires the
 * multi-warehouse feature on the Books organization.
 */
class ZohoTransferOrderController
{
    private const ENDPOINT = '/transferorders';

    /** GET /books/v3/transferorders?page=&... */
    public static function getAll($organization_id, $page = 1, $condition = '', $internal_organization_id = null)
    {
        $query = array_merge(ZohoBooksInventoryRequest::conditionToQuery($condition), ['page' => $page]);

        return ZohoBooksInventoryRequest::send('GET', self::ENDPOINT, $organization_id, null, $query, $internal_organization_id);
    }

    /** GET /books/v3/transferorders/{id} */
    public static function get($transfer_order_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('GET', self::path($transfer_order_id), $organization_id, null, [], $internal_organization_id);
    }

    /**
     * POST /books/v3/transferorders
     * Payload: transfer_order_number, date, from_warehouse_id, to_warehouse_id,
     * line_items[] (item_id, quantity_transfer, batches[] — UNVERIFIED), is_intransit_order.
     */
    public static function create(array $payload, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('POST', self::ENDPOINT, $organization_id, $payload, [], $internal_organization_id);
    }

    /** PUT /books/v3/transferorders/{id} */
    public static function update($transfer_order_id, array $payload, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('PUT', self::path($transfer_order_id), $organization_id, $payload, [], $internal_organization_id);
    }

    /** DELETE /books/v3/transferorders/{id} */
    public static function delete($transfer_order_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('DELETE', self::path($transfer_order_id), $organization_id, null, [], $internal_organization_id);
    }

    /**
     * POST /books/v3/transferorders/{id}/markastransferred
     * UNVERIFIED endpoint: taken from the Zoho Inventory reference ("mark as received"); not yet
     * confirmed to exist under /books/v3. A 404 / Zoho code 5 ("Invalid URL") here means it does not.
     */
    public static function markTransferred($transfer_order_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('POST', self::path($transfer_order_id) . '/markastransferred', $organization_id, null, [], $internal_organization_id);
    }

    private static function path($id): string
    {
        return self::ENDPOINT . '/' . rawurlencode((string) $id);
    }
}
