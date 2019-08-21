<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;

class ClassAnnotations
{
    /**
     * @Assert\Choice(callback={"Rector\Tests\Rector\Class_\RenameClassRector\Source\OldClass", "getValues"}, strict=true)
     */
    public $name;

    /**
     * @Serializer\Type("Rector\Tests\Rector\Class_\RenameClassRector\Source\OldClass")
     */
    public $time;

    /**
     * @Serializer\Type("Rector\Tests\Rector\Class_\RenameClassRector\Source\OldClassy")
     */
    public $keepThis;

    /**
     * @Serializer\Type("array<Rector\Tests\Rector\Class_\RenameClassRector\Source\OldClass>")
     * @Serializer\Type("iterable<key, Rector\Tests\Rector\Class_\RenameClassRector\Source\OldClass>")
     */
    public $flights = [];

    /**
     * @ORM\OneToMany(targetEntity="Rector\Tests\Rector\Class_\RenameClassRector\Source\OldClass")
     */
    public $entityProperty;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;

class ClassAnnotations
{
    /**
     * @Assert\Choice(callback={"Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClass", "getValues"}, strict=true)
     */
    public $name;

    /**
     * @Serializer\Type("Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClass")
     */
    public $time;

    /**
     * @Serializer\Type("Rector\Tests\Rector\Class_\RenameClassRector\Source\OldClassy")
     */
    public $keepThis;

    /**
     * @Serializer\Type("array<Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClass>")
     * @Serializer\Type("iterable<key, Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClass>")
     */
    public $flights = [];

    /**
     * @ORM\OneToMany(targetEntity="Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClass")
     */
    public $entityProperty;
}

?>
