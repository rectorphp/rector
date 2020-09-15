<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\FunctionLike;

use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DowngradePhp72\Rector\FunctionLike\AbstractDowngradeParamTypeDeclarationRector;

/**
 * @see \Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector\DowngradeParamMixedTypeDeclarationRectorTest
 */
final class DowngradeParamMixedTypeDeclarationRector extends AbstractDowngradeParamTypeDeclarationRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            $this->getRectorDefinitionDescription(),
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    public function someFunction(mixed $anything)
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    /**
     * @param mixed $anything
     */
    public function someFunction($anything)
    {
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

    public function getTypeNameToRemove(): string
    {
        return 'mixed';
    }
}
