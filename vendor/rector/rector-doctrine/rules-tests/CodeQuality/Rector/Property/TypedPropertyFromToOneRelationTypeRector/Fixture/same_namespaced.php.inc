<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelated
{
}

class DoctrineRelationTargetEntitySameNamespace
{
    /**
     * @ORM\OneToOne(targetEntity="DoctrineRelated")
     */
    private $related;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelated
{
}

class DoctrineRelationTargetEntitySameNamespace
{
    /**
     * @ORM\OneToOne(targetEntity="DoctrineRelated")
     */
    private ?\Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Fixture\DoctrineRelated $related = null;
}

?>
