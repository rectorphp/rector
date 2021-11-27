<?php

namespace Rector\Core\Tests\Issues\NullableParamNamespaced\Fixture;

use PhpParser\Node\Stmt\ClassLike;

class DoNotAddShortFQCNClassNameDocblockNullable
{
    public function run(?ClassLike $classLike): bool
    {
        if (! $classLike instanceof ClassLike) {
            return false;
        }

        return $this->callPrivateMethod($classLike, true);
    }

    private function callPrivateMethod(ClassLike $classLike, bool $value)
    {
        return $classLike->getMethods() !== [];
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\NullableParamNamespaced\Fixture;

use PhpParser\Node\Stmt\ClassLike;

class DoNotAddShortFQCNClassNameDocblockNullable
{
    public function run(?ClassLike $classLike): bool
    {
        if (! $classLike instanceof ClassLike) {
            return false;
        }

        return $this->callPrivateMethod($classLike);
    }

    private function callPrivateMethod(ClassLike $classLike)
    {
        return $classLike->getMethods() !== [];
    }
}

?>
