<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\CodeQuality\NodeAnalyzer\ConstructorPropertyDefaultExprResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector\InlineConstructorDefaultToPropertyRectorTest
 */
final class InlineConstructorDefaultToPropertyRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ConstructorPropertyDefaultExprResolver
     */
    private $constructorPropertyDefaultExprResolver;
    public function __construct(\Rector\CodeQuality\NodeAnalyzer\ConstructorPropertyDefaultExprResolver $constructorPropertyDefaultExprResolver)
    {
        $this->constructorPropertyDefaultExprResolver = $constructorPropertyDefaultExprResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move property default from constructor to property default', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $constructClassMethod = $node->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        // resolve property defaults
        $defaultPropertyExprAssigns = $this->constructorPropertyDefaultExprResolver->resolve($constructClassMethod);
        if ($defaultPropertyExprAssigns === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($defaultPropertyExprAssigns as $defaultPropertyExprAssign) {
            $property = $node->getProperty($defaultPropertyExprAssign->getPropertyName());
            if (!$property instanceof \PhpParser\Node\Stmt\Property) {
                continue;
            }
            $propertyProperty = $property->props[0];
            $propertyProperty->default = $defaultPropertyExprAssign->getDefaultExpr();
            $hasChanged = \true;
            $this->removeNode($defaultPropertyExprAssign->getAssignExpression());
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
