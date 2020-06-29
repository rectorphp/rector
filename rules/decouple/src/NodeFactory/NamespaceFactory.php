<?php

declare(strict_types=1);

namespace Rector\Decouple\NodeFactory;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Builder\ClassBuilder;
use Rector\Decouple\ValueObject\DecoupleClassMethodMatch;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NamespaceFactory
{
    /**
     * @param Stmt[] $classStmts
     */
    public function createNamespacedClassByNameAndStmts(
        Class_ $class,
        DecoupleClassMethodMatch $decoupleClassMethodMatch,
        array $classStmts
    ): Namespace_ {
        /** @var Namespace_|null $namespace */
        $namespace = $class->getAttribute(AttributeKey::NAMESPACE_NODE);
        if ($namespace === null) {
            throw new ShouldNotHappenException();
        }

        foreach ($namespace->stmts as $key => $stmt) {
            if (! $stmt instanceof Class_) {
                continue;
            }

            $namespace->stmts[$key] = $this->createNewClass($decoupleClassMethodMatch, $classStmts);
        }

        $this->createNewClass($decoupleClassMethodMatch, $classStmts);

        return $namespace;
    }

    /**
     * @param Stmt[] $classStmts
     */
    private function createNewClass(DecoupleClassMethodMatch $decoupleClassMethodMatch, array $classStmts): Class_
    {
        $classBuilder = new ClassBuilder($decoupleClassMethodMatch->getClassName());
        $classBuilder->addStmts($classStmts);
        $classBuilder->makeFinal();

        $parentClassName = $decoupleClassMethodMatch->getParentClassName();
        if ($parentClassName !== null) {
            $classBuilder->extend(new FullyQualified($parentClassName));
        }

        return $classBuilder->getNode();
    }
}
