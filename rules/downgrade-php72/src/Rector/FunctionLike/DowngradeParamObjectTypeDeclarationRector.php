<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FunctionLike;

/**
 * @see \Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector\DowngradeParamObjectTypeDeclarationRectorTest
 */
final class DowngradeParamObjectTypeDeclarationRector extends AbstractDowngradeParamTypeDeclarationRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            $this->getRectorDefinitionDescription(),
            [
                new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
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
