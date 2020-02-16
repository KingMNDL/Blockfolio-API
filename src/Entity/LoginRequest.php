<?php

namespace App\Entity;

use Swagger\Annotations as SWG;

class LoginRequest
{
    /**
     * @SWG\Property(description="Username" , example="admin")
     *
     * @var string
     */
    private $username;

    /**
     * @SWG\Property(description="Password", example="password")
     *
     * @var string
     */
    private $password;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }


}
