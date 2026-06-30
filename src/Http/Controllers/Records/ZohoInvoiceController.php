<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;


use GuzzleHttp\Client;
use Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoTokenCheck;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ZohoInvoiceController
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
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/invoices?organization_id=' . $organization_id . '&page=' . $page . $condition;

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

    public static function getById($zoho_invoice_id, $organization_id = null)
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [

                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/invoices/' . $zoho_invoice_id . '?organization_id=' . $organization_id;
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
    public static function getRecurringInvoices($organization_id, $page = 1, $condition = '')
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/recurringinvoices?organization_id=' . $organization_id . '&page=' . $page . $condition;

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
    public static function getRecurringInvoiceById($zoho_invoice_id, $organization_id = null)
        {

        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [

                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/recurringinvoices/' . $zoho_invoice_id;
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

    public static function getCRMInvoiceById($zoho_invoice_id, $fields = null)
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return null;
        }
        $apiURL = $token->api_domain . '/crm/v3/Invoices/' . $zoho_invoice_id;
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

    public static function updateCRMInvoice($data = [])
    {

        $zoho_invoice_id = $data['id'];

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
        $apiURL = $token->api_domain . '/crm/v3/Invoices/' . $zoho_invoice_id;
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

    public static function getByVendorId($zoho_vendor_id)
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
        $apiURL = $token->api_domain . '/crm/v3/Invoices/search?criteria=(Vendor_ID:equals:' . $zoho_vendor_id . ')';
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

    public static function getByCustomerId($zoho_customer_id, $organization_id)
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/invoices?&customer_id=' . $zoho_customer_id . '&organization_id=' . $organization_id;

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
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/invoices?customer_id=' . $zoho_customer_id . '';

        if ($searchParameter) {
            $apiURL .= '&invoice_number_contains=' . $searchParameter;
        }
        if ($organization_id) {
            $apiURL .= '&organization_id=' . $organization_id;
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
    public static function searchInvoicesByRelation($relation, $relation_id, $searchParameter, $organization_id)
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return null;
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/invoices?'.$relation.'_id=' . $relation_id . '';

        if ($searchParameter) {
            $apiURL .= '&invoice_number_contains=' . $searchParameter;
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

    public static function searchRecurringInvoiceByRelation($relation, $relation_id, $searchParameter, $organization_id)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return null;
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/recurringinvoices?'.$relation.'_id=' . $relation_id . '';

        if ($searchParameter) {
            $apiURL .= '&invoice_number_contains=' . $searchParameter;
        }
        if ($organization_id) {
            $apiURL .= '&organization_id=' . $organization_id;
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
    public static function getPDF($invoice_id, $organization_id)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/invoices/' . $invoice_id . '?accept=pdf';
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        try {
            $response = $client->request('GET', $apiURL, ['headers' => $headers, 'stream' => false]);
            $responseBody = $response->getBody();

            $streamResponse = new StreamedResponse(function () use ($responseBody) {
                while (!$responseBody->eof()) {
                    echo $responseBody->read(1024);
                }
            });

            $streamResponse->headers->set('Content-Type', 'application/pdf');
            $streamResponse->headers->set('Cache-Control', 'no-cache');

            return $streamResponse;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getHTML($invoice_id, $organization_id)
    {
        $token = ZohoTokenCheck::getToken();
        if (!$token || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or organization ID.',
            ];
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/invoices/' . $invoice_id . '?accept=html';
        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        try {
            $response = $client->request('GET', $apiURL, ['headers' => $headers, 'stream' => false]);
            $responseBody = $response->getBody();
        } catch (\Exception $e) {
            $responseBody = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
        return $responseBody;
    }

    public static function createBooksInvoice($data = [], $organization_id = null)
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return null;
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/invoices';
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

    public static function createBooksRecurringInvoice($data = [], $organization_id = null)
    {

        $token = ZohoTokenCheck::getToken();
        if (!$token) {
            return null;
        }
        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/recurringinvoices';
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

    public static function getComments($data = [])
    {
        $invoice_id = $data['id'] ?? null;
        $organization_id = $data['organization_id'] ?? null;

        $token = ZohoTokenCheck::getToken();
        if (!$token || !$invoice_id || !$organization_id) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or required parameters.',
            ];
        }

        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . '/books/v3/invoices/' . $invoice_id . '/comments?organization_id=' . $organization_id;

        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        try {
            $response = $client->request('GET', $apiURL, [
                'headers' => $headers,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            return [
                'code' => $statusCode,
                'response' => $responseBody,
            ];
        } catch (\Exception $e) {
            return [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

}
