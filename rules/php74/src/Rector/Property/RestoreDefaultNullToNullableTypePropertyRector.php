<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php74\Tests\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\RestoreDefaultNullToNullableTypePropertyRectorTest
 */
final class RestoreDefaultNullToNullableTypePropertyRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add null default to properties with PHP 7.4 property nullable type',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public ?string $name;
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public ?string $name = null;
}
CODE_SAMPLE
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

        $onlyProperty = $node->props[0];
        $onlyProperty->default = $this->nodeFactory->createNull();

        return $node;
    }

    private function shouldSkip(Property $property): bool
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return true;
        }

        if (! $property->type instanceof NullableType) {
            return true;
        }

        if (count($property->props) > 1) {
            return true;
        }

        $onlyProperty = $property->props[0];
        if ($onlyProperty->default !== null) {
            return true;
        }

        // is variable assigned in constructor
        $propertyName = $this->getName($property);

        return $this->isPropertyInitiatedInConstuctor($property, $propertyName);
    }

    private function isPropertyInitiatedInConstuctor(Property $property, string $propertyName): bool
    {
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        $constructClassMethod = $classLike->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
            return false;
        }

        $isPropertyInitiated = false;
        $this->traverseNodesWithCallable((array) $constructClassMethod->stmts, function (Node $node) use (
            $propertyName,
            &$isPropertyInitiated
        ): ?int {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->nodeNameResolver->isLocalPropertyFetchNamed($node->var, $propertyName)) {
                return null;
            }

            $isPropertyInitiated = true;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $isPropertyInitiated;
    }
}
