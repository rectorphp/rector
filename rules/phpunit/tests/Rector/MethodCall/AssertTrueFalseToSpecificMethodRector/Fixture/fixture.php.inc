<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector\Fixture;

abstract class Testing extends \PHPUnit\Framework\TestCase
{
}

final class MyTest extends Testing
{
    public function test()
    {
        $this->assertTrue(array_key_exists('...', ['...']), 'argument');
        $this->assertTrue(in_array('...', ['...'], true), 'argument');

        $this->assertFalse(is_readable('...'));
        $this->assertTrue(empty('...'));
        $this->assertFalse(file_exists('...'));
        $this->assertTrue(is_dir('...'));
        $this->assertFalse(is_infinite('...'));
        $this->assertTrue(is_nan('...'));
        $this->assertFalse(is_null('...'));
        $this->assertTrue(is_writable('...'));
        $this->assertFalse(array_search('...', ['...']), 'argument');
        $this->assertTrue(is_a($anything, 'Foo'), 'argument');
        $this->assertFalse(is_a($anything, Foo::class, true), 'argument');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector\Fixture;

abstract class Testing extends \PHPUnit\Framework\TestCase
{
}

final class MyTest extends Testing
{
    public function test()
    {
        $this->assertArrayHasKey('...', ['...'], 'argument');
        $this->assertContains('...', ['...'], 'argument');

        $this->assertNotIsReadable('...');
        $this->assertEmpty('...');
        $this->assertFileNotExists('...');
        $this->assertDirectoryExists('...');
        $this->assertFinite('...');
        $this->assertNan('...');
        $this->assertNotNull('...');
        $this->assertIsWritable('...');
        $this->assertNotContains('...', ['...'], 'argument');
        $this->assertInstanceOf('Foo', $anything, 'argument');
        $this->assertNotInstanceOf(Foo::class, $anything, 'argument');
    }
}

?>
