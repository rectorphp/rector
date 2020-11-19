<?php

namespace Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\AbstractTestCase;

class ToSelf extends AbstractTestCase
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

namespace Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\AbstractTestCase;

class ToSelf extends AbstractTestCase
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
