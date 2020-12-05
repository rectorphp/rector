<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\FunctionLike;

use Rector\DowngradePhp72\Rector\FunctionLike\AbstractDowngradeReturnTypeDeclarationRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp80\Tests\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector\DowngradeReturnStaticTypeDeclarationRectorTest
 */
final class DowngradeReturnStaticTypeDeclarationRector extends AbstractDowngradeReturnTypeDeclarationRector
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
    public function getStatic(): static
    {
        return new static();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getTypeNameToRemove(): string
    {
        return 'static';
    }
}
