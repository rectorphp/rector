<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\NodeTypeResolver\TypeAnalyzer\CountableTypeAnalyzer;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;

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
     * @required
     */
    public function autowireTypeAnalyzerDependencies(
        NodeTypeResolver $nodeTypeResolver,
        ArrayTypeAnalyzer $arrayTypeAnalyzer,
        CountableTypeAnalyzer $countableTypeAnalyzer,
        StringTypeAnalyzer $stringTypeAnalyzer
    ): void {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
        $this->countableTypeAnalyzer = $countableTypeAnalyzer;
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
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
            /** @var Class_|null $class */
            $class = $node->getAttribute(AttributeKey::CLASS_NODE);
            if ($class === null) {
                return false;
            }

            return $this->isObjectType($class, $type);
        }

        return false;
    }
}
