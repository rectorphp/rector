<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\DifferntButFirstListed\Bar as FooBarFirst;
use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\Bar;
use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\Foo;
use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\DifferntButFirstListed\Foo as BarFooLast;
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

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\DifferntButFirstListed\Bar as FooBarFirst;
use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\Bar;
use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\Foo;
use Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\DifferntButFirstListed\Foo as BarFooLast;
use DI\Annotation\Inject;

class InjectFromVarWithTypeOfSameName
{
    public function __construct(private \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\Bar $someBarDependency, private \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\Foo $someFooDependency)
    {
    }
}

?>
