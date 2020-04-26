<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeCorrector\ParentClassesInterfacesAndUsedTraitsCorrector;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;

final class NodeTypeResolver
{
    /**
     * @var NodeTypeResolverInterface[]
     */
    private $nodeTypeResolvers = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;

    /**
     * @var ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;

    /**
     * @var ParentClassesInterfacesAndUsedTraitsCorrector
     */
    private $parentClassesInterfacesAndUsedTraitsCorrector;

    /**
     * @param NodeTypeResolverInterface[] $nodeTypeResolvers
     */
    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ObjectTypeSpecifier $objectTypeSpecifier,
        ParentClassesInterfacesAndUsedTraitsCorrector $parentClassesInterfacesAndUsedTraitsCorrector,
        array $nodeTypeResolvers
    ) {
        $this->nodeNameResolver = $nodeNameResolver;

        foreach ($nodeTypeResolvers as $nodeTypeResolver) {
            $this->addNodeTypeResolver($nodeTypeResolver);
        }

        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->parentClassesInterfacesAndUsedTraitsCorrector = $parentClassesInterfacesAndUsedTraitsCorrector;
    }

    /**
     * Prevents circular dependency
     * @required
     */
    public function autowireNodeTypeResolver(ArrayTypeAnalyzer $arrayTypeAnalyzer): void
    {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
    }

    /**
     * @param ObjectType|string|mixed $requiredType
     */
    public function isObjectType(Node $node, $requiredType): bool
    {
        $this->ensureRequiredTypeIsStringOrObjectType($requiredType, __METHOD__);

        if (is_string($requiredType) && Strings::contains($requiredType, '*')) {
            return $this->isFnMatch($node, $requiredType);
        }

        $resolvedType = $this->resolve($node);
        if ($resolvedType instanceof MixedType) {
            return false;
        }

        // this should also work with ObjectType and UnionType with ObjectType
        // use PHPStan types here

        if (is_string($requiredType)) {
            $requiredType = new ObjectType($requiredType);
        }

        if ($resolvedType->equals($requiredType)) {
            return true;
        }

        return $this->isMatchingUnionType($requiredType, $resolvedType);
    }

    public function resolve(Node $node): Type
    {
        $type = $this->resolveFirstType($node);
        if (! $type instanceof TypeWithClassName) {
            return $type;
        }

        return $this->parentClassesInterfacesAndUsedTraitsCorrector->correct($type);
    }

    /**
     * e.g. string|null, ObjectNull|null
     */
    public function isNullableType(Node $node): bool
    {
        $nodeType = $this->resolve($node);
        if (! $nodeType instanceof UnionType) {
            return false;
        }

        return $nodeType->isSuperTypeOf(new NullType())->yes();
    }

    /**
     * @deprecated
     * Use @see NodeTypeResolver::resolve() instead
     */
    public function getStaticType(Node $node): Type
    {
        if ($this->isArrayExpr($node)) {
            /** @var Expr $node */
            return $this->resolveArrayType($node);
        }

        if ($node instanceof Arg) {
            throw new ShouldNotHappenException('Arg does not have a type, use $arg->value instead');
        }

        if ($node instanceof Param || $node instanceof Scalar) {
            return $this->resolve($node);
        }

        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $node instanceof Expr || $nodeScope === null) {
            return new MixedType();
        }

        if ($node instanceof New_ && $this->isAnonymousClass($node->class)) {
            return new ObjectWithoutClassType();
        }

        $staticType = $nodeScope->getType($node);
        if (! $staticType instanceof ObjectType) {
            return $staticType;
        }

        return $this->objectTypeSpecifier->narrowToFullyQualifiedOrAlaisedObjectType($node, $staticType);
    }

    public function isNullableObjectType(Node $node): bool
    {
        $nodeType = $this->resolve($node);

        if (! $nodeType instanceof UnionType) {
            return false;
        }

        if ($nodeType->isSuperTypeOf(new NullType())->no()) {
            return false;
        }

        if (count($nodeType->getTypes()) !== 2) {
            return false;
        }

        foreach ($nodeType->getTypes() as $subtype) {
            if ($subtype instanceof ObjectType) {
                return true;
            }
        }

        return false;
    }

    public function isNumberType(Node $node): bool
    {
        return $this->isStaticType($node, IntegerType::class) || $this->isStaticType($node, FloatType::class);
    }

    public function isStaticType(Node $node, string $staticTypeClass): bool
    {
        if (! is_a($staticTypeClass, Type::class, true)) {
            throw new ShouldNotHappenException(sprintf(
                '"%s" in "%s()" must be type of "%s"',
                $staticTypeClass,
                __METHOD__,
                Type::class
            ));
        }

        return is_a($this->resolve($node), $staticTypeClass);
    }

    private function addNodeTypeResolver(NodeTypeResolverInterface $nodeTypeResolver): void
    {
        foreach ($nodeTypeResolver->getNodeClasses() as $nodeClass) {
            $this->nodeTypeResolvers[$nodeClass] = $nodeTypeResolver;
        }
    }

    private function ensureRequiredTypeIsStringOrObjectType($requiredType, string $location): void
    {
        if (is_string($requiredType)) {
            return;
        }

        if ($requiredType instanceof ObjectType) {
            return;
        }

        $reportedType = is_object($requiredType) ? get_class($requiredType) : $requiredType;

        throw new ShouldNotHappenException(sprintf(
            'Value passed to "%s()" must be string or "%s". "%s" given',
            $location,
            ObjectType::class,
            $reportedType
        ));
    }

    private function isFnMatch(Node $node, string $requiredType): bool
    {
        $objectType = $this->resolve($node);

        $classNames = TypeUtils::getDirectClassNames($objectType);
        foreach ($classNames as $className) {
            if (! fnmatch($requiredType, $className, FNM_NOESCAPE)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function isMatchingUnionType(Type $requiredType, Type $resolvedType): bool
    {
        if (! $resolvedType instanceof UnionType) {
            return false;
        }

        foreach ($resolvedType->getTypes() as $unionedType) {
            if (! $unionedType->equals($requiredType)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function resolveFirstType(Node $node): Type
    {
        foreach ($this->nodeTypeResolvers as $nodeTypeResolver) {
            foreach ($nodeTypeResolver->getNodeClasses() as $nodeClass) {
                if (! is_a($node, $nodeClass)) {
                    continue;
                }

                return $nodeTypeResolver->resolve($node);
            }
        }

        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope === null || ! $node instanceof Expr) {
            return new MixedType();
        }

        // skip anonymous classes, ref https://github.com/rectorphp/rector/issues/1574
        if ($node instanceof New_ && $this->isAnonymousClass($node->class)) {
            return new ObjectWithoutClassType();
        }

        $type = $nodeScope->getType($node);

        // hot fix for phpstan not resolving chain method calls
        if ($node instanceof MethodCall && $type instanceof MixedType) {
            return $this->resolveFirstType($node->var);
        }

        return $type;
    }

    private function isArrayExpr(Node $node): bool
    {
        return $node instanceof Expr && $this->arrayTypeAnalyzer->isArrayType($node);
    }

    private function resolveArrayType(Expr $expr): Type
    {
        /** @var Scope|null $scope */
        $scope = $expr->getAttribute(AttributeKey::SCOPE);

        if ($scope instanceof Scope) {
            $arrayType = $scope->getType($expr);

            if ($arrayType !== null) {
                return $arrayType;
            }
        }

        return new ArrayType(new MixedType(), new MixedType());
    }

    private function isAnonymousClass(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        $className = $this->nodeNameResolver->getName($node);

        return $className === null || Strings::contains($className, 'AnonymousClass');
    }
}
