<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class DifferentVariableNameUsed
{
    /**
     * @var int
     */
    private $ID;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->ID = $id;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class DifferentVariableNameUsed
{
    public function __construct(
        /**
         * @var int
         */
        private $ID
    )
    {
    }
}

?>
