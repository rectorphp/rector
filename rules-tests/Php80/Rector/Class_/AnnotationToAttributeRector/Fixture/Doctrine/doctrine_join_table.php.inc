<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\Doctrine;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

final class DoctrineJoinTable
{
    /**
     * @ORM\ManyToMany(targetEntity=DoctrineJoinTable::class)
     * @ORM\JoinTable(
     *     name="my_join_table",
     *     joinColumns={@ORM\JoinColumn(name="first_column", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="second_column", referencedColumnName="id")}
     * )
     **/
    protected Collection $list;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\Doctrine;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

final class DoctrineJoinTable
{
    /**
     * @ORM\JoinTable(
     *     name="my_join_table",
     *     joinColumns={@ORM\JoinColumn(name="first_column", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="second_column", referencedColumnName="id")}
     * )
     **/
    #[ORM\ManyToMany(targetEntity: DoctrineJoinTable::class)]
    protected Collection $list;
}

?>
