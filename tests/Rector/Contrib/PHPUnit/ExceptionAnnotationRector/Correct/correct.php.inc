<?php
final class MyTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $this->setExpectedException(\FooException::class);
        // some code
    }
}
