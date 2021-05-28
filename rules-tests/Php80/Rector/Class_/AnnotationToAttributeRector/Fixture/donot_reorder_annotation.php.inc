<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Doctrine\ORM\Mapping as ORM;

/**
 * News
 *
 * @ORM\Table(name="news")
 * @ORM\Entity(repositoryClass="DoctrineFixtureDemo\Repository\NewsRepository")
 */
class News
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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

use Doctrine\ORM\Mapping as ORM;

/**
 * News
 */
#[\Doctrine\ORM\Mapping\Table(name: 'news')]
#[\Doctrine\ORM\Mapping\Entity(repositoryClass: 'DoctrineFixtureDemo\Repository\NewsRepository')]
class News
{
    /**
     * @var integer
     */
    #[\Doctrine\ORM\Mapping\Column(name: 'id', type: 'bigint', nullable: false)]
    #[\Doctrine\ORM\Mapping\Id]
    #[\Doctrine\ORM\Mapping\GeneratedValue(strategy: 'IDENTITY')]
    private $id;
    /**
     * @var string
     */
    #[\Doctrine\ORM\Mapping\Column(name: 'title', type: 'string', length: 50, nullable: false)]
    private $title;
    /**
     * @var string
     */
    #[\Doctrine\ORM\Mapping\Column(name: 'content', type: 'string', length: 255, nullable: false)]
    private $content;
}

?>
