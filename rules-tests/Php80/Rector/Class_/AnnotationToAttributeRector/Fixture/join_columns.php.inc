<?php

namespace Rector\Doctrine\Tests\Set\DoctrineORM29Set\Fixture;

use Doctrine\ORM\Mapping as ORM;

class AnEntity
{
    /**
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id", referencedColumnName="id"),
     *   @ORM\JoinColumn(name="entity_type", referencedColumnName="entity_type"),
     * })
     **/
    protected $page;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Set\DoctrineORM29Set\Fixture;

use Doctrine\ORM\Mapping as ORM;

class AnEntity
{
    #[\Doctrine\ORM\Mapping\JoinColumn(name: 'entity_id', referencedColumnName: 'id')]
    #[\Doctrine\ORM\Mapping\JoinColumn(name: 'entity_type', referencedColumnName: 'entity_type')]
    protected $page;
}

?>
