<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PSR4\NodeManipulator;

use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\Namespace_;
final class NamespaceManipulator
{
    public function removeClassLikes(Namespace_ $namespace) : void
    {
        foreach ($namespace->stmts as $key => $namespaceStatement) {
            if (!$namespaceStatement instanceof ClassLike) {
                continue;
            }
            unset($namespace->stmts[$key]);
        }
    }
}
