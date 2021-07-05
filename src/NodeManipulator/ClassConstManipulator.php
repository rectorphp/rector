<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassConstManipulator
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver,
        private AstResolver $astResolver
    ) {
    }

    public function hasClassConstFetch(ClassConst $classConst, ClassReflection $classReflection): bool
    {
        $classLike = $classConst->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            $ancestorClass = $this->astResolver->resolveClassFromClassReflection(
                $ancestorClassReflection,
                $ancestorClassReflection->getName()
            );

            if (! $ancestorClass instanceof ClassLike) {
                continue;
            }

            // has in class?
            $isClassConstFetchFound = (bool) $this->betterNodeFinder->find($ancestorClass, function (Node $node) use (
                $classConst
            ): bool {
                // property + static fetch
                if (! $node instanceof ClassConstFetch) {
                    return false;
                }

                return $this->isNameMatch($node, $classConst);
            });

            if ($isClassConstFetchFound) {
                return true;
            }
        }

        return false;
    }

    private function isNameMatch(ClassConstFetch $classConstFetch, ClassConst $classConst): bool
    {
        $selfConstantName = 'self::' . $this->nodeNameResolver->getName($classConst);
        $staticConstantName = 'static::' . $this->nodeNameResolver->getName($classConst);

        return $this->nodeNameResolver->isNames($classConstFetch, [$selfConstantName, $staticConstantName]);
    }
}
