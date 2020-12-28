<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\FunctionLike;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use Rector\DowngradePhp70\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeParamDeclarationRector\DowngradeIterablePseudoTypeParamDeclarationRectorTest
 */
final class DowngradeIterablePseudoTypeParamDeclarationRector extends AbstractDowngradeParamDeclarationRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove the iterable pseudo type params, add @param tags instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(iterable $iterator)
    {
        // do something
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param mixed[]|\Traversable $iterator
     */
    public function run($iterator)
    {
        // do something
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    public function shouldRemoveParamDeclaration(Param $param, FunctionLike $functionLike): bool
    {
        if ($param->type === null) {
            return false;
        }
        if (! $param->type instanceof Identifier) {
            return false;
        }
        return $param->type->toString() === 'iterable';
    }
}
