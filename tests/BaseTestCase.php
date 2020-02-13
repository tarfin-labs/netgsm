<?php

namespace TarfinLabs\Netgsm\Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @var Factory
     */
    protected $faker;

    public function setUp(): void
    {
        $this->faker = Factory::create();
    }
}
