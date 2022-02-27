<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\ReturnEmptyNodes\Source;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ReturnEmptyStmtsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('return empty stmts', []);
    }

    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    public function refactor(Node $node)
    {
        return $node->stmts;
    }
}
