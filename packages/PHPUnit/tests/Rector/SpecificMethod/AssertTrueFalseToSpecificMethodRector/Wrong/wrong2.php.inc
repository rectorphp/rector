<?php declare(strict_types=1);

namespace Tests\AssertTrueFalseToSpecificMethodRector;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertNotFalse(\array_search($foo, $this->bar->toArray()));
    }
}
