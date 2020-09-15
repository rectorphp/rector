<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FunctionLike;

use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\DowngradeReturnObjectTypeDeclarationRectorTest
 */
final class DowngradeReturnObjectTypeDeclarationRector extends AbstractDowngradeReturnTypeDeclarationRector
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
    public function getSomeObject(): object
    {
        return new SomeObject();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    /**
     * @return object
     */
    public function getSomeObject()
    {
        return new SomeObject();
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
        return 'object';
    }
}
