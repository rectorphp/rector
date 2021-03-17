<?php

namespace Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

class StaticToThis extends BeLocalClass
{
    public function run()
    {
        static::assertThis();
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

class StaticToThis extends BeLocalClass
{
    public function run()
    {
        $this->assertThis();
    }
}

?>
