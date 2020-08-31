<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Restoration\Tests\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\MakeTypedPropertyNullableIfCheckedRectorTest
 */
final class MakeTypedPropertyNullableIfCheckedRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Make typed property nullable if checked', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    private AnotherClass $anotherClass;

    public function run()
    {
        if ($this->anotherClass === null) {
            $this->anotherClass = new AnotherClass;
        }
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    private ?AnotherClass $anotherClass = null;

    public function run()
    {
        if ($this->anotherClass === null) {
            $this->anotherClass = new AnotherClass;
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
        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        /** @var PropertyProperty $onlyProperty */
        $onlyProperty = $node->props[0];

        $isPropretyNullChecked = $this->isPropertyNullChecked($onlyProperty);
        if (! $isPropretyNullChecked) {
            return null;
        }

        $currentPropertyType = $node->type;
        if ($currentPropertyType === null) {
            return null;
        }

        $node->type = new NullableType($currentPropertyType);
        $onlyProperty->default = $this->createNull();

        return $node;
    }

    private function shouldSkipProperty(Property $property): bool
    {
        if (count($property->props) !== 1) {
            return true;
        }

        if ($property->type === null) {
            return true;
        }

        return $property->type instanceof NullableType;
    }

    private function isPropertyNullChecked(PropertyProperty $onlyPropertyProperty): bool
    {
        $classLike = $onlyPropertyProperty->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        if ($this->isIdenticalOrNotIdenticalToNull($classLike, $onlyPropertyProperty)) {
            return true;
        }

        return $this->isBooleanNot($classLike, $onlyPropertyProperty);
    }

    private function isIdenticalOrNotIdenticalToNull(Class_ $class, PropertyProperty $onlyPropertyProperty): bool
    {
        $isIdenticalOrNotIdenticalToNull = false;

        $this->traverseNodesWithCallable((array) $class->stmts, function (Node $node) use (
            $onlyPropertyProperty,
            &$isIdenticalOrNotIdenticalToNull
        ): ?void {
            $matchedPropertyFetchName = $this->matchPropertyFetchNameComparedToNull($node);
            if ($matchedPropertyFetchName === null) {
                return null;
            }

            if (! $this->isName($onlyPropertyProperty, $matchedPropertyFetchName)) {
                return null;
            }

            $isIdenticalOrNotIdenticalToNull = true;
        });

        return $isIdenticalOrNotIdenticalToNull;
    }

    private function isBooleanNot(Class_ $class, PropertyProperty $onlyPropertyProperty): bool
    {
        $isBooleanNot = false;

        $this->traverseNodesWithCallable((array) $class->stmts, function (Node $node) use (
            $onlyPropertyProperty,
            &$isBooleanNot
        ): ?void {
            if (! $node instanceof BooleanNot) {
                return null;
            }

            if (! $node->expr instanceof PropertyFetch) {
                return null;
            }

            if (! $this->isName($node->expr->var, 'this')) {
                return null;
            }

            $propertyFetchName = $this->getName($node->expr->name);
            if (! $this->isName($onlyPropertyProperty, $propertyFetchName)) {
                return null;
            }

            $isBooleanNot = true;
        });

        return $isBooleanNot;
    }

    /**
     * Matches:
     * $this-><someProprety> === null
     * null === $this-><someProprety>
     */
    private function matchPropertyFetchNameComparedToNull(Node $node): ?string
    {
        if (! $node instanceof Identical && ! $node instanceof NotIdentical) {
            return null;
        }

        if ($node->left instanceof PropertyFetch && $this->isNull($node->right)) {
            $propertyFetch = $node->left;
        } elseif ($node->right instanceof PropertyFetch && $this->isNull($node->left)) {
            $propertyFetch = $node->right;
        } else {
            return null;
        }

        if (! $this->isName($propertyFetch->var, 'this')) {
            return null;
        }

        return $this->getName($propertyFetch->name);
    }
}
