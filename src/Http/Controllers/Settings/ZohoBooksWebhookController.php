<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Settings;

use GuzzleHttp\Client;
use Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoTokenCheck;

class ZohoBooksWebhookController
{
    private const ENDPOINT = '/settings/webhooks';

    public static function getAll($organization_id, $query = [])
    {
        return self::request('GET', self::ENDPOINT, $organization_id, [], $query);
    }

    public static function create($data, $organization_id)
    {
        return self::request('POST', self::ENDPOINT, $organization_id, $data);
    }

    public static function get($webhook_id, $organization_id)
    {
        return self::request('GET', self::ENDPOINT . '/' . $webhook_id, $organization_id);
    }

    public static function update($webhook_id, $data, $organization_id)
    {
        return self::request('PUT', self::ENDPOINT . '/' . $webhook_id, $organization_id, $data);
    }

    public static function delete($webhook_id, $organization_id)
    {
        return self::request('DELETE', self::ENDPOINT . '/' . $webhook_id, $organization_id);
    }

    private static function request($method, $endpoint, $organization_id, $data = [], $query = [])
    {
        if (!$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }

        $token = ZohoTokenCheck::getToken($organization_id);
        if (!$token) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }

        $query['organization_id'] = $organization_id;
        $apiURL = config('zoho-one.books_api_base_url') . '/books/v3' . $endpoint;

        $options = [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
            ],
            'query' => $query,
        ];

        if (in_array($method, ['POST', 'PUT'], true)) {
            $options['json'] = $data;
        }

        try {
            $client = new Client();
            $response = $client->request($method, $apiURL, $options);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }
}
