<?php

declare(strict_types=1);

use Foo\Bar\{ SomeLongClass as SomeClass };
use Foo\Bar\{ SomeLongClass2 };
class GroupUseClass
{
    public function someMethod()
    {
        new SomeClass;
    }

    public function someMethod2()
    {
        new SomeLongClass2;
    }
}

?>
-----
<?php

namespace Rector\Tests\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Fixture;

use Foo\Bar\{SomeLongClass as SomeClass};
use Foo\Bar\{SomeLongClass2};
class GroupUseClass
{
    public function someMethod()
    {
        new \Foo\Bar\SomeLongClass;
    }

    public function someMethod2()
    {
        new \Foo\Bar\SomeLongClass2;
    }
}
