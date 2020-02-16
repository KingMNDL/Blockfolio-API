<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use PhpCollection\CollectionInterface;

class User extends BaseUser
{
    protected $id;

    /**
     * @var ArrayCollection|CollectionInterface|Asset[]
     */
    protected $tests;

    protected $currency;

    public function __construct()
    {
        parent::__construct();
        $this->tests = new ArrayCollection();
    }

    /**
     * @param Asset $asset
     *
     * @return $this
     */
    public function addTest(Asset $asset): self
    {
        $this->tests[] = $asset;

        return $this;
    }

    /**
     * @param Asset $asset
     */
    public function removeTests(Asset $asset): void
    {
        $this->tests->removeElement($asset);
    }

    /**
     * Get assets
     *
     * @return Collection|Asset[]
     */
    public function getTests()
    {
        return $this->tests;
    }
}