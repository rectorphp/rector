<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = $this->getMock('SomeClass');

        $secondMock = $this->buildMock()
            ->getMock();
    }
}