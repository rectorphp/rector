<?php

final class MyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \FooException
     */
    public function test()
    {
        // some code
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}