<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\While_;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\While_;
use Rector\NodeManipulator\AssignManipulator;
use Rector\Php72\ValueObject\ListAndEach;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php72\Rector\While_\WhileEachToForeachRector\WhileEachToForeachRectorTest
 */
final class WhileEachToForeachRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private AssignManipulator $assignManipulator;
    public function __construct(AssignManipulator $assignManipulator)
    {
        $this->assignManipulator = $assignManipulator;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_EACH;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('each() function is deprecated, use foreach() instead.', [new CodeSample(<<<'CODE_SAMPLE'
while (list($key, $callback) = each($callbacks)) {
    // ...
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
foreach ($callbacks as $key => $callback) {
    // ...
}
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
while (list($key) = each($callbacks)) {
    // ...
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
foreach (array_keys($callbacks) as $key) {
    // ...
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [While_::class];
    }
    /**
     * @param While_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->cond instanceof Assign) {
            return null;
        }
        $listAndEach = $this->assignManipulator->matchListAndEach($node->cond);
        if (!$listAndEach instanceof ListAndEach) {
            return null;
        }
        $eachFuncCall = $listAndEach->getEachFuncCall();
        $list = $listAndEach->getList();
        if (!isset($eachFuncCall->getArgs()[0])) {
            return null;
        }
        $firstArg = $eachFuncCall->getArgs()[0];
        $foreachedExpr = \count($list->items) === 1 ? $this->nodeFactory->createFuncCall('array_keys', [$firstArg]) : $firstArg->value;
        $arrayItem = \array_pop($list->items);
        $isTrailingCommaLast = \false;
        if (!$arrayItem instanceof ArrayItem) {
            $foreachedExpr = $this->nodeFactory->createFuncCall('array_keys', [$eachFuncCall->args[0]]);
            /** @var ArrayItem $arrayItem */
            $arrayItem = \current($list->items);
            $isTrailingCommaLast = \true;
        }
        $foreach = new Foreach_($foreachedExpr, $arrayItem->value, ['stmts' => $node->stmts]);
        $this->mirrorComments($foreach, $node);
        // is key included? add it to foreach
        if ($list->items !== []) {
            /** @var ArrayItem|null $keyItem */
            $keyItem = \array_pop($list->items);
            if ($keyItem instanceof ArrayItem && !$isTrailingCommaLast) {
                $foreach->keyVar = $keyItem->value;
            }
        }
        return $foreach;
    }
}
