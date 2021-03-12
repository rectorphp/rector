<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

Trait SomeTrait {
    public function doStuffWithA ()
    {
        return $this->a;
    }
}
class SkipWithTrait
{
    use SomeTrait;
    private $a;
}

?>
