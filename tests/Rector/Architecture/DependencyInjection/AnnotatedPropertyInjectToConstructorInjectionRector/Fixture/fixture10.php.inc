<?php

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

?>
-----
<?php

namespace Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Wrong;

class ClassWithInjects10ParentParent
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }
}

class ClassWithInjects10Parent extends ClassWithInjects10ParentParent
{
}

class ClassWithInjects10 extends ClassWithInjects10Parent
{
    /**
     * @var \stdClass
     */
    private $property;
    public function __construct(\DateTimeInterface $dateTime, \stdClass $property)
    {
        parent::__construct($dateTime);
        $this->property = $property;
    }
}

?>
