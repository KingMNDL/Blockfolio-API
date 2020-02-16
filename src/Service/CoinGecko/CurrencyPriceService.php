<?php

namespace App\Service\CoinGecko;

use GuzzleHttp\Exception\GuzzleException;

class CurrencyPriceService
{
    private const ALL_CURRENCIES = 'iota,bitcoin,ethereum';

    public const VS_CURRENCY = 'usd';

    public const VS_CURRENCY_ICON = '$';
    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * CurrencyPriceService constructor.
     * @param ApiClient $apiClient
     */
    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }


    /**
     * @param string $currencies
     * @return mixed
     * @throws GuzzleException
     */
    public function getCurrencyPrices($currencies = self::ALL_CURRENCIES)
    {
        $response = $this->apiClient->sendRequest('GET', $currencies, self::VS_CURRENCY);

        return json_decode($response->getBody()->getContents(), TRUE);
    }
}