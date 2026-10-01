<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;


use GuzzleHttp\Client;
use Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoTokenCheck;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ZohoPackageController
{
    public static function getAll($organization_id, $page = 1, $condition = '')
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/packages?organization_id=' . $organization_id . '&page=' . $page . $condition;

        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        try {
            $response = $client->request('GET', $apiURL, ['headers' => $headers]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $responseBody = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
        return $responseBody;
    }

    public static function searchByCustomerId($zoho_customer_id, $searchParameter, $organization_id)
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/packages?customer_id=' . $zoho_customer_id . '&organization_id=' . $organization_id;

        if ($searchParameter) {
            $apiURL .= '&package_number_contains=' . $searchParameter;
        }

        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        try {
            $response = $client->request('GET', $apiURL, ['headers' => $headers]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $responseBody = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
        return $responseBody;
    }

    // --- Fulfilment (single package read + write). Go through ZohoBooksInventoryRequest so a
    // failure carries Zoho's own code/message, the HTTP status and Retry-After. ---

    /**
     * GET /books/v3/packages?page=&... — same list as getAll(), through ZohoBooksInventoryRequest
     * (array or legacy "&k=v" condition, explicit internal organization, structured failures).
     */
    public static function list($organization_id, $page = 1, $condition = '', $internal_organization_id = null)
    {
        $query = array_merge(ZohoBooksInventoryRequest::conditionToQuery($condition), ['page' => $page]);

        return ZohoBooksInventoryRequest::send('GET', '/packages', $organization_id, null, $query, $internal_organization_id);
    }

    /** GET /books/v3/packages/{package_id} */
    public static function get($package_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('GET', '/packages/' . rawurlencode((string) $package_id), $organization_id, null, [], $internal_organization_id);
    }

    /**
     * POST /books/v3/packages?salesorder_id={sales_order_id}
     * Payload: package_number (optional when auto-numbering), date, line_items[] (so_line_item_id,
     * quantity, batches[] for batch-tracked items — batch shape UNVERIFIED), notes.
     */
    public static function create($sales_order_id, array $payload, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('POST', '/packages', $organization_id, $payload, [
            'salesorder_id' => $sales_order_id,
        ], $internal_organization_id);
    }

    /** PUT /books/v3/packages/{package_id} */
    public static function update($package_id, array $payload, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('PUT', '/packages/' . rawurlencode((string) $package_id), $organization_id, $payload, [], $internal_organization_id);
    }

    /** DELETE /books/v3/packages/{package_id} (Books is expected to refuse a shipped package — UNVERIFIED). */
    public static function delete($package_id, $organization_id, $internal_organization_id = null)
    {
        return ZohoBooksInventoryRequest::send('DELETE', '/packages/' . rawurlencode((string) $package_id), $organization_id, null, [], $internal_organization_id);
    }

}
