<?php

use PHPUnit\Framework\TestCase;

class ArrayInputTest extends TestCase
{
    public function testParseInvalidInput($expectedExceptionMessage)
    {
        if (method_exists($this, 'expectException')) {
            $this->setExpectedException('SomeException', $message);
        } else {
            $this->setExpectedException('InvalidArgumentException', $expectedExceptionMessage);
        }
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}