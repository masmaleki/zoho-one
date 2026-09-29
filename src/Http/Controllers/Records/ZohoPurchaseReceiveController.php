<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;

/**
 * Zoho Books / Inventory purchase receives (goods physically received against a purchase order).
 *
 * GET list/detail are already read on live Books by the backend ingestion. create/update/delete
 * are documented in the Zoho Inventory reference but UNVERIFIED on live Books; the batch shape on
 * receive lines (`line_items[].batches[]`) is UNVERIFIED as well.
 */
class ZohoPurchaseReceiveController
{
    private const ENDPOINT = '/purchasereceives';

    /** GET /books/v3/purchasereceives?page=&... */
    public static function getAll($organization_id, $page = 1, $condition = '', $internal_organization_id = null)
    {
        $query = array_merge(ZohoBooksInventoryRequest::conditionToQuery($condition), ['page' => $page]);

        return ZohoBooksInventoryRequest::send('GET', self::ENDPOINT, $organization_id, null, $query, $internal_organization_id);
    }

    /** GET /books/v3/purchasereceives/{id} */
    public static function get($purchase_receive_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('GET', self::path($purchase_receive_id), $organization_id, null, [], $internal_organization_id);
    }

    /**
     * POST /books/v3/purchasereceives?purchaseorder_id={po}
     * Payload: receive_number, date, line_items[] (line_item_id of the PO line, item_id, quantity,
     * batches[] — UNVERIFIED), notes.
     */
    public static function create($purchase_order_id, array $payload, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('POST', self::ENDPOINT, $organization_id, $payload, [
            'purchaseorder_id' => $purchase_order_id,
        ], $internal_organization_id);
    }

    /** PUT /books/v3/purchasereceives/{id} — UNVERIFIED (Books may not allow editing a receive). */
    public static function update($purchase_receive_id, array $payload, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('PUT', self::path($purchase_receive_id), $organization_id, $payload, [], $internal_organization_id);
    }

    /** DELETE /books/v3/purchasereceives/{id} */
    public static function delete($purchase_receive_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('DELETE', self::path($purchase_receive_id), $organization_id, null, [], $internal_organization_id);
    }

    private static function path($id): string
    {
        return self::ENDPOINT . '/' . rawurlencode((string) $id);
    }
}
