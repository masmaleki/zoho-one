<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;

/**
 * Zoho Books inventory adjustments (quantity or value corrections).
 *
 * GET list/detail are already read on live Books by the backend ingestion. create/delete follow
 * the Books reference and are UNVERIFIED against a live organization.
 */
class ZohoInventoryAdjustmentController
{
    private const ENDPOINT = '/inventoryadjustments';

    /** GET /books/v3/inventoryadjustments?page=&... */
    public static function getAll($organization_id, $page = 1, $condition = '', $internal_organization_id = null)
    {
        $query = array_merge(ZohoBooksInventoryRequest::conditionToQuery($condition), ['page' => $page]);

        return ZohoBooksInventoryRequest::send('GET', self::ENDPOINT, $organization_id, null, $query, $internal_organization_id);
    }

    /** GET /books/v3/inventoryadjustments/{id} */
    public static function get($inventory_adjustment_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('GET', self::path($inventory_adjustment_id), $organization_id, null, [], $internal_organization_id);
    }

    /**
     * POST /books/v3/inventoryadjustments
     * Payload: date, reason, description, adjustment_type (quantity|value), reference_number,
     * line_items[] (item_id, quantity_adjusted, warehouse_id, batches[] — UNVERIFIED).
     */
    public static function create(array $payload, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('POST', self::ENDPOINT, $organization_id, $payload, [], $internal_organization_id);
    }

    /** DELETE /books/v3/inventoryadjustments/{id} */
    public static function delete($inventory_adjustment_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('DELETE', self::path($inventory_adjustment_id), $organization_id, null, [], $internal_organization_id);
    }

    private static function path($id): string
    {
        return self::ENDPOINT . '/' . rawurlencode((string) $id);
    }
}
