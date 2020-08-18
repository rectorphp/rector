<?php

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use DateTimeInterface;

class ClassWithInjects8Parent
{
    /**
     * @var DateTimeInterface
     * @inject
     */
    public $dateTime;
}

class ClassWithInjects8 extends ClassWithInjects8Parent
{
    /**
     * @var \Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct
     * @inject
     */
    public $property;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use DateTimeInterface;

class ClassWithInjects8Parent
{
    /**
     * @var DateTimeInterface
     */
    private $dateTime;
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }
}

class ClassWithInjects8 extends ClassWithInjects8Parent
{
    /**
     * @var \Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct
     */
    private $property;
    public function __construct(\DateTimeInterface $dateTime, \Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct $property)
    {
        $this->property = $property;
        parent::__construct($dateTime);
    }
}

?>
