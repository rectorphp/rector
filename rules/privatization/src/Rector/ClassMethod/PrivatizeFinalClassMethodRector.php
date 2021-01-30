<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\PrivatizeFinalClassMethodRectorTest
 */
final class PrivatizeFinalClassMethodRector extends AbstractRector
{
    /**
     * @var ClassMethodVisibilityGuard
     */
    private $classMethodVisibilityGuard;

    public function __construct(ClassMethodVisibilityGuard $classMethodVisibilityGuard)
    {
        $this->classMethodVisibilityGuard = $classMethodVisibilityGuard;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change protected class method to private if possible',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    protected function someMethod()
    {
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    private function someMethod()
    {
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
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

        if (! $classLike->isFinal()) {
            return null;
        }

        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }

        if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByParent($node)) {
            return null;
        }

        if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByTrait($node)) {
            return null;
        }

        $this->visibilityManipulator->makePrivate($node);

        return $node;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($this->isName($classMethod, 'createComponent*')) {
            return true;
        }

        return ! $classMethod->isProtected();
    }
}
