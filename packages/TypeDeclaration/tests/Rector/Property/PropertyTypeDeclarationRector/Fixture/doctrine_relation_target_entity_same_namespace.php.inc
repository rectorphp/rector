<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelated
{
}

class DoctrineRelationTargetEntitySameNamespace
{
    /**
     * @ORM\OneToOne(targetEntity="DoctrineRelated")
     * @var \Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture\DoctrineRelated
     */
    private $related;
}

?>
