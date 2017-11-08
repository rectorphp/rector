<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        // @move to dynamic rector, that is used already
        $this->setExpectedException('SomeException');
        $this->setExpectedException('SomeException', $message);
    }
}
