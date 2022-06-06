<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\StrictArraySearchRector\StrictArraySearchRectorTest
 */
final class StrictArraySearchRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Makes array_search search for identical elements', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('array_search($value, $items);', 'array_search($value, $items, true);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'array_search')) {
            return null;
        }
        if (\count($node->args) === 2) {
            $node->args[2] = $this->nodeFactory->createArg($this->nodeFactory->createTrue());
        }
        return $node;
    }
}
