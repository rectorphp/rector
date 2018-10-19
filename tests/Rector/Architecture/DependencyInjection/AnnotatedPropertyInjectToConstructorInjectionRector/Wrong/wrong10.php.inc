<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Wrong;

class ClassWithInjects10ParentParent
{
    /**
     * @var \DateTimeInterface
     * @inject
     */
    public $dateTime;
}

class ClassWithInjects10Parent extends ClassWithInjects10ParentParent
{
}

class ClassWithInjects10 extends ClassWithInjects10Parent
{
    /**
     * @var \stdClass
     * @inject
     */
    public $property;
}
