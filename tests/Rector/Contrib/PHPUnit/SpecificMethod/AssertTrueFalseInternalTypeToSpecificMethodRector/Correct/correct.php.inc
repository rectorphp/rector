<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInternalType('string', $something);
        $this->assertNotInternalType('array', $something);

        $this->assertInternalType('integer', $something, 'third argument');
        $this->assertNotInternalType('bool', $something, 'third argument');

        $this->assertTrue(is_file($something));
        $this->assertFalse(is_null($something), 'second argument');
    }
}