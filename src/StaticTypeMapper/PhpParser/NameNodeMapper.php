<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use Rector\Enum\ObjectReference;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\ParentObjectWithoutClassType;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType;
/**
 * @implements PhpParserNodeMapperInterface<Name>
 */
final class NameNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper
     */
    private $fullyQualifiedNodeMapper;
    public function __construct(ReflectionResolver $reflectionResolver, \Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper $fullyQualifiedNodeMapper)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->fullyQualifiedNodeMapper = $fullyQualifiedNodeMapper;
    }
    public function getNodeType() : string
    {
        return Name::class;
    }
    /**
     * @param Name $node
     */
    public function mapToPHPStan(Node $node) : Type
    {
        $name = $node->toString();
        if ($node->isSpecialClassName()) {
            return $this->createClassReferenceType($node, $name);
        }
        $expandedNamespacedName = $this->expandedNamespacedName($node);
        if ($expandedNamespacedName instanceof FullyQualified) {
            return $this->fullyQualifiedNodeMapper->mapToPHPStan($expandedNamespacedName);
        }
        return new MixedType();
    }
    private function expandedNamespacedName(Name $name) : ?FullyQualified
    {
        if (\get_class($name) !== Name::class) {
            return null;
        }
        if (!$name->hasAttribute(AttributeKey::NAMESPACED_NAME)) {
            return null;
        }
        return new FullyQualified($name->getAttribute(AttributeKey::NAMESPACED_NAME));
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\StaticType|\Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType|\PHPStan\Type\ObjectWithoutClassType
     */
    private function createClassReferenceType(Name $name, string $reference)
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($name);
        if (!$classReflection instanceof ClassReflection) {
            return new MixedType();
        }
        if ($reference === ObjectReference::STATIC) {
            return new StaticType($classReflection);
        }
        if ($reference === ObjectReference::SELF) {
            return new SelfStaticType($classReflection);
        }
        $parentClassReflection = $classReflection->getParentClass();
        if ($parentClassReflection instanceof ClassReflection) {
            return new ParentStaticType($parentClassReflection);
        }
        return new ParentObjectWithoutClassType();
    }
}
