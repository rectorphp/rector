<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FunctionLike;

use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\DowngradeParamObjectTypeDeclarationRectorTest
 */
final class DowngradeParamObjectTypeDeclarationRector extends AbstractDowngradeParamTypeDeclarationRector
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
    public function someFunction(object $someObject)
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
     * @param object $someObject
     */
    public function someFunction($someObject)
    {
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
