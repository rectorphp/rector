<?php

final class MyTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowExceptionWhenOperatorIsInvalid() : void
    {
        $this->expectException('Exception');
    }
}
