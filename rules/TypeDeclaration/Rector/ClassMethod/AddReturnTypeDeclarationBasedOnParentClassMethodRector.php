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
use Rector\NodeTypeResolver\Node\AttributeKey;
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
        return PhpVersionFeature::FATAL_ERROR_ON_INCOMPATIBLE_METHOD_SIGNATURE;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->nodeNameResolver->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }
        $parentMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($node);
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
        $parentClassMethodReturnType = $parentClassMethod->getReturnType();
        if ($parentClassMethodReturnType === null) {
            return null;
        }
        $parentClassMethodReturnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($parentClassMethodReturnType);
        return $this->processClassMethodReturnType($node, $parentClassMethodReturnType);
    }
    private function processClassMethodReturnType(ClassMethod $classMethod, Type $parentType) : ?ClassMethod
    {
        if ($parentType instanceof MixedType) {
            $class = $classMethod->getAttribute(AttributeKey::PARENT_NODE);
            if (!$class instanceof Class_) {
                return null;
            }
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
