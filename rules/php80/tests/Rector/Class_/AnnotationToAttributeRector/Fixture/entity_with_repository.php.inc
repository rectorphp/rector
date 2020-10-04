<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="MyProject\UserRepository", readOnly=true)
 */
class EntityWithRepository
{
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[@ORM\Entity(repositoryClass: 'MyProject\UserRepository', readOnly: true)]
class EntityWithRepository
{
}

?>
