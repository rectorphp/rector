<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\FunctionLike;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use Rector\DowngradePhp70\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp70\Tests\Rector\FunctionLike\DowngradeTypeParamDeclarationRector\DowngradeTypeParamDeclarationRectorTest
 */
final class DowngradeTypeParamDeclarationRector extends AbstractDowngradeParamDeclarationRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove the type params, add @param tags instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(string $input)
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
     * @param string $input
     */
    public function run($input)
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
        return true;
    }
}
