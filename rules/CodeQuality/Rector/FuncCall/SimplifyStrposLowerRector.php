<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector\SimplifyStrposLowerRectorTest
 */
final class SimplifyStrposLowerRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify strpos(strtolower(), "...") calls', [new CodeSample('strpos(strtolower($var), "...")', 'stripos($var, "...")')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'strpos')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!isset($node->getArgs()[0])) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        if (!$firstArg->value instanceof FuncCall) {
            return null;
        }
        /** @var FuncCall $innerFuncCall */
        $innerFuncCall = $firstArg->value;
        if (!$this->isName($innerFuncCall, 'strtolower')) {
            return null;
        }
        // pop 1 level up
        $node->args[0] = $innerFuncCall->getArgs()[0];
        $node->name = new Name('stripos');
        return $node;
    }
}
