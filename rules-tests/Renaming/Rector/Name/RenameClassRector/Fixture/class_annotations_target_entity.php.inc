<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

final class ClassAnnotationsTargetEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass")
     */
    public $entityProperty;
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

final class ClassAnnotationsTargetEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass")
     */
    public $entityProperty;
}

?>
