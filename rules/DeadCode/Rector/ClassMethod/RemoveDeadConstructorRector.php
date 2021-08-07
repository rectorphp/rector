<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeManipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector\RemoveDeadConstructorRectorTest
 */
final class RemoveDeadConstructorRector extends AbstractRector
{
    public function __construct(
        private ClassMethodManipulator $classMethodManipulator
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove empty constructor', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        if ($this->shouldSkipPropertyPromotion($node)) {
            return null;
        }

        if ($classLike->extends instanceof FullyQualified) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    private function shouldSkipPropertyPromotion(ClassMethod $classMethod): bool
    {
        if (! $this->isName($classMethod, MethodName::CONSTRUCT)) {
            return true;
        }
        if ($classMethod->stmts === null) {
            return true;
        }
        if ($classMethod->stmts !== []) {
            return true;
        }

        if ($this->classMethodManipulator->isPropertyPromotion($classMethod)) {
            return true;
        }

        return $this->classMethodManipulator->isNamedConstructor($classMethod);
    }
}
