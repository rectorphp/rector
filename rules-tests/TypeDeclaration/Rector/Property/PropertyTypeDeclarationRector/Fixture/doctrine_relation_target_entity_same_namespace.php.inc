<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

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

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelated
{
}

class DoctrineRelationTargetEntitySameNamespace
{
    /**
     * @ORM\OneToOne(targetEntity="DoctrineRelated")
     * @var \Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture\DoctrineRelated|null
     */
    private $related;
}

?>
