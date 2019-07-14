<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use Fully\Qualified\ClassName\SomeTotallyDifferentButFirstListed\Foo\Bar as FooBarFirst;
use Fully\Qualified\ClassName\TheActual\Foo\Bar;
use Fully\Qualified\ClassName\TheActual\Bar\Foo;
use Fully\Qualified\ClassName\SomeTotallyDifferentButLastListed\Bar\Foo as BarFooLast;

class InjectFromVarWithTypeOfSameName
{
    /**
     * @Inject
     *
     * @var Bar
     */
    private $someBarDependency;

    /**
     * @Inject
     *
     * @var Foo
     */
    private $someFooDependency;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use Fully\Qualified\ClassName\SomeTotallyDifferentButFirstListed\Foo\Bar as FooBarFirst;
use Fully\Qualified\ClassName\TheActual\Foo\Bar;
use Fully\Qualified\ClassName\TheActual\Bar\Foo;
use Fully\Qualified\ClassName\SomeTotallyDifferentButLastListed\Bar\Foo as BarFooLast;

class InjectFromVarWithTypeOfSameName
{
    /**
     *
     * @var Bar
     */
    private $someBarDependency;

    /**
     *
     * @var Foo
     */
    private $someFooDependency;
    public function __construct(\Fully\Qualified\ClassName\TheActual\Foo\Bar $someBarDependency, \Fully\Qualified\ClassName\TheActual\Bar\Foo $someFooDependency)
    {
        $this->someBarDependency = $someBarDependency;
        $this->someFooDependency = $someFooDependency;
    }
}

?>
