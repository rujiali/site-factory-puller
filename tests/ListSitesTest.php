<?php
namespace tests;

use AppBundle\Connector\ConnectorSites;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ListSitesTest extends TestCase
{

    protected $root;

    protected $successBody;

    protected $failBody;

    public function setUp()
    {
        parent::setUp();
        $this->root = __DIR__.'/../';
        copy($this->root.'/sitefactory.default.yml', $this->root.'/sitefactory.yml');
        $this->successBody = file_get_contents(__DIR__.'/Mocks/listSitesSuccess.json');
        $this->failBody = file_get_contents(__DIR__.'/Mocks/pingFail.json');
    }

    public function testSitesSuccess()
    {
        $mock = new MockHandler(
            [
            new Response(200, [], $this->successBody),
            ]
        );
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $connectorSites = new ConnectorSites($client);

        $this->assertTrue(is_array($connectorSites->listSites(NULL, NULL)));
    }

    public function testSitesFail()
    {
        $mock = new MockHandler(
            [
            new Response(403, [], $this->failBody),
            ]
        );
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $connectorSites = new ConnectorSites($client);

        $this->assertTrue($connectorSites->listSites(NULL, NULL) === 'Access denied');
    }

    public function tearDown()
    {
        parent::tearDown();
        unlink($this->root.'/sitefactory.yml');
    }
}