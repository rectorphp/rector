<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Property\AddFalseDefaultToBoolPropertyRector\AddFalseDefaultToBoolPropertyRectorTest
 */
final class AddFalseDefaultToBoolPropertyRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add false default to bool properties, to prevent null compare errors',
            [
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

        if (! $this->isBoolDocType($node)) {
            return null;
        }

        $onlyProperty->default = $this->nodeFactory->createFalse();

        return $node;
    }

    private function isBoolDocType(Property $property): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $phpDocInfo->getVarType() instanceof BooleanType;
    }
}
