<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;


use GuzzleHttp\Client;
use Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoTokenCheck;

class ZohoVendorController
{

    public static function getAll($page_token = null, $fields = null)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        if ($fields == null) {
            $fields = 'Email,Business_Vendor,Vendor_Source,Vendor_Type,LinkedIn,Website,Mobile,Phone,Vendor_Name,Country,First_Name,Last_Name,ZohoBooksID,Octopart_ID,Broker_Type,Vendor_Site,Vendor_Owner,Zip_Code,Street,State,Description,City,VAT_No,Approve_status,Currency,Incoterms,Line_Card,Parent_Vendor,Payment_Terms,Vendor_Number';
        }
        $apiURL = $token->api_domain . '/crm/v3/Vendors?fields=' . $fields;
        if ($page_token) {
            $apiURL .= '&page_token=' . $page_token;
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
                'data' => [
                    0 => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ]
                ],
            ];
        }
        return $responseBody;
    }

    public static function getById($zoho_vendor_id)
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        $apiURL = $token->api_domain . '/crm/v3/Vendors/search?criteria=(id:equals:' . $zoho_vendor_id . ')';
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
                'data' => [
                    0 => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ]
                ],
            ];
        }
        return $responseBody;
    }

    public static function create($data)
    {
        if (!$data) {
            return null;
        }
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        $apiURL = $token->api_domain . '/crm/v3/Vendors';
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        $body = [
            'data' => [
                0 => $data
            ]
        ];
        try {
            $response = $client->request('POST', $apiURL, ['headers' => $headers, 'body' => json_encode($body)]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $responseBody = [
                'data' => [
                    0 => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ]
                ],
            ];
        }
        return $responseBody;
    }

    public static function getZohoCrmVendor($zoho_crm_vendor_id)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        $apiURL = $token->api_domain . '/crm/v3/Vendors/' . $zoho_crm_vendor_id . '';
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
                'data' => [
                    0 => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ]
                ],
            ];
        }
        return $responseBody;
    }

    public static function getAllFromBooks($organization_id, $page = 1, $condition = '')
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/vendors?organization_id=' . $organization_id . '&page=' . $page . $condition;

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

    public static function search($phrase)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        $apiURL = $token->api_domain . '/crm/v3/Vendors/search?word=' . $phrase;
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
                'data' => [
                    0 => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ]
                ],
            ];
        }
        return $responseBody;
    }

    public static function getZohoBooksVendorByCrmVendorId($zoho_crm_vendor_id, $organization_id)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        // TODO: Must be checked
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/vendors?zcrm_vendor_id=' . $zoho_crm_vendor_id . '&organization_id=' . $organization_id;

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

    public static function getZohoBooksVendorById($zoho_books_vendor_id, $organization_id = null)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/contacts/' . $zoho_books_vendor_id . '?organization_id=' . $organization_id;

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

    public static function getImage($zoho_id)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        $apiURL = $token->api_domain . '/crm/v3/Vendors/' . $zoho_id . '/photo';
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        try {
            $response = $client->request('GET', $apiURL, ['headers' => $headers, ['stream' => true]]);
            $responseBody = $response->getBody()->getContents();
            $base64 = base64_encode($responseBody);

            if (!$base64) return null;

            $mime = "image/jpeg";
            $img = ('data:' . $mime . ';base64,' . $base64);

            return $img;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function updateAvatar($zoho_contact_id, $filePath, $fileMime, $fileUploadedName)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        $apiURL = $token->api_domain . '/crm/v3/Vendors/' . $zoho_contact_id . '/photo';
        $client = new Client();

        $params = [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
            ],
            'multipart' => [
                [
                    'name' => 'file',
                    'filename' => $fileUploadedName,
                    'Mime-Type' => $fileMime,
                    'contents' => fopen($filePath, 'r'),
                ],
            ],
        ];

        try {
            $response = $client->request('POST', $apiURL, $params);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $responseBody = [
                'data' => [
                    0 => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ]
                ],
            ];
        }
        return $responseBody;
    }

    public static function deleteAvatar($zoho_contact_id)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        $apiURL = $token->api_domain . '/crm/v3/Vendors/' . $zoho_contact_id . '/photo';
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        try {
            $response = $client->request('DELETE', $apiURL, ['headers' => $headers]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $responseBody = [
                'data' => [
                    0 => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ]
                ],
            ];
        }
        return $responseBody;
    }

    public static function update($zoho_crm_id, $data)
    {
        if (!$data) {
            return null;
        }
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        $apiURL = $token->api_domain . '/crm/v3/Vendors/' . $zoho_crm_id;
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        $body = [
            'data' => [
                0 => $data
            ]
        ];
        try {
            $response = $client->request('PUT', $apiURL, ['headers' => $headers, 'body' => json_encode($body)]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $responseBody = [
                'data' => [
                    0 => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ]
                ],
            ];
        }
        return $responseBody;
    }

    public static function getRelatedManufactureLineCardV6($zoho_id)
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        $apiURL = $token->api_domain . '/crm/v6/Vendors/' . $zoho_id . '/Manufacture16?fields=Line_Card';

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
                'data' => [
                    0 => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ]
                ],
            ];
        }
        return $responseBody;

    }

    public static function getRelatedManufactureStrongLineV6($zoho_id)
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return [
                'data' => [
                    0 => [
                        'code' => 498,
                        'message' => 'Invalid or missing token.',
                        'status' => 'error',
                    ]
                ],
            ];
        }
        $apiURL = $token->api_domain . '/crm/v6/Vendors/' . $zoho_id . '/Manufacture5?fields=Strong_Lines';

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
                'data' => [
                    0 => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'status' => 'error',
                    ]
                ],
            ];
        }
        return $responseBody;

    }


}
