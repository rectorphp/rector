<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\FixtureAutoImport;

use Doctrine\ORM\Mapping\Id;

class MirrorAdditionalDocblock
{
    /**
     * @var string
     * @Id
     */
    public $name;

    public function __construct($name)
    {
        $this->name   = $name;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\FixtureAutoImport;

use Doctrine\ORM\Mapping\Id;

class MirrorAdditionalDocblock
{
    /**
     * @param string $name
     */
    public function __construct(
        /**
         * @Id
         */
        public $name
    )
    {
    }
}

?>
