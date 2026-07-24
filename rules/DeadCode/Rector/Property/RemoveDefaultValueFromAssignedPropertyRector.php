<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Property\RemoveDefaultValueFromAssignedPropertyRector\RemoveDefaultValueFromAssignedPropertyRectorTest
 */
final class RemoveDefaultValueFromAssignedPropertyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ConstructorAssignDetector $constructorAssignDetector;
    public function __construct(ConstructorAssignDetector $constructorAssignDetector)
    {
        $this->constructorAssignDetector = $constructorAssignDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove redundant default value from property that is always assigned in the constructor', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private ?SomeType $someType = null;

    public function __construct(SomeType $someType)
    {
        $this->someType = $someType;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private ?SomeType $someType;

    public function __construct(SomeType $someType)
    {
        $this->someType = $someType;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->getMethod(MethodName::CONSTRUCT) instanceof ClassMethod) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // untyped properties are handled by RemoveNullPropertyInitializationRector
            if (!$property->type instanceof Node) {
                continue;
            }
            if ($property->hooks !== []) {
                continue;
            }
            foreach ($property->props as $propertyProperty) {
                if (!$propertyProperty->default instanceof Expr) {
                    continue;
                }
                $propertyName = $this->getName($propertyProperty);
                if (!$this->constructorAssignDetector->isPropertyAssigned($node, $propertyName)) {
                    continue;
                }
                $propertyProperty->default = null;
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
