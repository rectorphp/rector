<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/constructor_promotion
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector\DowngradePropertyPromotionRectorTest
 */
final class DowngradePropertyPromotionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;
    public function __construct(\Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator)
    {
        $this->classInsertManipulator = $classInsertManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change constructor property promotion to property asssign', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(public float $value = 0.0)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public float $value;

    public function __construct(float $value = 0.0)
    {
        $this->value = $value;
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $promotedParams = $this->resolvePromotedParams($node);
        if ($promotedParams === []) {
            return null;
        }
        $properties = $this->addPropertiesFromParams($promotedParams, $node);
        $this->addPropertyAssignsToConstructorClassMethod($properties, $node);
        foreach ($promotedParams as $promotedParam) {
            $promotedParam->flags = 0;
        }
        return $node;
    }
    /**
     * @return Param[]
     */
    private function resolvePromotedParams(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $constructorClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        $promotedParams = [];
        foreach ($constructorClassMethod->params as $param) {
            if ($param->flags === 0) {
                continue;
            }
            $promotedParams[] = $param;
        }
        return $promotedParams;
    }
    /**
     * @param Param[] $promotedParams
     * @return Property[]
     */
    private function addPropertiesFromParams(array $promotedParams, \PhpParser\Node\Stmt\Class_ $class) : array
    {
        $properties = $this->createPropertiesFromParams($promotedParams);
        $this->classInsertManipulator->addPropertiesToClass($class, $properties);
        return $properties;
    }
    /**
     * @param Property[] $properties
     */
    private function addPropertyAssignsToConstructorClassMethod(array $properties, \PhpParser\Node\Stmt\Class_ $class) : void
    {
        $assigns = [];
        foreach ($properties as $property) {
            $propertyName = $this->getName($property);
            $assign = $this->nodeFactory->createPropertyAssignment($propertyName);
            $assigns[] = new \PhpParser\Node\Stmt\Expression($assign);
        }
        /** @var ClassMethod $constructorClassMethod */
        $constructorClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        $constructorClassMethod->stmts = \array_merge($assigns, (array) $constructorClassMethod->stmts);
    }
    /**
     * @param Param[] $params
     * @return Property[]
     */
    private function createPropertiesFromParams(array $params) : array
    {
        $properties = [];
        foreach ($params as $param) {
            /** @var string $name */
            $name = $this->getName($param->var);
            $property = $this->nodeFactory->createProperty($name);
            $property->flags = $param->flags;
            $property->type = $param->type;
            $properties[] = $property;
        }
        return $properties;
    }
}
