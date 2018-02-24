<?php

final class MyTest extends \PHPUnit_Framework_TestCase
{
    /**
     */
    public function test()
    {
        $this->expectException('FooException');
        // some code
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}