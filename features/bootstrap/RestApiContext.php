<?php

use App\Entity\User;
use App\Tests\ApiClient;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use PHPUnit\Framework\Assert as Assertions;
use Behat\Gherkin\Node\TableNode;

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
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    /**
     * RestApiContext constructor.
     * @param KernelInterface $kernel
     * @param UserManagerInterface $userManager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        KernelInterface $kernel,
        UserManagerInterface $userManager,
        EntityManagerInterface $entityManager
    )
    {
        $this->kernel = $kernel;
        $this->kernel->boot();
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->client = new ApiClient($this->kernel);
    }

    /**
     * Sends HTTP request to specific URL with json data.
     *
     * @param string $method
     * @param string $url
     *
     * @param array $data
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
     * Sends HTTP request to specific URL with json data.
     *
     * @param string $method
     * @param string $url
     * @param PyStringNode $jsonData
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with json data:$/
     */
    public function iSendARequestWithJsonData($method, $url, PyStringNode $jsonData)
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
            $jsonData
        );
    }

    /**
     * @Given there are FOS User with the following details:
     *
     * @param TableNode $users
     */
    public function thereAreFosUserWithTheFollowingDetails(TableNode $users)
    {
        foreach ($users->getColumnsHash() as $key => $val) {

            $confirmationToken = isset($val['confirmation_token']) && $val['confirmation_token'] != ''
                ? $val['confirmation_token']
                : null;

            /** @var User $user */
            $user = $this->userManager->createUser();

            $user->setUsername($val['username']);
            $user->setPlainPassword($val['password']);
            $user->setEmail($val['email']);
            $user->setEnabled(1);

            if (!empty($confirmationToken)) {
                $user->setPasswordRequestedAt(new \DateTime('now'));
            }

            $this->userManager->updateUser($user);
        }
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
        $expected = (int)$code;
        $actual = (int)$this->response->getStatusCode();

        Assertions::assertSame($expected, $actual);
    }

    /**
     * @Then /^the response json should have key "([^"]*)"$/
     * @param string $key
     */
    public function theResponseJsonShouldHaveKey($key)
    {
        $jsonData = json_decode($this->response->getContent(), true);

        Assertions::assertArrayHasKey($key, $jsonData);
    }

    /**
     * Authenticate and obtain JWT token
     *
     * @param string $username
     *
     * @Given /^I am successfully authenticated as a FOS user: "([^"]*)"$/
     */
    public function iAmSuccessfullyAuthenticatedAsFosUser($username)
    {
        /** @var \Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager $jwtManager */
        $jwtManager = $this->kernel->getContainer()
            ->get('lexik_jwt_authentication.jwt_manager');
        $userManager = $this->kernel->getContainer()
            ->get('fos_user.user_manager');

        $user = $userManager->findUserByUsername($username);
        $this->token = $jwtManager->create($user);
    }


    /**
     * @BeforeScenario
     */
    public function setUpDb()
    {
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($classes);
    }


    /**
     * @AfterScenario
     */
    public function cleanDB()
    {
        $this->entityManager->getConnection()->prepare("SET FOREIGN_KEY_CHECKS = 0;")->execute();

        foreach ($this->entityManager->getConnection()->getSchemaManager()->listTableNames() as $tableNames) {
            $sql = 'DROP TABLE ' . $tableNames;
            $this->entityManager->getConnection()->prepare($sql)->execute();
        }
        $this->entityManager->getConnection()->prepare("SET FOREIGN_KEY_CHECKS = 1;")->execute();
    }

    /**
     * @Given /^there are Assets with following details:$/
     */
    public function thereAreAssetsWithFollowingDetails(TableNode $assets)
    {
        foreach ($assets->getColumnsHash() as $key => $val) {

            /** @var User $user */
            $asset = new \App\Entity\Asset();

            $asset->setLabel($val['label']);
            $asset->setCurrency($val['currency']);
            $asset->setValue($val['value']);

            $userManager = $this->kernel->getContainer()
                ->get('fos_user.user_manager');

            $user = $userManager->findUserByUsername($val['user']);

            $asset->setUser($user);

            $this->entityManager->persist($asset);
            $this->entityManager->flush();
        }
    }
}
