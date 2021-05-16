<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CreatedByRuleNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    /**
     * @var string
     */
    private $rectorClass;
    public function __construct(string $rectorClass)
    {
        $this->rectorClass = $rectorClass;
    }
    public function enterNode(\PhpParser\Node $node)
    {
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CREATED_BY_RULE, $this->rectorClass);
        return $node;
    }
}
