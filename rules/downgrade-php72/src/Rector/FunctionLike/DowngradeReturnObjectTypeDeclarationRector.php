<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FunctionLike;

use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\DowngradeReturnObjectTypeDeclarationRectorTest
 */
final class DowngradeReturnObjectTypeDeclarationRector extends AbstractDowngradeReturnTypeDeclarationRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            $this->getRectorDefinitionDescription(),
            [
                new CodeSample(
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
                ),
            ]
        );
    }

    public function getTypeNameToRemove(): string
    {
        return 'object';
    }
}
