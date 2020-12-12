<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Bar as FooBarFirst;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Bar;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Foo;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Foo as BarFooLast;
use DI\Annotation\Inject;

class InjectFromVar3
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

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Bar as FooBarFirst;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Bar;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Foo;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Foo as BarFooLast;
use DI\Annotation\Inject;

class InjectFromVar3
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
    public function __construct(\Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Bar $someBarDependency, \Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Foo $someFooDependency)
    {
        $this->someBarDependency = $someBarDependency;
        $this->someFooDependency = $someFooDependency;
    }
}

?>
