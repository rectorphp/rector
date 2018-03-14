<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic\ValueObjectRemover;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ValueObjectRemoverRector extends AbstractValueObjectRemoverRector
{
    /**
     * @var string[]
     */
    private $valueObjectsToSimpleTypes = [];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @param string[] $valueObjectsToSimpleTypes
     */
    public function __construct(array $valueObjectsToSimpleTypes, NodeTypeResolver $nodeTypeResolver)
    {
        $this->valueObjectsToSimpleTypes = $valueObjectsToSimpleTypes;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @todo complete the rest of cases
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('[Dynamic] Remove values objects and use directly the value.', [
            new CodeSample('$someValue = new SomeValueObject("just_a_string");', '$someValue = "just_a_string";'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if ($node instanceof New_) {
            return $this->processNewCandidate($node);
        }

        if ($node instanceof Property) {
            return $this->processPropertyCandidate($node);
        }

        if ($node instanceof Name) {
            $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
            if ($parentNode instanceof Param) {
                return true;
            }
        }

        return $node instanceof NullableType;
    }

    /**
     * @param New_|Property|Name|NullableType $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof New_) {
            return $node->args[0];
        }

        if ($node instanceof Property) {
            return $this->refactorProperty($node);
        }

        if ($node instanceof Name) {
            return $this->refactorName($node);
        }

        if ($node instanceof NullableType) {
            return $this->refactorNullableType($node);
        }

        return null;
    }

    private function processNewCandidate(New_ $newNode): bool
    {
        if (count($newNode->args) !== 1) {
            return false;
        }

        $classNodeTypes = $this->nodeTypeResolver->resolve($newNode->class);

        return (bool) array_intersect($classNodeTypes, $this->getValueObjects());
    }

    private function processPropertyCandidate(Property $propertyNode): bool
    {
        $propertyNodeTypes = $this->nodeTypeResolver->resolve($propertyNode);

        return (bool) array_intersect($propertyNodeTypes, $this->getValueObjects());
    }

    private function refactorProperty(Property $propertyNode): Property
    {
        $newType = $this->matchNewType($propertyNode);
        if ($newType === null) {
            return $propertyNode;
        }

        return $propertyNode;
    }

    private function refactorNullableType(NullableType $nullableTypeNode): NullableType
    {
        $newType = $this->matchNewType($nullableTypeNode->type);
        if (! $newType) {
            return $nullableTypeNode;
        }

        return new NullableType($newType);
    }

    private function refactorName(Node $nameNode): ?Name
    {
        $newType = $this->matchNewType($nameNode);
        if ($newType === null) {
            return null;
        }

        return new Name($newType);
    }
}
