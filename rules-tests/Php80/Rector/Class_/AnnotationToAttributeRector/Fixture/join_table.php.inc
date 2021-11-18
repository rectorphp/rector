<?php

namespace Rector\Doctrine\Tests\Set\DoctrineORM29Set\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

class AnEntity
{
    /**
     * @ORM\ManyToMany(targetEntity=AnEntity::class)
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

namespace Rector\Doctrine\Tests\Set\DoctrineORM29Set\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

class AnEntity
{
    /**
     * @ORM\JoinTable(
     *     name="my_join_table",
     *     joinColumns={@ORM\JoinColumn(name="first_column", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="second_column", referencedColumnName="id")}
     * )
     **/
    #[\Doctrine\ORM\Mapping\ManyToMany(targetEntity: AnEntity::class)]
    protected Collection $list;
}

?>
