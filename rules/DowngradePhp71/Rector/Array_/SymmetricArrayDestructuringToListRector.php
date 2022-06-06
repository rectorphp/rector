<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp71\Rector\Array_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Stmt\Foreach_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector\SymmetricArrayDestructuringToListRectorTest
 */
final class SymmetricArrayDestructuringToListRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade Symmetric array destructuring to list() function', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Array_::class, Assign::class, Foreach_::class];
    }
    /**
     * @param Array_|Assign|Foreach_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Assign) {
            if ($node->var instanceof Array_) {
                $node->var = $this->processToList($node->var);
                return $node;
            }
            return null;
        }
        if ($node instanceof Foreach_ && $node->valueVar instanceof Array_) {
            $node->valueVar = $this->processToList($node->valueVar);
            return $node;
        }
        return null;
    }
    private function processToList(Array_ $array) : FuncCall
    {
        $args = [];
        foreach ($array->items as $arrayItem) {
            $args[] = $arrayItem instanceof ArrayItem ? new Arg($arrayItem->value) : null;
        }
        return new FuncCall(new Name('list'), $args);
    }
}
