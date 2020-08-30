<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeTypeResolver\Node\AttributeKey;

class RenameVariableExpression
{
    private function shouldSkip(ClassMethod $classMethod): bool
    {
        /** @var ClassLike|null $class */
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return true;
        }

        /** @var Interface_ $class */
        return ! (bool) $class->extends;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeTypeResolver\Node\AttributeKey;

class RenameVariableExpression
{
    private function shouldSkip(ClassMethod $classMethod): bool
    {
        /** @var ClassLike|null $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return true;
        }

        /** @var Interface_ $classLike */
        return ! (bool) $classLike->extends;
    }
}

?>
