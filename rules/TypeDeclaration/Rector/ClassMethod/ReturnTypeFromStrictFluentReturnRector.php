<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictFluentReturnRector\ReturnTypeFromStrictFluentReturnRectorTest
 */
final class ReturnTypeFromStrictFluentReturnRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReflectionResolver $reflectionResolver, ReturnTypeInferer $returnTypeInferer, PhpVersionProvider $phpVersionProvider)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->reflectionResolver = $reflectionResolver;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type from strict return $this', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return $this;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): self
    {
        return $this;
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
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::HAS_RETURN_TYPE;
    }
    /**
     * @param ClassMethod $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $returnType = $this->returnTypeInferer->inferFunctionLike($node);
        if ($returnType instanceof ObjectType && $returnType->getClassName() === $classReflection->getName()) {
            $node->returnType = new Name('self');
            return $node;
        }
        if (!$returnType instanceof ThisType) {
            return null;
        }
        if ($classReflection->isAnonymous() || $classReflection->isFinalByKeyword() || !$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::STATIC_RETURN_TYPE)) {
            $node->returnType = new Name('self');
        } else {
            $node->returnType = new Name('static');
        }
        return $node;
    }
}
