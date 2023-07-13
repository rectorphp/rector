<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Fixture\Annotation;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Source\{AnotherClass as AnotherClassA, AnotherClass as AnotherClassB};
use Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Source\AnotherClass as AnotherClassC;

final class MyEntity
{
    /**
     * @ORM\Embedded(class="AnotherClassA")
     */
    private $other;

    /**
     * @ORM\OneToMany(targetEntity="AnotherClassB")
     */
    private readonly ?Collection $items;

    /**
     * @ORM\OneToMany(targetEntity="AnotherClassC")
     */
    private readonly ?Collection $items2;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Fixture\Annotation;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Source\{AnotherClass as AnotherClassA, AnotherClass as AnotherClassB};
use Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Source\AnotherClass as AnotherClassC;

final class MyEntity
{
    /**
     * @ORM\Embedded(class=\Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Source\AnotherClass::class)
     */
    private $other;

    /**
     * @ORM\OneToMany(targetEntity=\Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Source\AnotherClass::class)
     */
    private readonly ?Collection $items;

    /**
     * @ORM\OneToMany(targetEntity=\Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Source\AnotherClass::class)
     */
    private readonly ?Collection $items2;
}

?>
