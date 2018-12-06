<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Dunglas;

trait BazTrait
{
    /**
     * @param int $a
     *
     * @return \DateTime
     */
    protected function inTrait($a)
    {
    }
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Dunglas;

trait BazTrait
{
    /**
     * @param int $a
     *
     * @return \DateTime
     */
    protected function inTrait(int $a): \DateTime
    {
    }
}
?>
