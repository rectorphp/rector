<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\FunctionLike;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DowngradePhp72\Rector\FunctionLike\AbstractDowngradeReturnDeclarationRector;

/**
 * @see \Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeNullableTypeReturnDeclarationRector\DowngradeNullableTypeReturnDeclarationRectorTest
 */
final class DowngradeNullableTypeReturnDeclarationRector extends AbstractDowngradeReturnDeclarationRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove returning nullable types, add a @return tag instead',
            [
                new CodeSample(
                    <<<'PHP'
<?php

class SomeClass
{
    public function getResponseOrNothing(bool $flag): ?string
    {
        if ($flag) {
            return 'Hello world';
        }
        return null;
    }
}
PHP
                    ,
                    <<<'PHP'
<?php

class SomeClass
{
    /**
     * @return string|null
     */
    public function getResponseOrNothing(bool $flag)
    {
        if ($flag) {
            return 'Hello world';
        }
        return null;
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    public function shouldRemoveReturnDeclaration(FunctionLike $functionLike): bool
    {
        if ($functionLike->returnType === null) {
            return false;
        }

        // Check it is the union type
        return $functionLike->returnType instanceof NullableType;
    }
}
