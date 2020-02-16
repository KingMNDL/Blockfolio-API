<?php


namespace App\Service\Asset;

use App\Entity\Asset;
use App\Repository\AssetRepository;
use App\Service\CoinGecko\CurrencyPriceService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AssetManager
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CurrencyPriceService
     */
    private $currencyPriceService;

    /**
     * AssetManager constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param AssetRepository $assetRepository
     * @param EntityManagerInterface $entityManager
     * @param CurrencyPriceService $currencyPriceService
     */
    public function __construct(TokenStorageInterface $tokenStorage, AssetRepository $assetRepository, EntityManagerInterface $entityManager, CurrencyPriceService $currencyPriceService)
    {
        $this->tokenStorage = $tokenStorage;
        $this->assetRepository = $assetRepository;
        $this->entityManager = $entityManager;
        $this->currencyPriceService = $currencyPriceService;
    }


    /**
     * @param Asset $asset
     * @return bool
     */
    public function assetBelongsToUser(Asset $asset): bool
    {
        return $asset->getUser()->getUsername() === $this->getUser()->getUsername();
    }

    /**
     * @return object|string
     */
    private function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * @param Asset $asset
     */
    public function appendUserToAsset(Asset $asset): void
    {
        $asset->setUser($this->getUser());

        $this->entityManager->persist($asset);
    }

    /**
     * @param Asset $current
     * @param Asset $updated
     */
    public function update(Asset $current, Asset $updated): void
    {
        $current
            ->setLabel($updated->getLabel())
            ->setValue($updated->getValue())
            ->setCurrency($updated->getCurrency());

        $this->entityManager->persist($current);
    }

    /**
     * @param Asset $asset
     */
    public function delete(Asset $asset): void
    {
        $this->entityManager->remove($asset);
    }

    /**
     * @throws GuzzleException
     */
    public function getAllUserAssetsValue()
    {
        $value = 0;

        $assets = $this->getAllUserAssets();

        if (!empty($assets)) {
            $prices = $this->currencyPriceService->getCurrencyPrices();

            foreach ($assets as $asset) {
                $value += $asset->getValue() * $prices[$this->resolveCurrencyName($asset->getCurrency())][CurrencyPriceService::VS_CURRENCY];
            }
        }

        return $value;
    }

    /**
     * @return Asset[]|array
     */
    public function getAllUserAssets()
    {
        return $this->assetRepository->findByUser($this->getUser());
    }

    /**
     * @param $currency
     * @return mixed
     */
    private function resolveCurrencyName($currency)
    {
        $currencies = [
            'BTC' => 'bitcoin',
            'ETH' => 'ethereum',
            'IOTA' => 'iota'
        ];

        return $currencies[strtoupper($currency)];
    }

    /**
     * @param Asset $asset
     * @return float|int
     * @throws GuzzleException
     */
    public function getAssetValue(Asset $asset)
    {
        $price = $this->currencyPriceService->getCurrencyPrices($this->resolveCurrencyName($asset->getCurrency()));

        return $asset->getValue() * $price[$this->resolveCurrencyName($asset->getCurrency())][CurrencyPriceService::VS_CURRENCY];
    }


}
