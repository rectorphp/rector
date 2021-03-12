<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;

class ClassAnnotations
{
    /**
     * @Assert\Choice(callback={"Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass", "getValues"}, strict=true)
     */
    public $name;

    /**
     * @Serializer\Type("Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass")
     */
    public $time;

    /**
     * @Serializer\Type("Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClassy")
     */
    public $keepThis;

    /**
     * @ORM\OneToMany(targetEntity="Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass")
     */
    public $entityProperty;
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\ORM\Mapping as ORM;

class ClassAnnotations
{
    /**
     * @Assert\Choice(callback={"Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass", "getValues"}, strict=true)
     */
    public $name;

    /**
     * @Serializer\Type("Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass")
     */
    public $time;

    /**
     * @Serializer\Type("Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClassy")
     */
    public $keepThis;

    /**
     * @ORM\OneToMany(targetEntity="Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass")
     */
    public $entityProperty;
}

?>
