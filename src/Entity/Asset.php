<?php

namespace App\Entity;

use Swagger\Annotations as SWG;

class Asset
{
    /**
     * @SWG\Property(description="id", example="1")
     *
     * @var string
     */
    protected $id;

    /**
     * @SWG\Property(description="Label", example="binance")
     *
     * @var string
     */
    protected $label;

    /**
     * @SWG\Property(description="Value", example="0.0095")
     *
     * @var float
     */
    protected $value;

    /**
     * @SWG\Property(description="Currency", example="BTC")
     *
     * @var string
     */
    protected $currency;

    /**
     * @var User
     */
    private $user;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): Asset
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return Asset
     */
    public function setValue(float $value): Asset
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Asset
     */
    public function setCurrency(string $currency): Asset
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Asset
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user->getId();
    }
}
