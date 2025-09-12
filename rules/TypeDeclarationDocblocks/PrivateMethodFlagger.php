<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Parameter\FeatureFlags;
final class PrivateMethodFlagger
{
    public function isClassMethodPrivate(Class_ $class, ClassMethod $classMethod): bool
    {
        if ($classMethod->isPrivate()) {
            return \true;
        }
        if ($classMethod->isFinal() && !$class->extends instanceof Name && $class->implements === []) {
            return \true;
        }
        $isClassFinal = $class->isFinal() || FeatureFlags::treatClassesAsFinal($class);
        return $isClassFinal && !$class->extends instanceof Name && $class->implements === [] && $classMethod->isProtected();
    }
}
