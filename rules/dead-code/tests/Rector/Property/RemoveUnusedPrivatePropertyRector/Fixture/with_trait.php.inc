<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

Trait SomeTrait {
    public function doStuffWithA ()
    {
        return $this->a;
    }
}
class WithTrait
{
    use SomeTrait;
    private $a;
}

?>
