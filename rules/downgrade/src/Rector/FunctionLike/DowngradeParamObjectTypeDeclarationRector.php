<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\FunctionLike;

use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;

/**
 * @see \Rector\Downgrade\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\DowngradeParamObjectTypeDeclarationRectorTest
 */
final class DowngradeParamObjectTypeDeclarationRector extends AbstractDowngradeParamTypeDeclarationRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            $this->getRectorDefinitionDescription(),
            [
                new CodeSample(
                    <<<'PHP'
<?php

class SomeClass
{
    public function someFunction(object $someObject)
    {
    }
}
PHP
                    ,
                    <<<'PHP'
<?php

class SomeClass
{
    /**
     * @param object $someObject
     */
    public function someFunction($someObject)
    {
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * Name of the type to remove
     */
    protected function getParamTypeName(): string
    {
        return 'object';
    }

    protected function getPhpVersionFeature(): string
    {
        return PhpVersionFeature::OBJECT_TYPE;
    }
}
