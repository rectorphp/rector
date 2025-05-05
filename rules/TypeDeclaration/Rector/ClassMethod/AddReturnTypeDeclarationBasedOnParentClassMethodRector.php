<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector\AddReturnTypeDeclarationBasedOnParentClassMethodRectorTest
 */
final class AddReturnTypeDeclarationBasedOnParentClassMethodRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, PhpVersionProvider $phpVersionProvider, StaticTypeMapper $staticTypeMapper)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add missing return type declaration based on parent class method', [new CodeSample(<<<'CODE_SAMPLE'
class A
{
    public function execute(): int
    {
    }
}

class B extends A{
    public function execute()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class A
{
    public function execute(): int
    {
    }
}

class B extends A{
    public function execute(): int
    {
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
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($this->isNames($classMethod, [MethodName::CONSTRUCT, MethodName::DESCTRUCT])) {
                continue;
            }
            $parentClassMethodReturnType = $this->getReturnTypeRecursive($classMethod);
            if (!$parentClassMethodReturnType instanceof Type) {
                continue;
            }
            $changedClassMethod = $this->processClassMethodReturnType($node, $classMethod, $parentClassMethodReturnType);
            if (!$changedClassMethod instanceof ClassMethod) {
                continue;
            }
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function getReturnTypeRecursive(ClassMethod $classMethod) : ?Type
    {
        $returnType = $classMethod->getReturnType();
        if ($returnType instanceof Node) {
            return $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnType);
        }
        $parentMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($classMethod);
        while ($parentMethodReflection instanceof MethodReflection) {
            if ($parentMethodReflection->isPrivate()) {
                return null;
            }
            $parameterAcceptor = ParametersAcceptorSelector::combineAcceptors($parentMethodReflection->getVariants());
            $parentReturnType = $parameterAcceptor->getNativeReturnType();
            if (!$parentReturnType instanceof MixedType) {
                return $parentReturnType;
            }
            if ($parentReturnType->isExplicitMixed()) {
                return $parentReturnType;
            }
            $parentMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($parentMethodReflection);
        }
        return null;
    }
    private function processClassMethodReturnType(Class_ $class, ClassMethod $classMethod, Type $parentType) : ?ClassMethod
    {
        if ($parentType instanceof MixedType) {
            $className = (string) $this->getName($class);
            $currentObjectType = new ObjectType($className);
            if (!$parentType->equals($currentObjectType) && $classMethod->returnType instanceof Node) {
                return null;
            }
        }
        if ($parentType instanceof MixedType && !$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::MIXED_TYPE)) {
            return null;
        }
        // already set and sub type or equal → no change
        if ($this->parentClassMethodTypeOverrideGuard->shouldSkipReturnTypeChange($classMethod, $parentType)) {
            return null;
        }
        $classMethod->returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parentType, TypeKind::RETURN);
        return $classMethod;
    }
}
