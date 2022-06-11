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
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Rector\TypeDeclaration\PhpDocParser\NonInformativeReturnTagRemover;
use Rector\TypeDeclaration\PhpParserTypeAnalyzer;
use Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker;
use Rector\TypeDeclaration\TypeAnalyzer\ObjectTypeComparator;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInfererTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VendorLocker\VendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/scalar_type_hints_v5
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\ReturnTypeDeclarationRectorTest
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
     * @var \Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker
     */
    private $returnTypeAlreadyAddedChecker;
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
     * @var \Rector\VendorLocker\VendorLockResolver
     */
    private $vendorLockResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PhpParserTypeAnalyzer
     */
    private $phpParserTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\ObjectTypeComparator
     */
    private $objectTypeComparator;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(ReturnTypeInferer $returnTypeInferer, ReturnTypeAlreadyAddedChecker $returnTypeAlreadyAddedChecker, NonInformativeReturnTagRemover $nonInformativeReturnTagRemover, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, VendorLockResolver $vendorLockResolver, PhpParserTypeAnalyzer $phpParserTypeAnalyzer, ObjectTypeComparator $objectTypeComparator, PhpVersionProvider $phpVersionProvider)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->returnTypeAlreadyAddedChecker = $returnTypeAlreadyAddedChecker;
        $this->nonInformativeReturnTagRemover = $nonInformativeReturnTagRemover;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->vendorLockResolver = $vendorLockResolver;
        $this->phpParserTypeAnalyzer = $phpParserTypeAnalyzer;
        $this->objectTypeComparator = $objectTypeComparator;
        $this->phpVersionProvider = $phpVersionProvider;
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
        $inferedReturnType = $this->returnTypeInferer->inferFunctionLikeWithExcludedInferers($node, [ReturnTypeDeclarationReturnTypeInfererTypeInferer::class]);
        if ($inferedReturnType instanceof MixedType || $inferedReturnType instanceof NonExistingObjectType) {
            return null;
        }
        if ($this->returnTypeAlreadyAddedChecker->isSameOrBetterReturnTypeAlreadyAdded($node, $inferedReturnType)) {
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
        if ($this->shouldSkipInferredReturnNode($node)) {
            return null;
        }
        // should be previous overridden?
        if ($node->returnType !== null && $this->shouldSkipExistingReturnType($node, $inferedType)) {
            return null;
        }
        /** @var Name|NullableType|PhpParserUnionType $inferredReturnNode */
        $this->addReturnType($node, $inferredReturnNode);
        $this->nonInformativeReturnTagRemover->removeReturnTagIfNotUseful($node);
        return $node;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
    {
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod)) {
            return \true;
        }
        return $this->vendorLockResolver->isReturnChangeVendorLockedIn($classMethod);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function shouldSkipInferredReturnNode($functionLike) : bool
    {
        // already overridden by previous populateChild() method run
        if ($functionLike->returnType === null) {
            return \false;
        }
        return (bool) $functionLike->returnType->getAttribute(AttributeKey::DO_NOT_CHANGE);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function shouldSkipExistingReturnType($functionLike, Type $inferedType) : bool
    {
        if ($functionLike->returnType === null) {
            return \false;
        }
        if ($functionLike instanceof ClassMethod && $this->vendorLockResolver->isReturnChangeVendorLockedIn($functionLike)) {
            return \true;
        }
        $currentType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        if ($this->objectTypeComparator->isCurrentObjectTypeSubType($currentType, $inferedType)) {
            return \true;
        }
        return $this->isNullableTypeSubType($currentType, $inferedType);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @param \PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\IntersectionType $inferredReturnNode
     */
    private function addReturnType($functionLike, $inferredReturnNode) : void
    {
        if ($functionLike->returnType === null) {
            $functionLike->returnType = $inferredReturnNode;
            return;
        }
        $isSubtype = $this->phpParserTypeAnalyzer->isCovariantSubtypeOf($inferredReturnNode, $functionLike->returnType);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::COVARIANT_RETURN) && $isSubtype) {
            $functionLike->returnType = $inferredReturnNode;
            return;
        }
        if (!$isSubtype) {
            // type override with correct one
            $functionLike->returnType = $inferredReturnNode;
        }
    }
    private function isNullableTypeSubType(Type $currentType, Type $inferedType) : bool
    {
        if (!$currentType instanceof UnionType) {
            return \false;
        }
        if (!$inferedType instanceof UnionType) {
            return \false;
        }
        // probably more/less strict union type on purpose
        if ($currentType->isSubTypeOf($inferedType)->yes()) {
            return \true;
        }
        return $inferedType->isSubTypeOf($currentType)->yes();
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
