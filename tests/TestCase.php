<?php

namespace Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected Capsule $capsule;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        Container::setInstance($container);
        $container->instance('app', $container);
        $container->instance('config', new Repository([
            'app' => [
                'url' => 'http://localhost',
            ],
            'sso' => [
                'access_token_ttl' => 30,
                'refresh_token_ttl' => 30,
                'signing_key' => 'test-signing-key',
                'algorithm' => 'HS256',
            ],
        ]));

        Facade::setFacadeApplication($container);

        $this->capsule = new Capsule($container);
        $this->capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $this->capsule->setEventDispatcher(new Dispatcher($container));
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $this->setUpDatabase();
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
        Facade::setFacadeApplication(null);

        parent::tearDown();
    }

    protected function setUpDatabase(): void
    {
        $this->capsule->schema()->create('authorization_codes', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->json('payload');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }
}
