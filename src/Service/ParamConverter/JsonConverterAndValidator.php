<?php

namespace App\Service\ParamConverter;

use App\Exception\AutoValidationViolationException;
use FOS\RestBundle\Request\RequestBodyParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JsonConverterAndValidator implements ParamConverterInterface
{

    /**
     * @var RequestBodyParamConverter
     */
    private $requestBodyParamConverter;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * JsonConverterAndValidator constructor.
     *
     * @param RequestBodyParamConverter $requestBodyParamConverter
     * @param ValidatorInterface        $validator
     */
    public function __construct(RequestBodyParamConverter $requestBodyParamConverter, ValidatorInterface $validator)
    {
        $this->requestBodyParamConverter = $requestBodyParamConverter;
        $this->validator = $validator;
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     *
     * @return bool
     * @throws AutoValidationViolationException
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $options = $configuration->getOptions();
        if (isset($options['query'])) {
            $converted = $this->convertRequestQuery($request, $configuration);
        } else if (isset($options['form'])) {
            $converted = $this->convertRequestFormQuery($request, $configuration);
        } else {
            $converted = $this->requestBodyParamConverter->apply($request, $configuration);
        }

        if (!$converted) {
            return false;
        }

        $entity = $request->attributes->get($configuration->getName());

        $constraintViolations = $this->validator->validate($entity, null, $this->getValidationGroups($request));

        if ($constraintViolations->count() === 0) {
            return true;
        }

        throw new AutoValidationViolationException($constraintViolations);
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    private function convertRequestQuery(Request $request, ParamConverter $configuration): bool
    {
        $content = json_encode($request->query->all());
        if ($content === false) {
            return false;
        }

        $newRequest = new Request();
        $newRequest->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $content
        );

        $converted = $this->requestBodyParamConverter->apply($newRequest, $configuration);

        if (!$converted) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $newRequest->attributes->get($configuration->getName()));

        return true;
    }

    /**
     * This option works for x-www-form-urlencoded only
     *
     * @param Request        $request
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    private function convertRequestFormQuery(Request $request, ParamConverter $configuration): bool
    {
        $formData = $request->getContent();

        if (empty($formData)) {
            return false;
        }

        $params = array();
        parse_str($formData, $params);
        $params = json_encode($params);

        $newRequest = new Request();
        $newRequest->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $params
        );
        $newRequest->headers->set('Content-Type', 'application/json', true);

        $converted = $this->requestBodyParamConverter->apply($newRequest, $configuration);

        if (!$converted) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $newRequest->attributes->get($configuration->getName()));

        return true;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getValidationGroups(Request $request): array
    {
        $validation = $request->attributes->get('_validate', '');
        $rawGroups = explode(',', $validation);

        $groups = array_filter(
            $rawGroups,
            function ($group) {
                return trim($group) !== '';
            }
        );

        if (\count($groups) === 0) {
            $groups[] = 'Default';
        }

        return $groups;
    }

    /**
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() !== null && $configuration->getConverter() === 'json_converter_validator';
    }
}
