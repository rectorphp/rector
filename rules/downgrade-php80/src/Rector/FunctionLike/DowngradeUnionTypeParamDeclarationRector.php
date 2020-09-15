<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\FunctionLike;

use PhpParser\Node\Param;
use PhpParser\Node\UnionType;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DowngradePhp71\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;

/**
 * @see \Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector\DowngradeUnionTypeParamDeclarationRectorTest
 */
final class DowngradeUnionTypeParamDeclarationRector extends AbstractDowngradeParamDeclarationRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove the union type params, add @param tags instead',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
<?php

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
<?php

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
,
                    [
                        self::ADD_DOC_BLOCK => true,
                    ]
                ),
            ]
        );
    }

    public function shouldRemoveParamDeclaration(Param $param): bool
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
