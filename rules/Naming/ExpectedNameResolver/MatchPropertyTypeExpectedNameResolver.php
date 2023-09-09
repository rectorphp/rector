<?php

declare (strict_types=1);
namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class MatchPropertyTypeExpectedNameResolver
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(PropertyNaming $propertyNaming, PhpDocInfoFactory $phpDocInfoFactory, NodeNameResolver $nodeNameResolver, PropertyManipulator $propertyManipulator, ReflectionResolver $reflectionResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->propertyNaming = $propertyNaming;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyManipulator = $propertyManipulator;
        $this->reflectionResolver = $reflectionResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function resolve(Property $property, ClassLike $classLike) : ?string
    {
        if (!$classLike instanceof Class_) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($property);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($this->propertyManipulator->isUsedByTrait($classReflection, $propertyName)) {
            return null;
        }
        $expectedName = $this->resolveExpectedName($property);
        if (!$expectedName instanceof ExpectedName) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        // skip if already has suffix
        if (\substr_compare($propertyName, $expectedName->getName(), -\strlen($expectedName->getName())) === 0 || \substr_compare($propertyName, \ucfirst($expectedName->getName()), -\strlen(\ucfirst($expectedName->getName()))) === 0) {
            return null;
        }
        return $expectedName->getName();
    }
    private function resolveExpectedName(Property $property) : ?ExpectedName
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        $isPhpDocInfo = $phpDocInfo instanceof PhpDocInfo;
        // property type first
        if ($property->type instanceof Node) {
            $propertyType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($property->type);
            // not has docblock, use property type
            if (!$isPhpDocInfo) {
                return $this->propertyNaming->getExpectedNameFromType($propertyType);
            }
            // @var type is ObjectType, use property type
            $varType = $phpDocInfo->getVarType();
            if ($varType instanceof ObjectType) {
                return $this->propertyNaming->getExpectedNameFromType($propertyType);
            }
        }
        // fallback to docblock
        if ($isPhpDocInfo) {
            return $this->propertyNaming->getExpectedNameFromType($phpDocInfo->getVarType());
        }
        return null;
    }
}
