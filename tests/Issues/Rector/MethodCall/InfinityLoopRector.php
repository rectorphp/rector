<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Core\Tests\Issues\InfiniteLoopTest
 */
final class InfinityLoopRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     * @return Assign|null
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'modify')) {
            return null;
        }

        return new Assign($node->var, $node);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Road to left... to left... to lefthell..', []);
    }
}
