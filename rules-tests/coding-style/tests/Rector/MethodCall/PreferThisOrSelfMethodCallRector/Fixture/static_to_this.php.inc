<?php

namespace Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

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

namespace Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;

class StaticToThis extends BeLocalClass
{
    public function run()
    {
        $this->assertThis();
    }
}

?>
