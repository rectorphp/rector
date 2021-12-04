<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Privatization\NodeFactory\ClassConstantFactory;
use Rector\Privatization\NodeReplacer\PropertyFetchWithConstFetchReplacer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\ChangeReadOnlyPropertyWithDefaultValueToConstantRectorTest
 */
final class ChangeReadOnlyPropertyWithDefaultValueToConstantRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeFactory\ClassConstantFactory
     */
    private $classConstantFactory;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeReplacer\PropertyFetchWithConstFetchReplacer
     */
    private $propertyFetchWithConstFetchReplacer;
    public function __construct(\Rector\Core\NodeManipulator\PropertyManipulator $propertyManipulator, \Rector\Privatization\NodeFactory\ClassConstantFactory $classConstantFactory, \Rector\Privatization\NodeReplacer\PropertyFetchWithConstFetchReplacer $propertyFetchWithConstFetchReplacer)
    {
        $this->propertyManipulator = $propertyManipulator;
        $this->classConstantFactory = $classConstantFactory;
        $this->propertyFetchWithConstFetchReplacer = $propertyFetchWithConstFetchReplacer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change property with read only status with default value to constant', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string[]
     */
    private $magicMethods = [
        '__toString',
        '__wakeup',
    ];

    public function run()
    {
        foreach ($this->magicMethods as $magicMethod) {
            echo $magicMethod;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string[]
     */
    private const MAGIC_METHODS = [
        '__toString',
        '__wakeup',
    ];

    public function run()
    {
        foreach (self::MAGIC_METHODS as $magicMethod) {
            echo $magicMethod;
        }
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
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var PropertyProperty $onlyProperty */
        $onlyProperty = $node->props[0];
        // we need default value
        if ($onlyProperty->default === null) {
            return null;
        }
        if (!$node->isPrivate()) {
            return null;
        }
        // is property read only?
        if ($this->propertyManipulator->isPropertyChangeable($node)) {
            return null;
        }
        /** @var Class_ $classLike */
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        $this->propertyFetchWithConstFetchReplacer->replace($classLike, $node);
        return $this->classConstantFactory->createFromProperty($node);
    }
    private function shouldSkip(\PhpParser\Node\Stmt\Property $property) : bool
    {
        if (\count($property->props) !== 1) {
            return \true;
        }
        $classLike = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\Class_::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \true;
        }
        return $this->isObjectType($classLike, new \PHPStan\Type\ObjectType('PHP_CodeSniffer\\Sniffs\\Sniff'));
    }
}
