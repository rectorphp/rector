<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\FunctionLike;

use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DowngradePhp72\Rector\FunctionLike\AbstractDowngradeReturnTypeDeclarationRector;

/**
 * @see \Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector\DowngradeReturnStaticTypeDeclarationRectorTest
 */
final class DowngradeReturnStaticTypeDeclarationRector extends AbstractDowngradeReturnTypeDeclarationRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            $this->getRectorDefinitionDescription(),
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
<?php

class SomeClass
{
    public function getStatic(): static
    {
        return new static();
    }
}
PHP
                    ,
                    <<<'PHP'
<?php

class SomeClass
{
    /**
     * @return static
     */
    public function getStatic()
    {
        return new static();
    }
}
PHP
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
        return 'static';
    }
}
