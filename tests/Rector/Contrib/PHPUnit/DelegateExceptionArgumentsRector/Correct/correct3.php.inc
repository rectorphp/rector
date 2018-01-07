<?php

use PHPUnit\Framework\TestCase;

class ArrayInputTest extends TestCase
{
    public function testParseInvalidInput($expectedExceptionMessage)
    {
        if (method_exists($this, 'expectException')) {
            $this->setExpectedException('SomeException');
            $this->expectExceptionMessage($message);
        } else {
            $this->setExpectedException('InvalidArgumentException');
            $this->expectExceptionMessage($expectedExceptionMessage);
        }
    }
}
