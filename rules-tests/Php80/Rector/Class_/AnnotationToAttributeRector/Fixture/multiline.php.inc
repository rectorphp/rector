<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="foos",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="name_unique", columns={"name", "name2"}),
 *     }
 * )
 */
class Multiline
{
    /**
     * @ORM\Column(type="string")
     */
    private string $name;
    /**
     * @ORM\Column(type="string")
     */
    private string $name2;
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[\Doctrine\ORM\Mapping\Entity]
#[\Doctrine\ORM\Mapping\Table(name: 'foos', uniqueConstraints: ['(name="name_unique", columns={"name", "name2"})'])]
class Multiline
{
    #[\Doctrine\ORM\Mapping\Column(type: 'string')]
    private string $name;
    #[\Doctrine\ORM\Mapping\Column(type: 'string')]
    private string $name2;
}

?>
