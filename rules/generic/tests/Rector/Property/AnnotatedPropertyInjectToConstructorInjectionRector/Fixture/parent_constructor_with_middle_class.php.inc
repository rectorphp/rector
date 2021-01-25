<?php

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

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

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

class ClassWithInjects10ParentParent
{
    public function __construct(private \DateTimeInterface $dateTime)
    {
    }
}

class ClassWithInjects10Parent extends ClassWithInjects10ParentParent
{
}

class ClassWithInjects10 extends ClassWithInjects10Parent
{
    public function __construct(\DateTimeInterface $dateTime, private \stdClass $property)
    {
        parent::__construct($dateTime);
    }
}

?>
