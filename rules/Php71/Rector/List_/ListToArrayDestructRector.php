<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\List_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Stmt\Foreach_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php71\Rector\List_\ListToArrayDestructRector\ListToArrayDestructRectorTest
 */
final class ListToArrayDestructRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `list()` to array destruct', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        list($id1, $name1) = $data;

        foreach ($data as list($id, $name)) {
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        [$id1, $name1] = $data;

        foreach ($data as [$id, $name]) {
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Assign::class, Foreach_::class];
    }
    /**
     * @param Assign|Foreach_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Assign) {
            if (!$node->var instanceof List_) {
                return null;
            }
            if ($node->var->getAttribute(AttributeKey::KIND) === List_::KIND_ARRAY) {
                return null;
            }
            $list = $node->var;
            $node->var = new Array_($list->items);
            return $node;
        }
        if (!$node->valueVar instanceof List_) {
            return null;
        }
        if ($node->valueVar->getAttribute(AttributeKey::KIND) === List_::KIND_ARRAY) {
            return null;
        }
        $list = $node->valueVar;
        // all list items must be set
        foreach ($list->items as $listItem) {
            if ($listItem === null) {
                return null;
            }
        }
        $node->valueVar = new Array_($list->items);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ARRAY_DESTRUCT;
    }
}
