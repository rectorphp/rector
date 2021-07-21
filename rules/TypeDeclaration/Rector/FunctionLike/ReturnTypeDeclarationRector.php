<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\TypeDeclaration\ChildPopulator\ChildReturnPopulator;
use Rector\TypeDeclaration\PhpDocParser\NonInformativeReturnTagRemover;
use Rector\TypeDeclaration\PhpParserTypeAnalyzer;
use Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker;
use Rector\TypeDeclaration\TypeAnalyzer\ObjectTypeComparator;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer;
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
    public function __construct(
        private ReturnTypeInferer $returnTypeInferer,
        private ChildReturnPopulator $childReturnPopulator,
        private ReturnTypeAlreadyAddedChecker $returnTypeAlreadyAddedChecker,
        private NonInformativeReturnTagRemover $nonInformativeReturnTagRemover,
        private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard,
        private VendorLockResolver $vendorLockResolver,
        private PhpParserTypeAnalyzer $phpParserTypeAnalyzer,
        private ObjectTypeComparator $objectTypeComparator,
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change @return types and type from static analysis to type declarations if not a BC-break',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function getCount(): int
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipClassLike($node)) {
            return null;
        }

        if ($node instanceof ClassMethod && $this->shouldSkipClassMethod($node)) {
            return null;
        }

        $inferedReturnType = $this->returnTypeInferer->inferFunctionLikeWithExcludedInferers(
            $node,
            [ReturnTypeDeclarationReturnTypeInferer::class]
        );

        if ($inferedReturnType instanceof MixedType) {
            return null;
        }

        if ($this->returnTypeAlreadyAddedChecker->isSameOrBetterReturnTypeAlreadyAdded($node, $inferedReturnType)) {
            return null;
        }

        return $this->processType($node, $inferedReturnType);
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }

    private function processType(ClassMethod | Function_ $node, Type $inferedType): ?Node
    {
        $inferredReturnNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $inferedType,
            TypeKind::RETURN()
        );

        // nothing to change in PHP code
        if (! $inferredReturnNode instanceof Node) {
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

        if ($node instanceof ClassMethod) {
            $this->childReturnPopulator->populateChildren($node, $inferedType);
        }

        return $node;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod)) {
            return true;
        }

        return $this->vendorLockResolver->isReturnChangeVendorLockedIn($classMethod);
    }

    private function shouldSkipInferredReturnNode(ClassMethod | Function_ $functionLike): bool
    {
        // already overridden by previous populateChild() method run
        if ($functionLike->returnType === null) {
            return false;
        }

        return (bool) $functionLike->returnType->getAttribute(AttributeKey::DO_NOT_CHANGE);
    }

    private function shouldSkipExistingReturnType(ClassMethod | Function_ $functionLike, Type $inferedType): bool
    {
        if ($functionLike->returnType === null) {
            return false;
        }

        if ($functionLike instanceof ClassMethod && $this->vendorLockResolver->isReturnChangeVendorLockedIn(
            $functionLike
        )) {
            return true;
        }

        $currentType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        if ($this->objectTypeComparator->isCurrentObjectTypeSubType($currentType, $inferedType)) {
            return true;
        }

        return $this->isNullableTypeSubType($currentType, $inferedType);
    }

    private function addReturnType(
        ClassMethod | Function_ $functionLike,
        Name | NullableType | PhpParserUnionType $inferredReturnNode
    ): void {
        if ($functionLike->returnType === null) {
            $functionLike->returnType = $inferredReturnNode;
            return;
        }

        $isSubtype = $this->phpParserTypeAnalyzer->isCovariantSubtypeOf($inferredReturnNode, $functionLike->returnType);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::COVARIANT_RETURN) && $isSubtype) {
            $functionLike->returnType = $inferredReturnNode;
            return;
        }

        if (! $isSubtype) {
            // type override with correct one
            $functionLike->returnType = $inferredReturnNode;
        }
    }

    private function isNullableTypeSubType(Type $currentType, Type $inferedType): bool
    {
        if (! $currentType instanceof UnionType) {
            return false;
        }

        if (! $inferedType instanceof UnionType) {
            return false;
        }

        return $inferedType->isSubTypeOf($currentType)
            ->yes();
    }

    private function shouldSkipClassLike(FunctionLike $functionLike): bool
    {
        if (! $functionLike instanceof ClassMethod) {
            return false;
        }

        $classLike = $functionLike->getAttribute(AttributeKey::CLASS_NODE);
        return ! $classLike instanceof Class_;
    }
}
