<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\BinaryOp;

use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Generic\Rector\AbstractIsAbleFunCallRector;

/**
 * @see \Rector\Php73\Tests\Rector\BinaryOp\IsCountableRector\IsCountableRectorTest
 */
final class IsCountableRector extends AbstractIsAbleFunCallRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes is_array + Countable check to is_countable',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
is_array($foo) || $foo instanceof Countable;
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
is_countable($foo);
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getType(): string
    {
        return 'Countable';
    }

    public function getFuncName(): string
    {
        return 'is_countable';
    }

    public function getPhpVersion(): string
    {
        return PhpVersionFeature::IS_COUNTABLE;
    }
}
