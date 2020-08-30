<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\BinaryOp;

use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Generic\Rector\AbstractIsAbleRector;

/**
 * @see \Rector\Php73\Tests\Rector\BinaryOp\IsCountableRector\IsCountableRectorTest
 */
final class IsCountableRector extends AbstractIsAbleRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes is_array + Countable check to is_countable',
            [
                new CodeSample(
                    <<<'PHP'
is_array($foo) || $foo instanceof Countable;
PHP
                    ,
                    <<<'PHP'
is_countable($foo);
PHP
                ),
            ]
        );
    }

    protected function getType(): string
    {
        return 'Countable';
    }

    protected function getFuncName(): string
    {
        return 'is_countable';
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::IS_COUNTABLE;
    }
}
