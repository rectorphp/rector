<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

trait TraitWithNonAbstractPrivate
{
    private function privateMethod() {}

    public function publicMethod() {
        $this->privateMethod();
    }
}

class RemoveIfMethodNonAbstractPrivateInTrait
{
    use TraitWithNonAbstractPrivate;

    private function privateMethod()
    {
        //code
    }
}
?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

trait TraitWithNonAbstractPrivate
{
    private function privateMethod() {}

    public function publicMethod() {
        $this->privateMethod();
    }
}

class RemoveIfMethodNonAbstractPrivateInTrait
{
    use TraitWithNonAbstractPrivate;
}
?>
