<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;

/**
 * Zoho Books / Inventory shipment orders (a shipment of one or more packages of ONE sales order).
 *
 * Documented in the Zoho Inventory API reference and served under /books/v3 for organizations
 * with the Inventory add-on. Not yet read against a live Books organization — see the backend's
 * docs/architecture/FULFILMENT_BOOKS_API_VERIFICATION.md.
 */
class ZohoShipmentOrderController
{
    private const ENDPOINT = '/shipmentorders';

    /** GET /books/v3/shipmentorders?page=&... ($condition: legacy "&k=v" string or an array) */
    public static function getAll($organization_id, $page = 1, $condition = '', $internal_organization_id = null)
    {
        $query = array_merge(ZohoBooksInventoryRequest::conditionToQuery($condition), ['page' => $page]);

        return ZohoBooksInventoryRequest::send('GET', self::ENDPOINT, $organization_id, null, $query, $internal_organization_id);
    }

    /** GET /books/v3/shipmentorders/{id} */
    public static function get($shipment_order_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('GET', self::path($shipment_order_id), $organization_id, null, [], $internal_organization_id);
    }

    /**
     * POST /books/v3/shipmentorders?package_ids={a,b}&salesorder_id={so}
     * Payload: shipment_number, date, delivery_method, tracking_number, shipping_charge, notes.
     * Several packages of the same sales order in one shipment — UNVERIFIED on live Books.
     */
    public static function create(array $package_ids, $sales_order_id, array $payload, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('POST', self::ENDPOINT, $organization_id, $payload, [
            'package_ids' => implode(',', array_map('strval', $package_ids)),
            'salesorder_id' => $sales_order_id,
        ], $internal_organization_id);
    }

    /** PUT /books/v3/shipmentorders/{id} */
    public static function update($shipment_order_id, array $payload, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('PUT', self::path($shipment_order_id), $organization_id, $payload, [], $internal_organization_id);
    }

    /** DELETE /books/v3/shipmentorders/{id} */
    public static function delete($shipment_order_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('DELETE', self::path($shipment_order_id), $organization_id, null, [], $internal_organization_id);
    }

    /**
     * POST /books/v3/shipmentorders/{id}/status/delivered
     * (A matching "undelivered" status endpoint is NOT known to exist — UNVERIFIED, not implemented.)
     */
    public static function markDelivered($shipment_order_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('POST', self::path($shipment_order_id) . '/status/delivered', $organization_id, null, [], $internal_organization_id);
    }

    private static function path($id): string
    {
        return self::ENDPOINT . '/' . rawurlencode((string) $id);
    }
}
