<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector\SymmetricArrayDestructuringToListRectorTest
 */
final class SymmetricArrayDestructuringToListRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade Symmetric array destructuring to list() function', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
[$id1, $name1] = $data;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
list($id1, $name1) = $data;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Expr\Assign && $this->nodeComparator->areNodesEqual($node, $parentNode->var)) {
            return $this->processToList($node);
        }
        if (!$parentNode instanceof \PhpParser\Node\Stmt\Foreach_) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($node, $parentNode->valueVar)) {
            return null;
        }
        return $this->processToList($node);
    }
    private function processToList(\PhpParser\Node\Expr\Array_ $array) : \PhpParser\Node\Expr\FuncCall
    {
        $args = [];
        foreach ($array->items as $arrayItem) {
            $args[] = $arrayItem instanceof \PhpParser\Node\Expr\ArrayItem ? new \PhpParser\Node\Arg($arrayItem->value) : null;
        }
        /** @var Arg[]|VariadicPlaceholder[] $args */
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('list'), $args);
    }
}
