<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit\Runnable\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;

/**
 * Very dummy, use carefully and extend if needed
 */
final class PrefixingClassLikeNamesNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private $suffix;

    /**
     * @var string[]
     */
    private $classLikeNames = [];

    /**
     * @param string[] $classLikeNames
     */
    public function __construct(array $classLikeNames, string $suffix)
    {
        $this->classLikeNames = $classLikeNames;
        $this->suffix = $suffix;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof ClassLike) {
            return $this->refactorClassLike($node);
        }

        if ($node instanceof New_) {
            return $this->refactorNew($node);
        }

        return null;
    }

    private function refactorClassLike(ClassLike $classLike): ?ClassLike
    {
        if ($classLike->name === null) {
            return null;
        }

        // rename extends
        if ($classLike instanceof Class_) {
            $this->refactorClass($classLike);
        }

        foreach ($this->classLikeNames as $classLikeName) {
            $className = $classLike->name->toString();
            if ($className !== $classLikeName) {
                continue;
            }

            $classLike->name = new Identifier($classLikeName . '_' . $this->suffix);
            return $classLike;
        }

        return null;
    }

    private function refactorNew(New_ $new): ?New_
    {
        if (! $new->class instanceof Name) {
            return null;
        }

        foreach ($this->classLikeNames as $classLikeName) {
            $className = $new->class->toString();
            if ($className !== $classLikeName) {
                continue;
            }

            $new->class = new Name($classLikeName . '_' . $this->suffix);
            return $new;
        }

        return null;
    }

    private function refactorClass(Class_ $class): void
    {
        if ($class->extends === null) {
            return;
        }

        $extends = $class->extends->toString();

        foreach ($this->classLikeNames as $classLikeName) {
            if ($extends !== $classLikeName) {
                continue;
            }

            $class->extends = new Name($extends . '_' . $this->suffix);
            break;
        }
    }
}
