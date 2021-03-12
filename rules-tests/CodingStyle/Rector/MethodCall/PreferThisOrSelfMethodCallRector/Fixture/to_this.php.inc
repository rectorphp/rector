<?php

namespace Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

final class ToThis extends BeLocalClass
{
    public function run()
    {
        $this->assertThis();
        self::assertThis();
        parent::assertThis();
        self::assertThisAndThat(1, 2);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

final class ToThis extends BeLocalClass
{
    public function run()
    {
        $this->assertThis();
        $this->assertThis();
        parent::assertThis();
        $this->assertThisAndThat(1, 2);
    }
}

?>
