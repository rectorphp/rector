<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\FunctionLike;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\UnionType;
use Rector\DowngradePhp70\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector\DowngradeUnionTypeParamDeclarationRectorTest
 *
 * @requires PHP 8.0
 */
final class DowngradeUnionTypeParamDeclarationRector extends AbstractDowngradeParamDeclarationRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove the union type params, add @param tags instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function echoInput(string|int $input)
    {
        echo $input;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param string|int $input
     */
    public function echoInput($input)
    {
        echo $input;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    public function shouldRemoveParamDeclaration(Param $param, FunctionLike $functionLike): bool
    {
        if ($param->variadic) {
            return false;
        }

        if ($param->type === null) {
            return false;
        }

        // Check it is the union type
        return $param->type instanceof UnionType;
    }
}
