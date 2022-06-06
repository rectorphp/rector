<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\StaticTypeMapper\PhpParser;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantBooleanType;
use RectorPrefix20220606\PHPStan\Type\FloatType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\ObjectWithoutClassType;
use RectorPrefix20220606\PHPStan\Type\StaticType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\ThisType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Configuration\RenamedClassesDataCollector;
use RectorPrefix20220606\Rector\Core\Enum\ObjectReference;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\ParentObjectWithoutClassType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
/**
 * @implements PhpParserNodeMapperInterface<Name>
 */
final class NameNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector, ReflectionProvider $reflectionProvider, BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->reflectionProvider = $reflectionProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
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
        if ($this->isExistingClass($name)) {
            return new FullyQualifiedObjectType($name);
        }
        if (\in_array($name, [ObjectReference::STATIC, ObjectReference::SELF, ObjectReference::PARENT], \true)) {
            return $this->createClassReferenceType($node, $name);
        }
        return $this->createScalarType($name);
    }
    private function isExistingClass(string $name) : bool
    {
        if ($this->reflectionProvider->hasClass($name)) {
            return \true;
        }
        // to be existing class names
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        return \in_array($name, $oldToNewClasses, \true);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\StaticType|\PHPStan\Type\ObjectWithoutClassType
     */
    private function createClassReferenceType(Name $name, string $reference)
    {
        $classLike = $this->betterNodeFinder->findParentType($name, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return new MixedType();
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($reference === ObjectReference::STATIC) {
            return new StaticType($classReflection);
        }
        if ($reference === ObjectReference::PARENT) {
            $parentClassReflection = $classReflection->getParentClass();
            if ($parentClassReflection instanceof ClassReflection) {
                return new ParentStaticType($parentClassReflection);
            }
            return new ParentObjectWithoutClassType();
        }
        return new ThisType($classReflection);
    }
    /**
     * @return \PHPStan\Type\ArrayType|\PHPStan\Type\IntegerType|\PHPStan\Type\FloatType|\PHPStan\Type\StringType|\PHPStan\Type\Constant\ConstantBooleanType|\PHPStan\Type\BooleanType|\PHPStan\Type\MixedType
     */
    private function createScalarType(string $name)
    {
        if ($name === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }
        if ($name === 'int') {
            return new IntegerType();
        }
        if ($name === 'float') {
            return new FloatType();
        }
        if ($name === 'string') {
            return new StringType();
        }
        if ($name === 'false') {
            return new ConstantBooleanType(\false);
        }
        if ($name === 'bool') {
            return new BooleanType();
        }
        return new MixedType();
    }
}
