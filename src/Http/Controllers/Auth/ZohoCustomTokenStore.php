<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Auth;

use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Masmaleki\ZohoAllInOne\Models\ZohoToken;

class ZohoCustomTokenStore
{
    /**
     * @param $account_url
     * @param $location
     * @param $postInput
     * @return JsonResponse //A Token class instance representing the user token details.
     */
    public function getToken($account_url, $location, $postInput)
    {
        $apiURL = $account_url . '/oauth/v2/token';
        $client = new Client();
        $response = $client->request('POST', $apiURL, ['form_params' => $postInput]);
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody(), true);
        return $responseBody;
    }

    /**
     * @param $postInput
     * @param $response
     * @param $client_id
     * @param $secret_key
     * @param $z_return_url
     * @return ZohoToken
     */
    public function saveToken($postInput, $response, $client_id, $secret_key, $z_return_url, $organizationId)
    {
        $now = Carbon::now();

        $token = new ZohoToken();
        $token->organization_id = $organizationId;
        $token->access_token = $response['access_token'];
        $token->refresh_token = $response['refresh_token'] ?? '';
        $token->api_domain = $response['api_domain'];
        $token->token_type = $response['token_type'];
        $token->expiry_time = $now->add($response['expires_in'], 'seconds');
        $token->grant_token = $postInput['code'];
        $token->client_id = $postInput['client_id'];
        $token->client_secret = $postInput['client_secret'];
        $token->save();
        return $token;
    }

    /**
     * @param token A Token (com\zoho\api\authenticator\OAuthToken) class instance.
     * @throws SDKException if any problem occurs.
     */
    public function deleteToken($token)
    {
        // Add code to delete the token
    }

    /**
     * @return array  An array of Token (com\zoho\api\authenticator\OAuthToken) class instances
     */
    public function getTokens()
    {
        //Add code to retrieve all the stored tokens
    }

    public function deleteTokens()
    {
        //Add code to delete all the stored tokens.
    }

    public function refreshToken($token_id, $organizationId)
    {
        $token = ZohoToken::find($token_id);
        $postInput = [
            'refresh_token' => $token->refresh_token,
            'client_id' => $token->client_id,
            'client_secret' => $token->client_secret,
            'grant_type' => 'refresh_token',
        ];
        if ($organizationId == null) {
            $z_url = config('zoho-one.accounts_url');
            $z_return_url = config('zoho-one.redirect_uri');
            $z_api_url = config('zoho-one.api_base_url');
            $location = config('zoho-one.location');
        } else {
            $orgId = (int) $organizationId;
            $z_url = ZohoOrgCredentials::get($orgId, 'accounts_url') ?: config('zoho-one.accounts_url');
            $z_return_url = ZohoOrgCredentials::get($orgId, 'redirect_uri') ?: config('zoho-one.redirect_uri');
            $z_api_url = ZohoOrgCredentials::get($orgId, 'api_base_url') ?: config('zoho-one.api_base_url');
            $location = ZohoOrgCredentials::get($orgId, 'location') ?: config('zoho-one.location');
        }


        $refreshed_token_resp = self::getToken($z_url, $location, $postInput);

        //check the error response
        if (array_key_exists('error', $refreshed_token_resp ?? [])) {
            return null;
        }
        $token->organization_id = $organizationId ?? $token->organization_id;
        $token->access_token = $refreshed_token_resp['access_token'];
        $now = Carbon::now();
        $token->expiry_time = $now->add($refreshed_token_resp['expires_in'], 'seconds');
        $token->save();
        return $token;
    }

    /**
     * @param $id
     * @param $token
     * @return A Token class instance representing the user token details.
     */
    public function getTokenById($id, $token)
    {
        // Add code to get the token using unique id
        return null;
    }
}
