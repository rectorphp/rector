<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelated
{
}

class DoctrineRelationTargetEntitySameNamespace
{
    /**
     * @ORM\OneToOne(targetEntity="DoctrineRelated")
     * @var \Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture\DoctrineRelated|null
     */
    private $related;
}

?>
