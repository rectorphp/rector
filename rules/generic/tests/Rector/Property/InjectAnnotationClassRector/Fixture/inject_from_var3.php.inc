<?php

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Bar as FooBarFirst;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Bar;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Foo;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Foo as BarFooLast;
use DI\Annotation\Inject;

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

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Bar as FooBarFirst;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Bar;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Foo;
use Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Foo as BarFooLast;
use DI\Annotation\Inject;

class InjectFromVarWithTypeOfSameName
{
    public function __construct(private \Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Bar $someBarDependency, private \Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\Source\Foo $someFooDependency)
    {
    }
}

?>
