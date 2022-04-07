<?php

declare(strict_types=1);

namespace Rector\Visibility\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Visibility\Rector\ClassMethod\ExplicitPublicClassMethodRector\ExplicitPublicClassMethodRectorTest
 */
final class ExplicitPublicClassMethodRector extends AbstractRector
{
    public function __construct(
        private readonly VisibilityManipulator $visibilityManipulator,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add explicit public method visibility.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    function foo()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function foo()
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
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
        // already non-public
        if (! $node->isPublic()) {
            return null;
        }

        // explicitly public
        if ($this->visibilityManipulator->hasVisibility($node, \Rector\Core\ValueObject\Visibility::PUBLIC)) {
            return null;
        }

        $this->visibilityManipulator->makePublic($node);

        return $node;
    }
}
