<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class Repository {
    /**
     * @var object[]
     */
    public array $items = [];

    public function add(object $test) {
        $this->items[] = $test;
    }
}

class Hello {
    public function __construct(private Repository $repository)
    {}

    public function test(object $test) {
        $this->repository->add($test);
    }
}

class HelloTest extends TestCase
{
    use ObjectProphecy;

    private Hello $hello;

    protected function setUp(): void
    {
        $service = $this->prophesize(Service::class);
        $this->hello = new Hello($service->reveal());
    }

    public function testSomething()
    {
        $test = new \stdClass();
        $this->service->add($test)
            ->shouldBeCalled();

        $this->hello->test($test);
    }
}
