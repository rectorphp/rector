<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\NodeTypeResolver\TypeAnalyzer\CountableTypeAnalyzer;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeTypeResolverTrait
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;

    /**
     * @var CountableTypeAnalyzer
     */
    private $countableTypeAnalyzer;

    /**
     * @var StringTypeAnalyzer
     */
    private $stringTypeAnalyzer;

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    /**
     * @required
     */
    public function autowireNodeTypeResolverTrait(
        NodeTypeResolver $nodeTypeResolver,
        ArrayTypeAnalyzer $arrayTypeAnalyzer,
        CountableTypeAnalyzer $countableTypeAnalyzer,
        StringTypeAnalyzer $stringTypeAnalyzer,
        TypeUnwrapper $typeUnwrapper
    ): void {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
        $this->countableTypeAnalyzer = $countableTypeAnalyzer;
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
        $this->typeUnwrapper = $typeUnwrapper;
    }

    public function isInObjectType(Node $node, string $type): bool
    {
        $objectType = $this->nodeTypeResolver->resolve($node);

        $desiredObjectType = new ObjectType($type);
        if ($objectType->isSuperTypeOf($desiredObjectType)->yes()) {
            return true;
        }

        return $objectType->equals($desiredObjectType);
    }

    public function isPropertyBoolean(Property $property): bool
    {
        return $this->nodeTypeResolver->isPropertyBoolean($property);
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
        foreach ($requiredTypes as $requiredType) {
            if ($this->isObjectType($node, $requiredType)) {
                return true;
            }
        }

        return false;
    }

    protected function isReturnOfObjectType(Return_ $return, string $objectType): bool
    {
        if ($return->expr === null) {
            return false;
        }

        $returnType = $this->getStaticType($return->expr);
        if (! $returnType instanceof TypeWithClassName) {
            return false;
        }

        return is_a($returnType->getClassName(), $objectType, true);
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

    protected function isStringOrUnionStringOnlyType(Node $node): bool
    {
        return $this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($node);
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

    protected function isNullableObjectType(Node $node): bool
    {
        return $this->nodeTypeResolver->isNullableObjectType($node);
    }

    protected function isCountableType(Node $node): bool
    {
        return $this->countableTypeAnalyzer->isCountableType($node);
    }

    protected function isArrayType(Node $node): bool
    {
        return $this->arrayTypeAnalyzer->isArrayType($node);
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
            if ($node->var instanceof Variable) {
                return $this->isObjectType($node->var, $type);
            }

            // method call is variable return
            return $this->isObjectType($node->var, $type);
        }

        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $type);
        }

        if ($node instanceof ClassMethod) {
            /** @var Class_|null $classLike */
            $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
            if ($classLike === null) {
                return false;
            }

            return $this->isObjectType($classLike, $type);
        }

        return false;
    }
}
