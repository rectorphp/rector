<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Fixture\Annotation;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector\Source\AnotherClass;

final class MyEntity
{
    /**
     * Wrong Usage: @ORM\Embedded has a "class" attribute, but no "targetEntity"!
     *
     * @ORM\Embedded(targetEntity="AnotherClass")
     */
    private $other;

    /**
     * Wrong Usage: @ORM\OneToMany has a "targetEntity" attribute, but no "class"!
     *
     * @ORM\OneToMany(class="AnotherClass")
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

final class MyEntity
{
    /**
     * Wrong Usage: @ORM\Embedded has a "class" attribute, but no "targetEntity"!
     *
     * @ORM\Embedded(targetEntity="AnotherClass")
     */
    private $other;

    /**
     * Wrong Usage: @ORM\OneToMany has a "targetEntity" attribute, but no "class"!
     *
     * @ORM\OneToMany(class="AnotherClass")
     */
    private readonly ?Collection $items;

    public function addItem(AnotherClass $myOtherEntity): void
    {
        $this->items->add($myOtherEntity);
    }
}

?>
