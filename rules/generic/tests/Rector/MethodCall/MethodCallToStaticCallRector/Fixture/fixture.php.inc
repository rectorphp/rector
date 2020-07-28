<?php

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallToStaticCallRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\MethodCallToStaticCallRector\AnotherDependency;

final class SomeClass
{
    private $anotherDependency;

    public function __construct(AnotherDependency $anotherDependency)
    {
        $this->anotherDependency = $anotherDependency;
    }

    public function loadConfiguration()
    {
        return $this->anotherDependency->process('value');
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallToStaticCallRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\MethodCallToStaticCallRector\AnotherDependency;

final class SomeClass
{
    private $anotherDependency;

    public function __construct(AnotherDependency $anotherDependency)
    {
        $this->anotherDependency = $anotherDependency;
    }

    public function loadConfiguration()
    {
        return \StaticCaller::anotherMethod('value');
    }
}

?>
