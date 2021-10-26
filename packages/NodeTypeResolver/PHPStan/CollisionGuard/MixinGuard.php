<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\CollisionGuard;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class MixinGuard
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    /**
     * @param Stmt[] $stmts
     */
    public function containsMixinPhpDoc(array $stmts): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $node): bool {
            if (! $node instanceof FullyQualified && ! $node instanceof Class_) {
                return false;
            }

            if ($node instanceof Class_ && $node->isAnonymous()) {
                return false;
            }

            $className = $node instanceof FullyQualified ? $node->toString() : $node->namespacedName->toString();

            return $this->isCircularMixin($className);
        });
    }

    private function isCircularMixin(string $className): bool
    {
        // fix error in parallel test
        // use function_exists on purpose as using reflectionProvider broke the test in parallel
        if (function_exists($className)) {
            return false;
        }

        $hasClass = $this->reflectionProvider->hasClass($className);

        if (! $hasClass) {
            return false;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->isBuiltIn()) {
            return false;
        }

        foreach ($classReflection->getMixinTags() as $mixinTag) {
            $type = $mixinTag->getType();
            if (! $type instanceof ObjectType) {
                return false;
            }

            if ($type->getClassName() === $className) {
                return true;
            }

            if ($this->isCircularMixin($type->getClassName())) {
                return true;
            }
        }

        return false;
    }
}
