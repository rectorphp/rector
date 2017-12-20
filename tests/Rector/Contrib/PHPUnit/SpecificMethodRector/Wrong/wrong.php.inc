<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(array_key_exists('...', ['...']), 'argument');
        $this->assertTrue(in_array('...', ['...']), 'argument');
        $this->assertFalse(is_readable('...'));
        $this->assertTrue(empty('...'));
        $this->assertFalse(file_exists('...'));
        $this->assertTrue(is_dir('...'));
        $this->assertFalse(is_infinite('...'));
        $this->assertTrue(is_nan('...'));
        $this->assertFalse(is_null('...'));
        $this->assertTrue(is_writable('...'));
        $this->assertFalse(array_search('...', ['...']), 'argument');
    }
}
