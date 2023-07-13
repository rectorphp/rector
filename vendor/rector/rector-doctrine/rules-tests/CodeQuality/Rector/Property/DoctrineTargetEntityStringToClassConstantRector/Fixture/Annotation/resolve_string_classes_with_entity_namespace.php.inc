<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Fixture\Annotation;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Source\AnotherClass;

/**
 * @see https://github.com/doctrine/orm/issues/8818
 */
final class MyEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="App:AnotherClass")
     */
    private readonly ?Collection $items;

    public function addItem(AnotherClass $myOtherEntity): void
    {
        $this->items->add($myOtherEntity);
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Fixture\Annotation;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Source\AnotherClass;

/**
 * @see https://github.com/doctrine/orm/issues/8818
 */
final class MyEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="App:AnotherClass")
     */
    private readonly ?Collection $items;

    public function addItem(AnotherClass $myOtherEntity): void
    {
        $this->items->add($myOtherEntity);
    }
}

?>
