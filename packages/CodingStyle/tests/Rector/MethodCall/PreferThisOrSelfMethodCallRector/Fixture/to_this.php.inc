<?php

namespace Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

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

namespace Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

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
