<?php

namespace App\Service\CoinGecko;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * ApiClient constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }


    /**
     * @param string $method
     * @param string $currencies
     * @param string $vsCurrency
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws \Exception
     */
    public function sendRequest(string $method, string $currencies, string $vsCurrency): ResponseInterface
    {
        try {
            return $this->client->request(
                $method,
                $this->getEndpoint($currencies, $vsCurrency),
                $this->getRequestOptions());

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string $currencies
     * @param string $vsCurrency
     * @return string
     */
    private function getEndpoint(string $currencies, string $vsCurrency): string
    {
        return '/api/v3/simple/price?ids=' . $currencies . '&vs_currencies=' . $vsCurrency . '';
    }

    /**
     * @return array
     */
    private function getRequestOptions(): array
    {
        $options = [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json'
            ],
        ];

        return array_filter($options);
    }
}