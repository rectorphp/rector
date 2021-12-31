<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\NodeDecorator\CreatedByRuleDecorator;

final class CreatedByRuleNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private readonly CreatedByRuleDecorator $createdByRuleDecorator,
        private readonly string $rectorClass
    ) {
    }

    public function enterNode(Node $node)
    {
        $this->createdByRuleDecorator->decorate($node, $this->rectorClass);
        return $node;
    }
}
