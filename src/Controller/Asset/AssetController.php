<?php

namespace App\Controller\Asset;

use App\Entity\Asset;
use App\Service\Asset\AssetManager;
use App\Service\CoinGecko\CurrencyPriceService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use GuzzleHttp\Exception\GuzzleException;

class AssetController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AssetManager
     */
    private $assetManager;

    /**
     * AssetController constructor.
     * @param EntityManagerInterface $entityManager
     * @param AssetManager $assetManager
     */
    public function __construct(EntityManagerInterface $entityManager, AssetManager $assetManager)
    {
        $this->entityManager = $entityManager;
        $this->assetManager = $assetManager;
    }

    /**
     * @SWG\Get(
     *      summary="Get all assets",
     *      description="All assets belonging to authenticated user",
     *      produces={"application/json"},
     *      tags={"Assets"},
     *      @SWG\Response(
     *          response="200",
     *          description="success",
     *          @SWG\Schema(
     *              @SWG\Property(property="items", type="array", @SWG\Items(
     *                  ref=@Model(type=App\Entity\Asset::class)
     *              ))
     *          )
     *      ),
     *      security={{"Bearer":{}}}
     * )
     *
     *
     * @return View
     */
    public function getAssets(): View
    {
        $assets = $this->assetManager->getAllUserAssets();

        return $this->view(['items' => $assets]);
    }

    /**
     * @SWG\Get(
     *      summary="Get single asset",
     *      description="Get single asset by it's ID",
     *      produces={"application/json"},
     *      tags={"Assets"},
     *      @SWG\Parameter(
     *          description="Asset ID",
     *          in="path",
     *          allowEmptyValue=false,
     *          required=true,
     *          name="id",
     *          type="string",
     *          format="string"
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="success",
     *          @SWG\Schema(
     *              @SWG\Property(property="items", type="array", @SWG\Items(
     *                  ref=@Model(type=App\Entity\Asset::class)
     *              ))
     *          )
     *      ),
     *      @SWG\Response(
     *          response=403,
     *          description="Access denied",
     *          @SWG\Schema(ref="#/definitions/AccessDeniedError")
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Not found",
     *          @SWG\Schema(ref="#/definitions/NotFoundError")
     *      ),
     *      security={{"Bearer":{}}}
     * )
     *
     * @param Asset $asset
     *
     * @return View
     */
    public function getAsset(Asset $asset): View
    {
        $this->throwExceptionIfAssetDoesntBelong($asset);

        return $this->view($asset, Response::HTTP_CREATED);
    }

    /**
     * @param Asset $asset
     */
    private function throwExceptionIfAssetDoesntBelong(Asset $asset)
    {
        if (!$this->assetManager->assetBelongsToUser($asset)) {
            throw new AccessDeniedHttpException('Access denied. This asset does not belong to current User.');
        }
    }

    /**
     * @SWG\Post(
     *     summary="Create new asset",
     *     description="Creates and returns new asset",
     *     produces={"application/json"},
     *     tags={"Assets"},
     *     @SWG\Parameter(
     *       name="body",
     *       in="body",
     *       description="Asset object",
     *       required=true,
     *       @Model(type=Asset::class, groups={"Create"})
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="successful operation",
     *         @Model(type=Asset::class, groups={"Get"})
     *     ),
     *      @SWG\Response(
     *          response="422",
     *          description="Invalid request",
     *          @SWG\Schema(ref="#/definitions/ValidationError")
     *      ),
     *     security={{"Bearer":{}}}
     * )
     *
     * @ParamConverter("asset", converter="json_converter_validator")
     *
     * @param Asset $asset
     *
     * @return View
     */
    public function createAsset(Asset $asset): View
    {
        $this->assetManager->appendUserToAsset($asset);

        $this->entityManager->flush();

        return $this->view($asset, Response::HTTP_CREATED);
    }

    /**
     * @SWG\Put(
     *     tags={"Assets"},
     *     summary="Update asset",
     *     description="Update single asset entity",
     *      @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          description="Asset ID",
     *          required=true,
     *          type="integer"
     *      ),
     *     @SWG\Parameter(
     *       name="body",
     *       in="body",
     *       description="Asset object",
     *       required=true,
     *       @Model(type=Asset::class, groups={"Update"})
     *     ),
     *      @SWG\Response(
     *          response="204",
     *          description="Updated",
     *      ),
     *      @SWG\Response(
     *          response=403,
     *          description="Access denied",
     *          @SWG\Schema(ref="#/definitions/AccessDeniedError")
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Not found",
     *          @SWG\Schema(ref="#/definitions/NotFoundError")
     *      ),
     *      @SWG\Response(
     *          response="422",
     *          description="Invalid request",
     *          @SWG\Schema(ref="#/definitions/ValidationError")
     *      ),
     *     security={{"Bearer":{}}}
     * )
     *
     * @ParamConverter("asset", converter="json_converter_validator")
     *
     * @param Asset $current
     * @param Asset $asset
     *
     * @return Response
     */
    public function updateAsset(Asset $current, Asset $asset): Response
    {
        $this->throwExceptionIfAssetDoesntBelong($current);

        $this->assetManager->update($current, $asset);

        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @SWG\Delete(
     *     summary="Delete asset",
     *     description="Deletes asset entity",
     *     produces={"application/json"},
     *     tags={"Assets"},
     *     @SWG\Parameter(
     *         description="Asset ID to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="string",
     *         format="string"
     *     ),
     *     @SWG\Response(
     *         response=204,
     *         description="Removed successfully",
     *     ),
     *      @SWG\Response(
     *          response=403,
     *          description="Access denied",
     *          @SWG\Schema(ref="#/definitions/AccessDeniedError")
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Not found",
     *          @SWG\Schema(ref="#/definitions/NotFoundError")
     *      ),
     *     security={{"Bearer":{}}}
     * )
     *
     * @param Asset $asset
     *
     * @return Response
     */
    public function deleteAsset(Asset $asset): Response
    {
        $this->throwExceptionIfAssetDoesntBelong($asset);

        $this->assetManager->delete($asset);

        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @SWG\Get(
     *      summary="Get value of all assets",
     *      description="Value of all assets belonging to authenticated user",
     *      produces={"application/json"},
     *      tags={"Value"},
     *      @SWG\Response(
     *          response="200",
     *          description="success",
     *          @SWG\Schema(
     *              @SWG\Property(property="items", type="array", @SWG\Items(
     *                  ref=@Model(type=App\Entity\Asset::class)
     *              ))
     *          )
     *      ),
     *      security={{"Bearer":{}}}
     * )
     *
     * @return View
     * @throws GuzzleException
     *
     */
    public function getAssetsValue(): View
    {
        $value = $this->assetManager->getAllUserAssetsValue();

        return $this->view($value . ' '.CurrencyPriceService::VS_CURRENCY_ICON);
    }

    /**
     * @SWG\Get(
     *      summary="Get single asset value",
     *      description="Get value of single asset by it's ID",
     *      produces={"application/json"},
     *      tags={"Value"},
     *      @SWG\Parameter(
     *          description="Asset ID",
     *          in="path",
     *          allowEmptyValue=false,
     *          required=true,
     *          name="id",
     *          type="string",
     *          format="string"
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="success",
     *          @SWG\Schema(
     *              @SWG\Property(property="items", type="array", @SWG\Items(
     *                  ref=@Model(type=App\Entity\Asset::class)
     *              ))
     *          )
     *      ),
     *      @SWG\Response(
     *          response=403,
     *          description="Access denied",
     *          @SWG\Schema(ref="#/definitions/AccessDeniedError")
     *      ),
     *      @SWG\Response(
     *          response=404,
     *          description="Not found",
     *          @SWG\Schema(ref="#/definitions/NotFoundError")
     *      ),
     *      security={{"Bearer":{}}}
     * )
     *
     * @param Asset $asset
     *
     * @return View
     * @throws GuzzleException
     *
     */
    public function getAssetValue(Asset $asset): View
    {
        $this->throwExceptionIfAssetDoesntBelong($asset);

        $value = $this->assetManager->getAssetValue($asset);

        return $this->view($value . ' '.CurrencyPriceService::VS_CURRENCY_ICON);
    }
}
