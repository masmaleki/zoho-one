<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Users;

use GuzzleHttp\Client;
use Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoTokenCheck;

class ZohoOrganizationController
{

    public static function getAll()
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return null;
        }

        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/organizations';

        $client = new Client();
        $postInput = [
            'page' => 1,
            'type' => 'AllUsers'
        ];
        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
            //'Content-Type' => 'application/json;charset=UTF-8'
        ];

        $response = $client->request('GET', $apiURL, ['form_params' => $postInput, 'headers' => $headers]);
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody(), true);
        return $responseBody;

    }


}
