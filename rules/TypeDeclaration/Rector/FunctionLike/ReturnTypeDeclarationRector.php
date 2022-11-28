<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Rector\TypeDeclaration\PhpDocParser\NonInformativeReturnTagRemover;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/scalar_type_hints_v5
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\ReturnTypeDeclarationRectorTest
 *
 * @deprecated Moving doc types to type declarations is dangerous. Use specific strict types instead.
 * This rule will be split info many small ones.
 */
final class ReturnTypeDeclarationRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PhpDocParser\NonInformativeReturnTagRemover
     */
    private $nonInformativeReturnTagRemover;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver
     */
    private $classMethodReturnVendorLockResolver;
    public function __construct(ReturnTypeInferer $returnTypeInferer, NonInformativeReturnTagRemover $nonInformativeReturnTagRemover, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->nonInformativeReturnTagRemover = $nonInformativeReturnTagRemover;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->classMethodReturnVendorLockResolver = $classMethodReturnVendorLockResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Function_::class, ClassMethod::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change @return types and type from static analysis to type declarations if not a BC-break', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return int
     */
    public function getCount()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function getCount(): int
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClassLike($node)) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->shouldSkipClassMethod($node)) {
            return null;
        }
        // skip already added types
        if ($node->returnType instanceof Node) {
            return null;
        }
        $inferedReturnType = $this->returnTypeInferer->inferFunctionLike($node);
        if ($inferedReturnType instanceof MixedType || $inferedReturnType instanceof NonExistingObjectType) {
            return null;
        }
        if (!$inferedReturnType instanceof UnionType) {
            return $this->processType($node, $inferedReturnType);
        }
        foreach ($inferedReturnType->getTypes() as $unionedType) {
            // mixed type cannot be joined with another types
            if ($unionedType instanceof MixedType) {
                return null;
            }
        }
        return $this->processType($node, $inferedReturnType);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function processType($node, Type $inferedType) : ?Node
    {
        $inferredReturnNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferedType, TypeKind::RETURN);
        // nothing to change in PHP code
        if (!$inferredReturnNode instanceof Node) {
            return null;
        }
        /** @var Name|NullableType|PhpParserUnionType|IntersectionType $inferredReturnNode */
        $node->returnType = $inferredReturnNode;
        $this->nonInformativeReturnTagRemover->removeReturnTagIfNotUseful($node);
        return $node;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
    {
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod)) {
            return \true;
        }
        return $this->classMethodReturnVendorLockResolver->isVendorLocked($classMethod);
    }
    private function shouldSkipClassLike(FunctionLike $functionLike) : bool
    {
        if (!$functionLike instanceof ClassMethod) {
            return \false;
        }
        $classLike = $this->betterNodeFinder->findParentType($functionLike, Class_::class);
        return !$classLike instanceof Class_;
    }
}
