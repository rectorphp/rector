<?php

declare (strict_types=1);
namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\NodeManipulator\PropertyManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class MatchPropertyTypeExpectedNameResolver
{
    /**
     * @readonly
     */
    private PropertyNaming $propertyNaming;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private PropertyManipulator $propertyManipulator;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
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
        // skip if already has suffix
        if (\substr_compare($propertyName, $expectedName->getName(), -\strlen($expectedName->getName())) === 0 || \substr_compare($propertyName, \ucfirst($expectedName->getName()), -\strlen(\ucfirst($expectedName->getName()))) === 0) {
            return null;
        }
        return $expectedName->getName();
    }
    private function resolveExpectedName(Property $property) : ?ExpectedName
    {
        // property type first
        if ($property->type instanceof Node) {
            $propertyType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($property->type);
            return $this->propertyNaming->getExpectedNameFromType($propertyType);
        }
        // fallback to docblock
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        $hasVarTag = $phpDocInfo instanceof PhpDocInfo && $phpDocInfo->getVarTagValueNode() instanceof VarTagValueNode;
        if ($hasVarTag) {
            return $this->propertyNaming->getExpectedNameFromType($phpDocInfo->getVarType());
        }
        return null;
    }
}
