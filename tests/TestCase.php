<?php

namespace Vemcogroup\Weather\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Application;
use Vemcogroup\Weather\WeatherServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /* @var Client $client */
    protected $client;

    /* @var MockHandler $mockHandler */
    protected $mockHandler;

    protected $baseStubPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->baseStubPath = __DIR__ . '/stub/';
    }

    /**
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            WeatherServiceProvider::class,
        ];
    }

    /**
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        config()->set('weather.api_key', '1324');
        config()->set('weather.provider', 'darksky');
    }

    protected function addMockHandler($code, $body, $headers = []): void
    {
        if (!$this->mockHandler) {
            $this->createMockResponse();
        }

        $this->mockHandler->append(new Response($code, $headers, $body));
    }

    protected function createMockResponse(): void
    {
        $this->mockHandler = new MockHandler();

        $handler = HandlerStack::create($this->mockHandler);
        $this->client = new Client(['handler' => $handler]);
        $this->app->instance(Client::class, $this->client);
    }

    protected function getFile($filename): string
    {
        return File::get(($this->baseStubPath . $filename));
    }
}
