<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\NodeDecorator\CreatedByRuleDecorator;
final class CreatedByRuleNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    /**
     * @readonly
     * @var \Rector\Core\NodeDecorator\CreatedByRuleDecorator
     */
    private $createdByRuleDecorator;
    /**
     * @readonly
     * @var string
     */
    private $rectorClass;
    public function __construct(\Rector\Core\NodeDecorator\CreatedByRuleDecorator $createdByRuleDecorator, string $rectorClass)
    {
        $this->createdByRuleDecorator = $createdByRuleDecorator;
        $this->rectorClass = $rectorClass;
    }
    public function enterNode(\PhpParser\Node $node)
    {
        $this->createdByRuleDecorator->decorate($node, $this->rectorClass);
        return $node;
    }
}
