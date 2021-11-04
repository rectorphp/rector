<?php

namespace Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\SomeAbstractTestCase;

class ToSelf extends SomeAbstractTestCase
{
    public function run()
    {
        $this->assertThis();
        self::assertThis();
        static::assertThis();
        parent::assertThis();
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\SomeAbstractTestCase;

class ToSelf extends SomeAbstractTestCase
{
    public function run()
    {
        self::assertThis();
        self::assertThis();
        self::assertThis();
        parent::assertThis();
    }
}

?>
