<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector\Fixture;

use PHPUnit\Framework\TestCase;
use stdClass;

final class SomeTest extends TestCase
{
    public function test()
    {
        $objects = [ new stdClass(), new stdClass(), new stdClass() ];
        $this->assertContains(new stdClass(), $objects, 'message', false, false);
        $this->assertNotContains(new stdClass(), $objects, 'message', false, false);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector\Fixture;

use PHPUnit\Framework\TestCase;
use stdClass;

final class SomeTest extends TestCase
{
    public function test()
    {
        $objects = [ new stdClass(), new stdClass(), new stdClass() ];
        $this->assertContainsEquals(new stdClass(), $objects, 'message');
        $this->assertNotContainsEquals(new stdClass(), $objects, 'message');
    }
}

?>
