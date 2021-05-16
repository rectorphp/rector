<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class CreatedByRuleNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private string $rectorClass
    ) {
    }

    public function enterNode(Node $node)
    {
        $node->setAttribute(AttributeKey::CREATED_BY_RULE, $this->rectorClass);
        return $node;
    }
}
