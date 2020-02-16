<?php

use App\Tests\ApiClient;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use PHPUnit\Framework\Assert as Assertions;

/**
 * Class RestApiContext
 */
class RestApiContext implements Context
{
    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string|null
     */
    protected $token;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Response
     */
    protected $response;


    /**
     * RestApiContext constructor.
     *
     * @param KernelInterface          $kernel
     */
    public function __construct(
        KernelInterface $kernel
    ) {
        $this->kernel = $kernel;
        $this->kernel->boot();
        $this->client = new ApiClient($this->kernel);
    }
    /**
     * Sends HTTP request to specific URL with json data.
     *
     * @param string $method
     * @param string $url
     *
     * @param array  $data
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)"$/
     */
    public function iSendARequest($method, $url, $data = [])
    {
        $headers = $this->headers;
        $headers['CONTENT_TYPE'] = 'application/json';

        if ($this->token) {
            $headers['HTTP_Authorization'] = sprintf('Bearer %s', $this->token);
        }

        $this->response = $this->client->request(
            $method,
            $url,
            [],
            [],
            $headers,
            $data ? json_encode($data) : null
        );
    }

    /**
     * Checks that response has specific status code.
     *
     * @param string $code status code
     *
     * @Then the response code should be :arg1
     */
    public function theResponseCodeShouldBe($code)
    {
        $expected = (int) $code;
        $actual = (int) $this->response->getStatusCode();

        Assertions::assertSame($expected, $actual);
    }
}
