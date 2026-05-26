<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Auth;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Masmaleki\ZohoAllInOne\Models\ZohoToken;

class ZohoTokenCheck
{

    public static function getToken($organizationId = null)
    {
        $org = $organizationId ?: config('zoho-one.current_internal_organization_id');

        $zoho_token = ZohoToken::query()->where('organization_id', '=', $org ?? 1)->latest()->first();
        if ($zoho_token) {
            $expiry_time = Carbon::parse($zoho_token->expiry_time);
            if ($expiry_time->lt(Carbon::now())) {
                $zoho = new ZohoCustomTokenStore();
                $zoho_token = $zoho->refreshToken($zoho_token->id, $organizationId);
            }
            return $zoho_token;
        }
        return null;

    }

    public function applicationRegister()
    {
        $organizationId = request()->get('organization_id');
        return redirect(ZohoConfig::getAuthUrl($organizationId));
    }

    public static function saveTokens(Request $request, $organizationId = null)
    {
        $data = $request->all();

        $z_return_url = config('zoho-one.redirect_uri');
        $z_url = config('zoho-one.accounts_url');

        if ($organizationId == null) {
            $client_id = config('zoho-one.client_id');
            $secret_key = config('zoho-one.client_secret');
            $z_oauth_scope = config('zoho-one.oauth_scope');
        } else {
            $orgId = (int) $organizationId;
            $client_id = ZohoOrgCredentials::get($orgId, 'client_id');
            $secret_key = ZohoOrgCredentials::get($orgId, 'client_secret');
            $z_return_url = "$z_return_url/$organizationId";
            $z_oauth_scope = ZohoOrgCredentials::get($orgId, 'oauth_scope');
        }

        $postInput = [
            'grant_type' => 'authorization_code',
            'client_id' => $client_id,
            'client_secret' => $secret_key,
            'redirect_uri' => $z_return_url,
            'code' => $data['code'],
        ];
        $zoho = new ZohoCustomTokenStore();
        if ($request->has('refresh_token')) {
            $zoho->saveToken($postInput, $request->all(), $client_id, $secret_key, $z_return_url, $organizationId);
        } else {
            $resp = $zoho->getToken($data['accounts-server'], $data['location'], $postInput);
            $zoho->saveToken($postInput, $resp, $client_id, $secret_key, $z_return_url, $organizationId);
        }
        $frontendUrl = rtrim(config('app.url_ui', config('app.url')), '/');

        return redirect($frontendUrl . '/setting/token_settings?zoho_connected=1');
    }

}
