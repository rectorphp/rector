<?php

final class MyTest extends \PHPUnit_Framework_TestCase
{
    /**
     */
    public function testThrowExceptionWhenOperatorIsInvalid(): void
    {
        $this->expectException('Phpml\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid operator "~=" provided');
        Comparison::compare(1, 1, '~=');

        // extra line
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}