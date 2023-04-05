<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\CodeQuality\NodeAnalyzer\ConstructorPropertyDefaultExprResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector\InlineConstructorDefaultToPropertyRectorTest
 */
final class InlineConstructorDefaultToPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ConstructorPropertyDefaultExprResolver
     */
    private $constructorPropertyDefaultExprResolver;
    public function __construct(ConstructorPropertyDefaultExprResolver $constructorPropertyDefaultExprResolver)
    {
        $this->constructorPropertyDefaultExprResolver = $constructorPropertyDefaultExprResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move property default from constructor to property default', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $name;

    public function __construct()
    {
        $this->name = 'John';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $name = 'John';

    public function __construct()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        // resolve property defaults
        $defaultPropertyExprAssigns = $this->constructorPropertyDefaultExprResolver->resolve($constructClassMethod);
        if ($defaultPropertyExprAssigns === []) {
            return null;
        }
        $hasChanged = \false;
        $propertyProperties = $this->getNonReadonlyPropertyProperty($node);
        foreach ($defaultPropertyExprAssigns as $defaultPropertyExprAssign) {
            foreach ($propertyProperties as $propertyProperty) {
                if (!$this->isName($propertyProperty, $defaultPropertyExprAssign->getPropertyName())) {
                    continue;
                }
                $propertyProperty->default = $defaultPropertyExprAssign->getDefaultExpr();
                $hasChanged = \true;
                $this->removeNode($defaultPropertyExprAssign->getAssignExpression());
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @return PropertyProperty[]
     */
    private function getNonReadonlyPropertyProperty(Class_ $class) : array
    {
        $propertyProperties = [];
        foreach ($class->getProperties() as $property) {
            if ($property->isReadonly()) {
                continue;
            }
            $propertyProperties = \array_merge($propertyProperties, $property->props);
        }
        return $propertyProperties;
    }
}
