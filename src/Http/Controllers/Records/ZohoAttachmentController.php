<?php

namespace Masmaleki\ZohoAllInOne\Http\Controllers\Records;

use GuzzleHttp\Client;
use Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoTokenCheck;

class ZohoAttachmentController
{
    public static function getAll($zoho_module_name, $zoho_record_id)
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
        $apiURL = $token->api_domain . '/crm/v3/' . $zoho_module_name . '/' . $zoho_record_id . '/Attachments?fields=id,File_Name,$file_id';
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

    public static function upload($zoho_module_name, $zoho_record_id, $file_content, $file_mime, $file_upload_name)
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
        $apiURL = $token->api_domain . '/crm/v3/' . $zoho_module_name . '/' . $zoho_record_id . '/Attachments';
        $client = new Client();

        $params = [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
            ],
            'multipart' => [
                [
                    'name' => 'file',
                    'filename' => $file_upload_name,
                    'Mime-Type' => $file_mime,
                    'contents' => $file_content,
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

    public static function delete($zoho_module_name, $zoho_record_id, $zoho_attachment_id)
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
        $apiURL = $token->api_domain . '/crm/v3/' . $zoho_module_name . '/' . $zoho_record_id . '/Attachments/' . $zoho_attachment_id;
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

    public static function getAllFromZohoBooks($data = [])
    {
        $module_id = $data['id'] ?? null;
        $organization_id = $data['organization_id'] ?? null;
        $module = $data['module'] ?? null;

        $token = ZohoTokenCheck::getToken();
        if (!$token || !$module_id || !$organization_id || !$module) {
            return [
                'code' => 498,
                'message' => 'Invalid/missing token or required parameters.',
            ];
        }

        $apiURL = \Masmaleki\ZohoAllInOne\Http\Controllers\Auth\ZohoOrgCredentials::booksApiBaseUrl() . "/books/v3/$module/$module_id/attachment?organization_id=$organization_id";

        $client = new Client();

        $headers = [
            'Authorization' => 'Zoho-oauthtoken ' . $token->access_token,
        ];

        try {
            $response = $client->request('GET', $apiURL, [
                'headers' => $headers,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode == 200) {
                $responseBody = json_decode($response->getBody(), true);

                // Assume 'files' contains file metadata with download URLs
                $files = [];
                if (isset($responseBody['files']) && is_array($responseBody['files'])) {
                    foreach ($responseBody['files'] as $fileData) {
                        // Get the file's download URL
                        $fileUrl = $fileData['download_url'];  // Update if the actual key is different

                        // Request the file content
                        $fileResponse = $client->request('GET', $fileUrl, [
                            'headers' => $headers,
                        ]);

                        // Extract filename from Content-Disposition header
                        $contentDisposition = $fileResponse->getHeader('Content-Disposition');
                        $fileName = self::extractFileName($contentDisposition[0] ?? 'unknown');
                        $fileContent = $fileResponse->getBody()->getContents();

                        $files[] = [
                            'file_name' => $fileName,
                            'file_content' => $fileContent,
                        ];
                    }

                    return [
                        'code' => $statusCode,
                        'files' => $files,
                    ];
                }

                return [
                    'code' => $statusCode,
                    'response' => $responseBody,
                ];
            }

            return [
                'code' => $statusCode,
                'message' => 'Unexpected response.',
            ];

        } catch (\Exception $e) {
            return [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Extracts the filename from the Content-Disposition header.
     *
     * @param string $contentDisposition
     * @return string|null
     */
    private static function extractFileName($contentDisposition)
    {
        if (preg_match('/filename="(.+?)"/', $contentDisposition, $matches)) {
            return $matches[1];
        }
        return 'unknown';
    }



}
