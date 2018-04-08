<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

final class ValueObjectRemoverRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $valueObjectsToSimpleTypes = [];

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @param string[] $valueObjectsToSimpleTypes
     */
    public function __construct(
        array $valueObjectsToSimpleTypes,
        DocBlockAnalyzer $docBlockAnalyzer,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->valueObjectsToSimpleTypes = $valueObjectsToSimpleTypes;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
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

        return false;
    }

    /**
     * @param New_ $node
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
            $newType = $this->matchNewType($node);
            if ($newType === null) {
                return null;
            }

            return new Name($newType);
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

    /**
     * @return string[]
     */
    private function getValueObjects(): array
    {
        return array_keys($this->valueObjectsToSimpleTypes);
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

        $this->docBlockAnalyzer->replaceVarType($propertyNode, $newType);

        return $propertyNode;
    }

    private function matchNewType(Node $node): ?string
    {
        $nodeTypes = $this->nodeTypeResolver->resolve($node);
        foreach ($nodeTypes as $propertyType) {
            if (! isset($this->valueObjectsToSimpleTypes[$propertyType])) {
                continue;
            }

            return $this->valueObjectsToSimpleTypes[$propertyType];
        }

        return null;
    }
}
