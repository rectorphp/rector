<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change protected class method to private if possible', [
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

        if ($classLike->extends === null) {
            $this->makePrivate($node);
            return $node;
        }

        if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByParent($node, $classLike)) {
            return null;
        }

        $this->makePrivate($node);

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
