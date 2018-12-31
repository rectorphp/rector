<?php

namespace Rector\Php\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\Php\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

final class ToThis extends BeLocalClass
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

use Rector\Php\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

final class ToThis extends BeLocalClass
{
    public function run()
    {
        $this->assertThis();
        $this->assertThis();
        parent::assertThis();
    }
}

?>
