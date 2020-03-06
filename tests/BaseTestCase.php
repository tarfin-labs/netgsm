<?php

namespace TarfinLabs\Netgsm\Tests;

use Faker\Factory;
use Illuminate\Foundation\Application;
use Mockery;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @var Factory
     */
    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $app = new Application();
        $app->singleton('translator', function () {
            $translate = Mockery::mock();
            $translate->shouldReceive('get')->andReturnArg(0);

            return $translate;
        });
    }
}
