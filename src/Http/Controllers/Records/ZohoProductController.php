<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;


use GuzzleHttp\Client;
use Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoTokenCheck;
use Illuminate\Support\Carbon;

class ZohoProductController
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

        $fields = $fields ? $fields : 'Product_Name,Lifecylce_Status,Owner,Created_By,Modified_By';

        $apiURL = $token->api_domain . '/crm/v3/Products?fields=' . $fields;

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

    public static function getById($zoho_product_id, $fields = null)
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
        $apiURL = $token->api_domain . '/crm/v3/Products/' . $zoho_product_id;
        // $apiURL = $token->api_domain . '/crm/v3/Products/search?criteria=(id:equals:' . $zoho_product_id . ')';
        if ($fields) {
            $apiURL .= '?fields=' . $fields;
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

    public static function getItemById($zoho_books_item_id, $organization_id = null)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/items/' . $zoho_books_item_id . '?organization_id=' . $organization_id;
        $client = new Client();
        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        try {
            $response = $client->request('GET', $apiURL, ['headers' => $headers]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $responseBody = [];
            $responseBody['code'] = $e->getCode();
            $responseBody['message'] = $e->getMessage();
        }

        return $responseBody;
    }

    public static function getAllItems($organization_id = null, $page = 1, $condition = '')
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return null;
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/items?page=' . $page . $condition;
        if ($organization_id) {
            $apiURL .= '&organization_id=' . $organization_id;
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
            $responseBody = [];
            $responseBody['code'] = $e->getCode();
            $responseBody['message'] = $e->getMessage();
        }


        return $responseBody;
    }

    public static function searchItemByName($product_name, $organization_id = null)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/items?page=1&per_page=25&sort_column=created_time&sort_order=A&name_contains=' . $product_name;
        if ($organization_id) {
            $apiURL .= '&organization_id=' . $organization_id;
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

    public static function updateProduct($data = [])
    {
        $zoho_product_id = $data['id'];

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
        $apiURL = $token->api_domain . '/crm/v3/Products/' . $zoho_product_id . '';
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        if (!isset($data['id'])) {
            $data['id'] = $zoho_product_id;
        }

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

    public static function create($data = [])
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
        $apiURL = $token->api_domain . '/crm/v3/Products';
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


    public static function updateItem($data = [])
    {
        $zoho_books_item_id = $data['id'];
        $organization_id = $data['organization_id'] ?? null;
        unset($data['organization_id']);
        unset($data['id']);
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/items/' . $zoho_books_item_id . '?organization_id=' . $organization_id;

        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        $body = $data;

        try {
            $response = $client->request('PUT', $apiURL, ['headers' => $headers, 'body' => json_encode($body)]);
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

    public static function createItem($data = [], $organization_id = null)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return null;
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/items';
        if ($organization_id) {
            $apiURL .= '?organization_id=' . $organization_id;
        }
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        $body = $data;

        try {
            $response = $client->request('POST', $apiURL, ['headers' => $headers, 'body' => json_encode($body)]);
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $responseBody = [];
            $responseBody['code'] = $e->getCode();
            $responseBody['message'] = $e->getMessage();
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
        $apiURL = $token->api_domain . '/crm/v3/Products/search?word=' . $phrase;//. '&fields=Product_Name,Part_Description';
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

    public static function getImage($zoho_product_id)
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
        $apiURL = $token->api_domain . '/crm/v3/Products/' . $zoho_product_id . '/photo';
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


    public static function deleteImage($zoho_product_id)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return null;
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/items/' . $zoho_books_item_id;
        if ($organization_id) {
            $apiURL .= '?organization_id=' . $organization_id;
        }
        
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        $response = $client->request('DELETE', $apiURL, ['headers' => $headers]);
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody(), true);
        return $responseBody;
    }

    public static function updateImage($zoho_product_id, $filePath, $fileMime, $fileUploadedName)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = $token->api_domain . '/crm/v3/Products/' . $zoho_product_id . '/photo';
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

        $response = $client->request('POST', $apiURL, $params);
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody(), true);
        return $responseBody;
    }


    public static function getZohoBooksItem($zoho_books_item_id, $organization_id = null)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/items/' . $zoho_books_item_id;
        if ($organization_id) {
            $apiURL .= '?organization_id=' . $organization_id;
        }
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        $response = $client->request('GET', $apiURL, ['headers' => $headers]);
        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody(), true);
        return $responseBody;
    }

    public static function getAllZohoBooksItems($organization_id, $page, $conditions)

    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/items/' . $zoho_books_item_id;
        if ($organization_id) {
            $apiURL .= '?organization_id=' . $organization_id;
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

    public static function getProductsCOQL($zoho_crm_product_id = null, $offset = 0, $conditions = null, $fields = null)
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

        $apiURL = $token->api_domain . '/crm/v3/coql';
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        $conditions = ($conditions) ? $conditions . ' and ' : '';
        $zoho_crm_product_id_conditions = $zoho_crm_product_id != null ? " (id = " . $zoho_crm_product_id . ")" : "(id != 0) ";

        $fields = $fields ? $fields : 'Qty_in_Demand , Owner ,RoHs,  Product_Active , Created_Time, id, Product_Name , Lifecylce_Status , SPQ1 , Record_Image , RoHs_Status ,  Catagory, MPN_ID , Taxable ,Product_Category, Manufacture_Name, Packaging, Datasheet_URL, Octopart_URL';

        $body = [
            'select_query' => "select " . $fields . " from Products where " . $conditions . $zoho_crm_product_id_conditions . "  limit " . $offset . ", 200",
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
}
