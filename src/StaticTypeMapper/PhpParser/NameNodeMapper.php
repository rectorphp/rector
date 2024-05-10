<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use Rector\Enum\ObjectReference;
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
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
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
        return new MixedType();
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
