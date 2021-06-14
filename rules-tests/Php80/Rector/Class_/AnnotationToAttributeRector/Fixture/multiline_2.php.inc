<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Multiline2
{
    /**
     * @var Collection<int, Foo>
     *
     * @ORM\OrderBy({
     *     "createdAt": "DESC",
     * })
     *
     * @ORM\OneToMany(targetEntity=Foo::class, mappedBy="user", orphanRemoval=true)
     */
    private Collection $foos;
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[\Doctrine\ORM\Mapping\Entity]
class Multiline2
{
    /**
     * @var Collection<int, Foo>
     *
     *
     */
    #[\Doctrine\ORM\Mapping\OrderBy(['createdAt' => 'DESC'])]
    #[\Doctrine\ORM\Mapping\OneToMany(targetEntity: Foo::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $foos;
}

?>
