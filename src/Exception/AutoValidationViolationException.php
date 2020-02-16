<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class AutoValidationViolationException extends \Exception
{

    /**
     * @var ConstraintViolationListInterface
     */
    private $constraintViolations;

    /**
     * AutoValidationViolationException constructor.
     *
     * @param ConstraintViolationListInterface $constraintViolationList
     */
    public function __construct(ConstraintViolationListInterface $constraintViolationList)
    {
        parent::__construct('Entity is invalid', 422);

        $this->constraintViolations = $constraintViolationList;
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getConstraintViolations(): ConstraintViolationListInterface
    {
        return $this->constraintViolations;
    }
}
