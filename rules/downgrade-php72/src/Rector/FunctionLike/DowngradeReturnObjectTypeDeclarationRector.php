<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FunctionLike;

/**
 * @see \Rector\DowngradePhp72\Tests\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector\DowngradeReturnObjectTypeDeclarationRectorTest
 */
final class DowngradeReturnObjectTypeDeclarationRector extends AbstractDowngradeReturnTypeDeclarationRector
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
