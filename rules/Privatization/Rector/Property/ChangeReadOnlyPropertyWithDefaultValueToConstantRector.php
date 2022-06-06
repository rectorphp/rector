<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Privatization\Rector\Property;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PhpParser\Node\Stmt\PropertyProperty;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\NodeManipulator\PropertyManipulator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Privatization\NodeFactory\ClassConstantFactory;
use RectorPrefix20220606\Rector\Privatization\NodeReplacer\PropertyFetchWithConstFetchReplacer;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\ChangeReadOnlyPropertyWithDefaultValueToConstantRectorTest
 */
final class ChangeReadOnlyPropertyWithDefaultValueToConstantRector extends AbstractRector
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
    public function __construct(PropertyManipulator $propertyManipulator, ClassConstantFactory $classConstantFactory, PropertyFetchWithConstFetchReplacer $propertyFetchWithConstFetchReplacer)
    {
        $this->propertyManipulator = $propertyManipulator;
        $this->classConstantFactory = $classConstantFactory;
        $this->propertyFetchWithConstFetchReplacer = $propertyFetchWithConstFetchReplacer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change property with read only status with default value to constant', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
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
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        // is property read only?
        if ($this->propertyManipulator->isPropertyChangeable($class, $node)) {
            return null;
        }
        /** @var Class_ $classLike */
        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        $this->propertyFetchWithConstFetchReplacer->replace($classLike, $node);
        return $this->classConstantFactory->createFromProperty($node);
    }
    private function shouldSkip(Property $property) : bool
    {
        if (\count($property->props) !== 1) {
            return \true;
        }
        $classLike = $this->betterNodeFinder->findParentType($property, Class_::class);
        if (!$classLike instanceof Class_) {
            return \true;
        }
        if ($property->attrGroups !== []) {
            return \true;
        }
        return $this->isObjectType($classLike, new ObjectType('PHP_CodeSniffer\\Sniffs\\Sniff'));
    }
}
