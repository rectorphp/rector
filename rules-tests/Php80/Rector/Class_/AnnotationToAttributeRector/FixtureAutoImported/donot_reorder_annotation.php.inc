<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Doctrine\ORM\Mapping as ORM;

class News
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint", nullable=false)
     */
    private $id;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping as ORM;

class News
{
    /**
     * @var integer
     */
    #[Id]
    #[Column(name: 'id', type: 'bigint', nullable: false)]
    private $id;
}

?>
