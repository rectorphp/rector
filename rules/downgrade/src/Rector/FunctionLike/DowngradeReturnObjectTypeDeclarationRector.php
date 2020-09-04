<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\FunctionLike;

use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;

/**
 * @see \Rector\Downgrade\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\DowngradeReturnObjectTypeDeclarationRectorTest
 */
final class DowngradeReturnObjectTypeDeclarationRector extends AbstractDowngradeReturnTypeDeclarationRector
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
    public function getSomeObject(): object
    {
        return new SomeObject();
    }
}
PHP
                    ,
                    <<<'PHP'
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
PHP
                ),
            ]
        );
    }

    public function getPhpVersionFeature(): string
    {
        return PhpVersionFeature::OBJECT_TYPE;
    }

    /**
     * Name of the type to remove
     */
    protected function getReturnTypeName(): string
    {
        return 'object';
    }
}
