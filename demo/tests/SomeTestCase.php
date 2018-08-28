<?php declare(strict_types=1);

namespace App\Tests;

use App\SomeDemoService;

final class SomeTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException SomeException
     * @expectedExceptionCode 404
     */
    public function test()
    {
        $someService = new SomeDemoService();
        $someService->someMethod();
    }
}
