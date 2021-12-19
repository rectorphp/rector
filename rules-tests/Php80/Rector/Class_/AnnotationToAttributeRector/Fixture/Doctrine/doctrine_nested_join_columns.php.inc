<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\Doctrine;

use Doctrine\ORM\Mapping as ORM;

final class DoctrineNestedJoinColumns
{
    /**
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id", referencedColumnName="id"),
     *   @ORM\JoinColumn(name="entity_type", referencedColumnName="entity_type"),
     * })
     */
    protected $page;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\Doctrine;

use Doctrine\ORM\Mapping as ORM;

final class DoctrineNestedJoinColumns
{
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(name: 'entity_type', referencedColumnName: 'entity_type')]
    protected $page;
}

?>
