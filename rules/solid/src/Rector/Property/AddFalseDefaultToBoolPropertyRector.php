<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\BooleanType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\SOLID\Tests\Rector\Property\AddFalseDefaultToBoolPropertyRector\AddFalseDefaultToBoolPropertyRectorTest
 */
final class AddFalseDefaultToBoolPropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add false default to bool properties, to prevent null compare errors', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var bool
     */
    private $isDisabled;
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var bool
     */
    private $isDisabled = false;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->props) !== 1) {
            return null;
        }

        $onlyProperty = $node->props[0];
        if ($onlyProperty->default !== null) {
            return null;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo->getVarType() instanceof BooleanType) {
            return null;
        }

        $onlyProperty->default = $this->createFalse();

        return $node;
    }
}
