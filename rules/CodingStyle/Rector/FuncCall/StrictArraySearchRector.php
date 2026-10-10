<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\StrictArraySearchRector\StrictArraySearchRectorTest
 */
final class StrictArraySearchRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Makes array_search search for identical elements', [new CodeSample('array_search($value, $items);', 'array_search($value, $items, true);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'array_search')) {
            return null;
        }
        if (count($node->args) === 2) {
            $node->args[2] = $this->nodeFactory->createArg($this->nodeFactory->createTrue());
            return $node;
        }
        return null;
    }
}
