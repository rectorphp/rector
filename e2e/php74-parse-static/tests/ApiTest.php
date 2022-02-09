<?php

declare(strict_types=1);

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;

class ApiTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::createClient();
    }
}
