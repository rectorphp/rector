<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class WithDoctrineAnnotations
{
    /**
     * @ORM\Column(type="text")
     */
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class WithDoctrineAnnotations
{
    public function __construct(
        /**
         * @ORM\Column(type="text")
         */
        private string $content
    )
    {
    }

    public function getContent(): string
    {
        return $this->content;
    }
}

?>
