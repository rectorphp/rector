<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\ChangesReporting\Rector\AbstractRector\NotifyingRemovingNodeTrait;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use Rector\PostRector\Rector\AbstractRector\NodeCommandersTrait;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

trait AbstractRectorTrait
{
    use RemovedAndAddedFilesTrait;
    use BetterStandardPrinterTrait;
    use NodeCommandersTrait;
    use ComplexRemovalTrait;
    use NotifyingRemovingNodeTrait;

    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @var TypeUnwrapper
     */
    protected $typeUnwrapper;

    /**
     * @var NodeTypeResolver
     */
    protected $nodeTypeResolver;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @required
     */
    public function autowireAbstractRectorTrait(
        NodeNameResolver $nodeNameResolver,
        ClassNaming $classNaming,
        NodeTypeResolver $nodeTypeResolver,
        TypeUnwrapper $typeUnwrapper,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ): void {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classNaming = $classNaming;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }

    protected function isName(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isName($node, $name);
    }

    protected function areNamesEqual(Node $firstNode, Node $secondNode): bool
    {
        return $this->nodeNameResolver->areNamesEqual($firstNode, $secondNode);
    }

    /**
     * @param string[] $names
     */
    protected function isNames(Node $node, array $names): bool
    {
        return $this->nodeNameResolver->isNames($node, $names);
    }

    protected function getName(Node $node): ?string
    {
        return $this->nodeNameResolver->getName($node);
    }

    /**
     * @param string|Name|Identifier|ClassLike $name
     */
    protected function getShortName($name): string
    {
        return $this->classNaming->getShortName($name);
    }

    protected function isLocalPropertyFetchNamed(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isLocalPropertyFetchNamed($node, $name);
    }

    protected function isLocalMethodCallNamed(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isLocalMethodCallNamed($node, $name);
    }

    /**
     * @param string[] $names
     */
    protected function isLocalMethodCallsNamed(Node $node, array $names): bool
    {
        return $this->nodeNameResolver->isLocalMethodCallsNamed($node, $names);
    }

    protected function isFuncCallName(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isFuncCallName($node, $name);
    }

    /**
     * Detects "SomeClass::class"
     */
    protected function isClassConstReference(Node $node, string $className): bool
    {
        if (! $node instanceof ClassConstFetch) {
            return false;
        }

        if (! $this->isName($node->name, 'class')) {
            return false;
        }

        return $this->isName($node->class, $className);
    }

    protected function isStaticCallNamed(Node $node, string $className, string $methodName): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        // handles (new Some())->...
        if ($node->class instanceof Expr) {
            if (! $this->isObjectType($node->class, $className)) {
                return false;
            }
        } elseif (! $this->isName($node->class, $className)) {
            return false;
        }

        return $this->isName($node->name, $methodName);
    }

    /**
     * @param string[] $names
     */
    protected function isFuncCallNames(Node $node, array $names): bool
    {
        return $this->nodeNameResolver->isFuncCallNames($node, $names);
    }

    /**
     * @param string[] $methodNames
     */
    protected function isStaticCallsNamed(Node $node, string $className, array $methodNames): bool
    {
        foreach ($methodNames as $methodName) {
            if ($this->isStaticCallNamed($node, $className, $methodName)) {
                return true;
            }
        }

        return false;
    }

    protected function isMethodCall(Node $node, string $variableName, string $methodName): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if ($node->var instanceof MethodCall) {
            return false;
        }

        if ($node->var instanceof StaticCall) {
            return false;
        }

        if (! $this->isName($node->var, $variableName)) {
            return false;
        }

        return $this->isName($node->name, $methodName);
    }

    protected function isVariableName(Node $node, string $name): bool
    {
        if (! $node instanceof Variable) {
            return false;
        }

        return $this->isName($node, $name);
    }

    protected function isInClassNamed(Node $node, string $desiredClassName): bool
    {
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        return is_a($className, $desiredClassName, true);
    }

    /**
     * @param string[] $desiredClassNames
     */
    protected function isInClassesNamed(Node $node, array $desiredClassNames): bool
    {
        foreach ($desiredClassNames as $desiredClassName) {
            if ($this->isInClassNamed($node, $desiredClassName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ObjectType|string $type
     */
    protected function isObjectType(Node $node, $type): bool
    {
        return $this->nodeTypeResolver->isObjectType($node, $type);
    }

    /**
     * @param string[]|ObjectType[] $requiredTypes
     */
    protected function isObjectTypes(Node $node, array $requiredTypes): bool
    {
        return $this->nodeTypeResolver->isObjectTypes($node, $requiredTypes);
    }

    /**
     * @param Type[] $desiredTypes
     */
    protected function isSameObjectTypes(ObjectType $objectType, array $desiredTypes): bool
    {
        foreach ($desiredTypes as $abstractClassConstructorParamType) {
            if ($abstractClassConstructorParamType->equals($objectType)) {
                return true;
            }
        }

        return false;
    }

    protected function isNumberType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNumberType($node);
    }

    protected function isStaticType(Node $node, string $staticTypeClass): bool
    {
        return $this->nodeTypeResolver->isStaticType($node, $staticTypeClass);
    }

    protected function getStaticType(Node $node): Type
    {
        return $this->nodeTypeResolver->getStaticType($node);
    }

    protected function isNullableType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNullableType($node);
    }

    protected function getObjectType(Node $node): Type
    {
        return $this->nodeTypeResolver->resolve($node);
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    protected function isMethodStaticCallOrClassMethodObjectType(Node $node, string $type): bool
    {
        if ($node instanceof MethodCall) {
            // method call is variable return
            return $this->isObjectType($node->var, $type);
        }

        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $type);
        }

        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        return $this->isObjectType($classLike, $type);
    }

    /**
     * @param Node|Node[] $nodes
     */
    protected function traverseNodesWithCallable($nodes, callable $callable): void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, $callable);
    }
}
