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
final class DowngradePipeToMultiCatchExceptionRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade single one | separated to multi catch exception', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [TryCatch::class];
    }
    /**
     * @param TryCatch $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->catches as $key => $catch) {
            if (\count($catch->types) === 1) {
                continue;
            }
            $catchTypes = $catch->types;
            /** @var Node\Name $firstType */
            $firstType = \array_shift($catchTypes);
            $catch->types = [$firstType];
            foreach ($catchTypes as $catchType) {
                $newCatch = new Catch_([$catchType], $catch->var, $catch->stmts);
                \array_splice($node->catches, $key + 1, 0, [$newCatch]);
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
