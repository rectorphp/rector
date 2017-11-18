<?php

final class MyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Phpml\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid operator "~=" provided
     */
    public function testThrowExceptionWhenOperatorIsInvalid(): void
    {
        Comparison::compare(1, 1, '~=');
    }
}
