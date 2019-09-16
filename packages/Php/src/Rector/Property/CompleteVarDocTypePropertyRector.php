<?php declare(strict_types=1);

namespace Rector\Php\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

/**
 * @see \Rector\Php\Tests\Rector\Property\CompleteVarDocTypePropertyRector\CompleteVarDocTypePropertyRectorTest
 */
final class CompleteVarDocTypePropertyRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var PropertyTypeInferer
     */
    private $propertyTypeInferer;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        PropertyTypeInferer $propertyTypeInferer
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->propertyTypeInferer = $propertyTypeInferer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Complete property `@var` annotations or correct the old ones', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
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
        $propertyType = $this->propertyTypeInferer->inferProperty($node);
        if ($propertyType instanceof MixedType) {
            return null;
        }

        $this->docBlockManipulator->changeVarTag($node, $propertyType);

        return $node;
    }
}
