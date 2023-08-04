<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/lsp_errors
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector\AddReturnTypeDeclarationBasedOnParentClassMethodRectorTest
 */
final class AddReturnTypeDeclarationBasedOnParentClassMethodRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, AstResolver $astResolver, PhpVersionProvider $phpVersionProvider)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->astResolver = $astResolver;
        $this->phpVersionProvider = $phpVersionProvider;
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
            if ($this->isName($classMethod, MethodName::CONSTRUCT)) {
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
        if ($returnType === null) {
            $parentMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($classMethod);
            if (!$parentMethodReflection instanceof MethodReflection) {
                return null;
            }
            $parentClassMethod = $this->astResolver->resolveClassMethodFromMethodReflection($parentMethodReflection);
            if (!$parentClassMethod instanceof ClassMethod) {
                return null;
            }
            if ($parentClassMethod->isPrivate()) {
                return null;
            }
            return $this->getReturnTypeRecursive($parentClassMethod);
        }
        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnType);
    }
    private function processClassMethodReturnType(Class_ $class, ClassMethod $classMethod, Type $parentType) : ?ClassMethod
    {
        if ($parentType instanceof MixedType) {
            $className = (string) $this->nodeNameResolver->getName($class);
            $currentObjectType = new ObjectType($className);
            if (!$parentType->equals($currentObjectType) && $classMethod->returnType !== null) {
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
