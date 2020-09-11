<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\FunctionLike;

use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DowngradePhp72\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;

/**
 * @see \Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector\DowngradeNullableTypeParamDeclarationRectorTest
 */
final class DowngradeNullableTypeParamDeclarationRector extends AbstractDowngradeParamDeclarationRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove the nullable type params, add @param tags instead',
            [
                new CodeSample(
                    <<<'PHP'
<?php

class SomeClass
{
    public function run(?string $input)
    {
        // do something
    }
}
PHP
                    ,
                    <<<'PHP'
<?php

class SomeClass
{
    /**
     * @param string|null $input
     */
    public function run($input)
    {
        // do something
    }
}
PHP
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
        return $param->type instanceof NullableType;
    }
}
