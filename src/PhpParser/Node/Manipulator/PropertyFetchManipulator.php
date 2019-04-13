<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\ObjectType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;

/**
 * Read-only utils for PropertyFetch Node:
 * "$this->property"
 */
final class PropertyFetchManipulator
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        Broker $broker,
        NameResolver $nameResolver,
        ClassManipulator $classManipulator
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->broker = $broker;
        $this->nameResolver = $nameResolver;
        $this->classManipulator = $classManipulator;
    }

    public function isPropertyToSelf(PropertyFetch $propertyFetch): bool
    {
        if (! $this->nameResolver->isName($propertyFetch->var, 'this')) {
            return false;
        }

        /** @var Node\Stmt\Class_|null $class */
        $class = $propertyFetch->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return false;
        }

        return $this->classManipulator->hasPropertyFetchAsProperty($class, $propertyFetch);
    }

    public function isMagicOnType(Node $node, string $type): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $varNodeTypes = $this->nodeTypeResolver->resolve($node->var);
        if (! in_array($type, $varNodeTypes, true)) {
            return false;
        }

        $nodeName = $this->nameResolver->resolve($node);
        if ($nodeName === null) {
            return false;
        }

        return ! $this->hasPublicProperty($node, $nodeName);
    }

    private function hasPublicProperty(PropertyFetch $propertyFetch, string $propertyName): bool
    {
        $nodeScope = $propertyFetch->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope === null) {
            throw new ShouldNotHappenException();
        }

        $propertyFetchType = $nodeScope->getType($propertyFetch->var);
        if ($propertyFetchType instanceof ObjectType) {
            $propertyFetchType = $propertyFetchType->getClassName();
        }

        if (! is_string($propertyFetchType)) {
            return false;
        }

        if (! $this->broker->hasClass($propertyFetchType)) {
            return false;
        }

        $classReflection = $this->broker->getClass($propertyFetchType);
        if (! $classReflection->hasProperty($propertyName)) {
            return false;
        }

        $propertyReflection = $classReflection->getProperty($propertyName, $nodeScope);

        return $propertyReflection->isPublic();
    }
}
