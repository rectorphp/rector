<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Enum\ObjectReference;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ParentObjectWithoutClassType;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
final class NameNodeMapper implements \Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface
{
    /**
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\Core\Configuration\RenamedClassesDataCollector $renamedClassesDataCollector, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return class-string<Node>
     */
    public function getNodeType() : string
    {
        return \PhpParser\Node\Name::class;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function mapToPHPStan($node) : \PHPStan\Type\Type
    {
        $name = $node->toString();
        if ($this->isExistingClass($name)) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($name);
        }
        if (\Rector\Core\Enum\ObjectReference::isValid($name)) {
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
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\ObjectWithoutClassType|\PHPStan\Type\StaticType
     */
    private function createClassReferenceType(\PhpParser\Node\Name $name, string $reference)
    {
        $className = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            return new \PHPStan\Type\MixedType();
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($reference === \Rector\Core\Enum\ObjectReference::STATIC()->getValue()) {
            return new \PHPStan\Type\StaticType($classReflection);
        }
        if ($reference === \Rector\Core\Enum\ObjectReference::PARENT()->getValue()) {
            $parentClassReflection = $classReflection->getParentClass();
            if ($parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
                return new \Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType($parentClassReflection);
            }
            return new \Rector\StaticTypeMapper\ValueObject\Type\ParentObjectWithoutClassType();
        }
        return new \PHPStan\Type\ThisType($classReflection);
    }
    /**
     * @return \PHPStan\Type\ArrayType|\PHPStan\Type\BooleanType|\PHPStan\Type\Constant\ConstantBooleanType|\PHPStan\Type\FloatType|\PHPStan\Type\IntegerType|\PHPStan\Type\MixedType|\PHPStan\Type\StringType
     */
    private function createScalarType(string $name)
    {
        if ($name === 'array') {
            return new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
        }
        if ($name === 'int') {
            return new \PHPStan\Type\IntegerType();
        }
        if ($name === 'float') {
            return new \PHPStan\Type\FloatType();
        }
        if ($name === 'string') {
            return new \PHPStan\Type\StringType();
        }
        if ($name === 'false') {
            return new \PHPStan\Type\Constant\ConstantBooleanType(\false);
        }
        if ($name === 'bool') {
            return new \PHPStan\Type\BooleanType();
        }
        return new \PHPStan\Type\MixedType();
    }
}
