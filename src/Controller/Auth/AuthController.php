<?php

namespace App\Controller\Auth;

use App\Entity\LoginRequest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AuthController extends AbstractFOSRestController
{
    /**
     * @SWG\Post(
     *     summary="LoginRequest",
     *     description="Authenticates and returns JWT token",
     *     produces={"application/json"},
     *     tags={"User"},
     *     @SWG\Parameter(
     *       name="body",
     *       in="body",
     *       description="Login request object",
     *       required=true,
     *       @Model(type=LoginRequest::class, groups={"Create"})
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     * )
     *
     * @ParamConverter("loginRequest", converter="json_converter_validator")
     *
     * @param LoginRequest $loginRequest
     *
     */
    public function login(LoginRequest $loginRequest)
    {

    }
}
