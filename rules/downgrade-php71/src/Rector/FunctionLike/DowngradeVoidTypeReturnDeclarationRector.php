<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\FunctionLike;

use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DowngradePhp72\Rector\FunctionLike\AbstractDowngradeReturnTypeDeclarationRector;

/**
 * @see \Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector\DowngradeVoidTypeReturnDeclarationRectorTest
 */
final class DowngradeVoidTypeReturnDeclarationRector extends AbstractDowngradeReturnTypeDeclarationRector
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
    public function run(): void
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
     * @return void
     */
    public function run()
    {
        // do something
    }
}
PHP
                ),
            ]
        );
    }

    public function getTypeNameToRemove(): string
    {
        return 'void';
    }
}
