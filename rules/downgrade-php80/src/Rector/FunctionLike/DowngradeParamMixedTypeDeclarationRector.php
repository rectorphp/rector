<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\FunctionLike;

use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\ValueObject\PhpVersionFeature;
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
                new CodeSample(
                    <<<'PHP'
<?php

class SomeClass
{
    public function someFunction(mixed $anything)
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
     * @param mixed $anything
     */
    public function someFunction($anything)
    {
    }
}
PHP
                ),
            ]
        );
    }

    public function getPhpVersionFeature(): string
    {
        return PhpVersionFeature::OBJECT_TYPE;
    }

    public function getTypeNameToRemove(): string
    {
        return 'mixed';
    }
}
