<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInternalType('string', $something);
        $this->assertNotInternalType('string', $something);
        $this->assertInternalType('array', $something);
        $this->assertNotInternalType('array', $something);
        $this->assertInternalType('numeric', $something);
        $this->assertNotInternalType('numeric', $something);
        $this->assertInternalType('integer', $something);
        $this->assertNotInternalType('integer', $something);
        $this->assertInternalType('float', $something);
        $this->assertNotInternalType('float', $something);
        $this->assertInternalType('bool', $something);
        $this->assertNotInternalType('bool', $something);
        $this->assertInternalType('object', $something);
        $this->assertNotInternalType('object', $something);
        $this->assertInternalType('resource', $something);
        $this->assertNotInternalType('resource', $something);
        $this->assertInternalType('scalar', $something);
        $this->assertNotInternalType('scalar', $something);
        $this->assertInternalType('callable', $something);
        $this->assertNotInternalType('callable', $something);
        $this->assertTrue(is_file($something));
        $this->assertFalse(is_file($something));
        $this->assertTrue(is_dir($something));
        $this->assertFalse(is_dir($something));
        $this->assertTrue(is_null($something));
        $this->assertFalse(is_null($something));
        $this->assertTrue(is_a($something));
        $this->assertFalse(is_a($something));
        $this->assertTrue(is_link($something));
        $this->assertFalse(is_link($something));
    }
}
