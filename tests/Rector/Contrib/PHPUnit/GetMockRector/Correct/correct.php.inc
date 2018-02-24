<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = $this->createMock('SomeClass');

        $secondMock = $this->buildMock()
            ->getMock();
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}