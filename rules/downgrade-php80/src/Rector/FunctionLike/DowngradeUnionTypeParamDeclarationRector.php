<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\FunctionLike;

use PhpParser\Node\UnionType;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DowngradePhp72\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;

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
                new CodeSample(
                    <<<'PHP'
<?php

class SomeClass
{
    public function echoInput(string|int $input)
    {
        echo $input;
    }
}
PHP
                    ,
                    <<<'PHP'
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
PHP
                ),
            ]
        );
    }

    public function getPhpVersionFeature(): string
    {
        return PhpVersionFeature::UNION_TYPES;
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
