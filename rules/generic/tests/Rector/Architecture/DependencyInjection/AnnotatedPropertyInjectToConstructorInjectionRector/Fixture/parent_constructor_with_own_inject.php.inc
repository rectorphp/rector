<?php

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use DateTimeInterface;
use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;

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
     * @var \Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeProduct
     * @inject
     */
    public $property;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use DateTimeInterface;
use Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;

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
     * @var SomeProduct
     */
    private $property;
    public function __construct(\DateTimeInterface $dateTime, \Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector\Source\SomeProduct $property)
    {
        $this->property = $property;
        parent::__construct($dateTime);
    }
}

?>
