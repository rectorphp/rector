<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(is_readable('...'), 'second argument');
        $this->assertTrue(array_key_exists('...', ['...']), 'second argument');
        $this->assertTrue(empty('...'), 'second argument');
        $this->assertTrue(file_exists('...'), 'second argument');
        $this->assertTrue(is_dir('...'), 'second argument');
        $this->assertTrue(is_infinite('...'), 'second argument');
        $this->assertTrue(is_null('...'), 'second argument');
        $this->assertTrue(is_writable('...'), 'second argument');
        $this->assertTrue(is_nan('...'), 'second argument');
    }
}
