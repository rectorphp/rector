<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector\DowngradePipeToMultiCatchExceptionRectorTest
 */
final class DowngradePipeToMultiCatchExceptionRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade single one | separated to multi catch exception', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
try {
    // Some code...
} catch (ExceptionType1 | ExceptionType2 $exception) {
    $sameCode;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
try {
    // Some code...
} catch (ExceptionType1 $exception) {
    $sameCode;
} catch (ExceptionType2 $exception) {
    $sameCode;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\TryCatch::class];
    }
    /**
     * @param TryCatch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $originalCatches = $node->catches;
        foreach ($node->catches as $key => $catch) {
            if (\count($catch->types) === 1) {
                continue;
            }
            $types = $catch->types;
            $node->catches[$key]->types = [$catch->types[0]];
            foreach ($types as $keyCatchType => $catchType) {
                if ($keyCatchType === 0) {
                    continue;
                }
                $this->nodesToAddCollector->addNodeAfterNode(new \PhpParser\Node\Stmt\Catch_([$catchType], $catch->var, $catch->stmts), $node->catches[$key]);
            }
        }
        if ($this->nodeComparator->areNodesEqual($originalCatches, $node->catches)) {
            return null;
        }
        return $node;
    }
}
