<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\TypeDeclaration\Guard\PropertyTypeOverrideGuard;
use Rector\TypeDeclaration\TypeAnalyzer\PropertyTypeDefaultValueAnalyzer;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\TrustedClassMethodPropertyTypeInferer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorReadonlyClassRector\TypedPropertyFromStrictConstructorReadonlyClassRectorTest
 */
final class TypedPropertyFromStrictConstructorReadonlyClassRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\TrustedClassMethodPropertyTypeInferer
     */
    private $trustedClassMethodPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\Guard\PropertyTypeOverrideGuard
     */
    private $propertyTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\PropertyTypeDefaultValueAnalyzer
     */
    private $propertyTypeDefaultValueAnalyzer;
    public function __construct(TrustedClassMethodPropertyTypeInferer $trustedClassMethodPropertyTypeInferer, VarTagRemover $varTagRemover, ConstructorAssignDetector $constructorAssignDetector, PropertyTypeOverrideGuard $propertyTypeOverrideGuard, ReflectionResolver $reflectionResolver, DoctrineTypeAnalyzer $doctrineTypeAnalyzer, PropertyTypeDefaultValueAnalyzer $propertyTypeDefaultValueAnalyzer)
    {
        $this->trustedClassMethodPropertyTypeInferer = $trustedClassMethodPropertyTypeInferer;
        $this->varTagRemover = $varTagRemover;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->propertyTypeOverrideGuard = $propertyTypeOverrideGuard;
        $this->reflectionResolver = $reflectionResolver;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->propertyTypeDefaultValueAnalyzer = $propertyTypeDefaultValueAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add typed public properties based only on strict constructor types in readonly classes', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @immutable
 */
class SomeObject
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @immutable
 */
class SomeObject
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod || $node->getProperties() === []) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if (!$this->propertyTypeOverrideGuard->isLegal($property, $classReflection)) {
                continue;
            }
            $propertyType = $this->trustedClassMethodPropertyTypeInferer->inferProperty($node, $property, $constructClassMethod);
            if ($this->shouldSkipProperty($property, $propertyType, $classReflection, $scope)) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
            if (!$propertyTypeNode instanceof Node) {
                continue;
            }
            $propertyProperty = $property->props[0];
            $propertyName = $this->nodeNameResolver->getName($property);
            if ($this->constructorAssignDetector->isPropertyAssigned($node, $propertyName)) {
                $propertyProperty->default = null;
                $hasChanged = \true;
            }
            if ($this->propertyTypeDefaultValueAnalyzer->doesConflictWithDefaultValue($propertyProperty, $propertyType)) {
                continue;
            }
            $property->type = $propertyTypeNode;
            $this->varTagRemover->removeVarTagIfUseless($phpDocInfo, $property);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    private function shouldSkipProperty(Property $property, Type $propertyType, ClassReflection $classReflection, Scope $scope) : bool
    {
        if (!$property->isPublic()) {
            return \true;
        }
        if ($propertyType instanceof MixedType) {
            return \true;
        }
        if ($this->doctrineTypeAnalyzer->isInstanceOfCollectionType($propertyType)) {
            return \true;
        }
        $isReadOnlyByPhpdoc = \false;
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($classReflection->hasProperty($propertyName)) {
            $propertyReflection = $classReflection->getProperty($propertyName, $scope);
            if ($propertyReflection instanceof PhpPropertyReflection) {
                $isReadOnlyByPhpdoc = $propertyReflection->isReadOnlyByPhpDoc();
            }
        }
        return !$isReadOnlyByPhpdoc;
    }
}
