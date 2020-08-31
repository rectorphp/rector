<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Manipulator\PropertyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\SOLID\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\ChangeReadOnlyPropertyWithDefaultValueToConstantRectorTest
 */
final class ChangeReadOnlyPropertyWithDefaultValueToConstantRector extends AbstractRector
{
    /**
     * @var PropertyManipulator
     */
    private $propertyManipulator;

    public function __construct(PropertyManipulator $propertyManipulator)
    {
        $this->propertyManipulator = $propertyManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change property with read only status with default value to constant', [
            new CodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
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
PHP
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var PropertyProperty $onlyProperty */
        $onlyProperty = $node->props[0];

        // we need default value
        if ($onlyProperty->default === null) {
            return null;
        }

        if (! $node->isPrivate()) {
            return null;
        }

        // is property read only?
        if ($this->propertyManipulator->isPropertyChangeable($node)) {
            return null;
        }

        $this->replacePropertyFetchWithClassConstFetch($node, $onlyProperty);

        return $this->createClassConst($node, $onlyProperty);
    }

    private function shouldSkip(Property $property): bool
    {
        if (count($property->props) !== 1) {
            return true;
        }

        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return true;
        }

        return $this->isObjectType($classLike, 'PHP_CodeSniffer\Sniffs\Sniff');
    }

    private function replacePropertyFetchWithClassConstFetch(Node $node, PropertyProperty $propertyProperty): void
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            throw new ShouldNotHappenException();
        }

        $propertyName = $this->getName($propertyProperty);
        $constantName = $this->createConstantNameFromProperty($propertyProperty);

        $this->traverseNodesWithCallable($classLike, function (Node $node) use ($propertyName, $constantName): ?ClassConstFetch {
            if (! $this->isLocalPropertyFetch($node)) {
                return null;
            }

            if (! $this->isName($node->name, $propertyName)) {
                return null;
            }

            // replace with constant fetch
            return new ClassConstFetch(new Name('self'), $constantName);
        });
    }

    private function createClassConst(Property $property, PropertyProperty $propertyProperty): ClassConst
    {
        $constantName = $this->createConstantNameFromProperty($propertyProperty);

        /** @var Expr $defaultValue */
        $defaultValue = $propertyProperty->default;
        $const = new Const_($constantName, $defaultValue);

        $classConst = new ClassConst([$const]);
        $classConst->flags = $property->flags & ~ Class_::MODIFIER_STATIC;

        $classConst->setAttribute(AttributeKey::PHP_DOC_INFO, $property->getAttribute(AttributeKey::PHP_DOC_INFO));

        return $classConst;
    }

    private function createConstantNameFromProperty(PropertyProperty $propertyProperty): string
    {
        $propertyName = $this->getName($propertyProperty);
        $constantName = StaticRectorStrings::camelCaseToUnderscore($propertyName);

        return strtoupper($constantName);
    }

    private function isLocalPropertyFetch(Node $node): bool
    {
        if ($node instanceof PropertyFetch) {
            return $this->isName($node->var, 'this');
        }

        if ($node instanceof StaticPropertyFetch) {
            return $this->isName($node->class, 'self');
        }

        return false;
    }
}
