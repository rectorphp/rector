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
    public function __construct(private \DateTimeInterface $dateTime)
    {
    }
}

class ClassWithInjects8 extends ClassWithInjects8Parent
{
    public function __construct(\DateTimeInterface $dateTime, private \Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeProduct $property)
    {
        parent::__construct($dateTime);
    }
}

?>
