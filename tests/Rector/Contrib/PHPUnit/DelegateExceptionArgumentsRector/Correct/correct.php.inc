<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->setExpectedException('SomeException');
        $this->setExpectedException('SomeException');
        $this->expectExceptionMessage($message);
    }
}
