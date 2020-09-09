<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\BinaryOp;

use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Generic\Rector\AbstractIsAbleFunCallRector;

/**
 * @see \Rector\Php71\Tests\Rector\BinaryOp\IsIterableRector\IsIterableRectorTest
 */
final class IsIterableRector extends AbstractIsAbleFunCallRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes is_array + Traversable check to is_iterable', [
            new CodeSample('is_array($foo) || $foo instanceof Traversable;', 'is_iterable($foo);'),
        ]);
    }

    public function getFuncName(): string
    {
        return 'is_iterable';
    }

    public function getPhpVersion(): string
    {
        return PhpVersionFeature::IS_ITERABLE;
    }

    public function getType(): string
    {
        return 'Traversable';
    }
}
