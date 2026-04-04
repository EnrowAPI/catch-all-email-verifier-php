<?php

namespace CatchAllVerifier;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CatchAllVerifier
{
    private const BASE_URL = 'https://api.enrow.io';

    private static function request(string $apiKey, string $method, string $path, ?array $body = null): array
    {
        $client = new Client(['base_uri' => self::BASE_URL]);

        $options = [
            'headers' => [
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        try {
            $response = $client->request($method, $path, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $data = json_decode($e->getResponse()->getBody()->getContents(), true);
                $message = $data['message'] ?? 'API error ' . $e->getResponse()->getStatusCode();
                throw new \RuntimeException($message, $e->getResponse()->getStatusCode(), $e);
            }
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Verify a single email address on a catch-all domain.
     *
     * @param string $apiKey Your Enrow API key.
     * @param array $params {
     *     @type string $email   Email address to verify (required).
     *     @type string $custom  Custom tracking parameter.
     *     @type string $webhook Webhook URL for async notification.
     * }
     * @return array Verification result containing an id to poll with get().
     */
    public static function verify(string $apiKey, array $params): array
    {
        $body = ['email' => $params['email']];

        if (!empty($params['custom'])) {
            $body['custom'] = $params['custom'];
        }
        if (!empty($params['webhook'])) {
            $body['settings'] = ['webhook' => $params['webhook']];
        }

        return self::request($apiKey, 'POST', '/email/verify/single', $body);
    }

    /**
     * Get the result of a single email verification.
     *
     * @param string $apiKey Your Enrow API key.
     * @param string $id     The verification ID returned by verify().
     * @return array Verification result with qualification, status, etc.
     */
    public static function get(string $apiKey, string $id): array
    {
        return self::request($apiKey, 'GET', '/email/verify/single?id=' . urlencode($id));
    }

    /**
     * Verify multiple email addresses in a single batch.
     *
     * @param string $apiKey Your Enrow API key.
     * @param array $params {
     *     @type array  $emails  Array of email address strings.
     *     @type string $custom  Custom tracking parameter.
     *     @type string $webhook Webhook URL for async notification.
     * }
     * @return array Batch result containing batchId, total, status.
     */
    public static function verifyBulk(string $apiKey, array $params): array
    {
        $body = ['emails' => $params['emails']];

        if (!empty($params['custom'])) {
            $body['custom'] = $params['custom'];
        }
        if (!empty($params['webhook'])) {
            $body['settings'] = ['webhook' => $params['webhook']];
        }

        return self::request($apiKey, 'POST', '/email/verify/bulk', $body);
    }

    /**
     * Get the results of a bulk email verification.
     *
     * @param string $apiKey Your Enrow API key.
     * @param string $id     The batch ID returned by verifyBulk().
     * @return array Batch results with status, completed count, and results array.
     */
    public static function getBulk(string $apiKey, string $id): array
    {
        return self::request($apiKey, 'GET', '/email/verify/bulk?id=' . urlencode($id));
    }
}
