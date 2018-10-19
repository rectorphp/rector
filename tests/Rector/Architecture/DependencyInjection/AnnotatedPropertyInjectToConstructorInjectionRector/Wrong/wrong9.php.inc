<?php declare(strict_types=1);

use Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;

class ClassWithInjects9 extends ClassWithInjects9Parent
{
    /**
     * @var SomeProduct
     * @inject
     */
    public $property;
}

class ClassWithInjects9Parent
{
    /**
     * @var DateTimeInterface
     * @inject
     */
    public $dateTime;
}
