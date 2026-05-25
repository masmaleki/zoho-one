<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Auth;


class ZohoConfig
{

    public static function getAuthUrl($organizationId = null)
    {
        $z_return_url = config('zoho-one.redirect_uri');
        $z_url = config('zoho-one.accounts_url');

        if ($organizationId == null) {
            $client_id = config('zoho-one.client_id');
            $z_oauth_scope = config('zoho-one.oauth_scope');
        } else {
            $orgId = (int) $organizationId;
            $client_id = ZohoOrgCredentials::get($orgId, 'client_id');
            $z_oauth_scope = ZohoOrgCredentials::get($orgId, 'oauth_scope');
            $z_url = ZohoOrgCredentials::get($orgId, 'accounts_url') ?: $z_url;
            $z_return_url = "$z_return_url/$organizationId";
        }

        return "$z_url/oauth/v2/auth?scope=$z_oauth_scope&client_id=$client_id&response_type=code&access_type=offline&redirect_uri=$z_return_url";
    }

}
