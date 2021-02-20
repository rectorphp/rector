<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Bar as FooBarFirst;
use Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\Bar;
use Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\Foo;
use Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Foo as BarFooLast;
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

namespace Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Fixture;

use Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Bar as FooBarFirst;
use Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\Bar;
use Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\Foo;
use Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\DifferntButFirstListed\Foo as BarFooLast;
use DI\Annotation\Inject;

class InjectFromVarWithTypeOfSameName
{
    public function __construct(private \Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\Bar $someBarDependency, private \Rector\DependencyInjection\Tests\Rector\Property\InjectAnnotationClassRector\Source\Foo $someFooDependency)
    {
    }
}

?>
