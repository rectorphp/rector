<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Doctrine\ORM\Mapping as ORM;

class News
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=50, nullable=false)
     */
    private $title;
    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=255, nullable=false)
     */
    private $content;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping as ORM;

class News
{
    /**
     * @var integer
     */
    #[Column(name: 'id', type: 'bigint', nullable: false)]
    #[Id]
    private $id;
    /**
     * @var string
     */
    #[Column(name: 'title', type: 'string', length: 50, nullable: false)]
    private $title;
    /**
     * @var string
     */
    #[Column(name: 'content', type: 'string', length: 255, nullable: false)]
    private $content;
}

?>
