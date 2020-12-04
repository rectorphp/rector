<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Stmt\TryCatch;
use Rector\DowngradePhp71\Rector\FunctionLike\AbstractDowngradeRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp71\Tests\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRectorTest
 */
final class DowngradePipeToMultiCatchExceptionRector extends AbstractDowngradeRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Downgrade single one | separated to multi catch exception',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
try {
    // Some code...
 } catch (ExceptionType1 | ExceptionType2 $exception) {
    $sameCode;
 }
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
try {
    // Some code...
} catch (ExceptionType1 $exception) {
    $sameCode;
} catch (ExceptionType2 $exception) {
    $sameCode;
}
CODE_SAMPLE
                    ,
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [TryCatch::class];
    }

    /**
     * @param TryCatch $node
     */
    public function refactor(Node $node): ?Node
    {
        return [];
    }
}
