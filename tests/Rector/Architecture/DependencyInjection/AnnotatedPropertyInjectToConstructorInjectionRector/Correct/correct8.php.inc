<?php declare(strict_types=1);

use Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct;

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
    public function __construct(\DateTimeInterface $dateTime, \Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeProduct $property)
    {
        parent::__construct($dateTime);
        $this->property = $property;
    }
}
