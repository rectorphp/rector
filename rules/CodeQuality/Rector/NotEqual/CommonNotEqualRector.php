<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\NotEqual;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/4294663/1348344
 * @see \Rector\Tests\CodeQuality\Rector\NotEqual\CommonNotEqualRector\CommonNotEqualRectorTest
 */
final class CommonNotEqualRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use common != instead of less known <> with same meaning', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($one, $two)
    {
        return $one <> $two;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($one, $two)
    {
        return $one != $two;
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
        return [\PhpParser\Node\Expr\BinaryOp\NotEqual::class];
    }
    /**
     * @param NotEqual $node
     */
    public function refactor(\PhpParser\Node $node) : \PhpParser\Node\Expr\BinaryOp\NotEqual
    {
        // invoke override to default "!="
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
