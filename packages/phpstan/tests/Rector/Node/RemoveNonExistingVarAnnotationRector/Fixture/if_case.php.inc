<?php

namespace Rector\PHPStan\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class IfCase
{
    public function run()
    {
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod !== null) {
        }

        /** @var ClassMethod $notHere */
        if ($constructorMethod !== null) {
        }
    }
}

?>
-----
<?php

namespace Rector\PHPStan\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class IfCase
{
    public function run()
    {
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod !== null) {
        }

        if ($constructorMethod !== null) {
        }
    }
}

?>
