<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\BinaryOp;

use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Generic\Rector\AbstractIsAbleRector;

/**
 * @see \Rector\Php71\Tests\Rector\BinaryOp\IsIterableRector\IsIterableRectorTest
 */
final class IsIterableRector extends AbstractIsAbleRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes is_array + Traversable check to is_iterable', [
            new CodeSample('is_array($foo) || $foo instanceof Traversable;', 'is_iterable($foo);'),
        ]);
    }

    protected function getFuncName(): string
    {
        return 'is_iterable';
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::IS_ITERABLE;
    }

    protected function getType(): string
    {
        return 'Traversable';
    }
}
