<?php

namespace Rector\Php\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\Php\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\AbstractTestCase;

class ToSelf extends AbstractTestCase
{
    public function run()
    {
        $this->assertThis();
        self::assertThis();
        parent::assertThis();
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\Php\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\AbstractTestCase;

class ToSelf extends AbstractTestCase
{
    public function run()
    {
        self::assertThis();
        self::assertThis();
        parent::assertThis();
    }
}

?>
