<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\ChildPopulator\ChildReturnPopulator;
use Rector\TypeDeclaration\PhpDocParser\NonInformativeReturnTagRemover;
use Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\ReturnTypeDeclarationRectorTest
 */
final class ReturnTypeDeclarationRector extends AbstractTypeDeclarationRector
{
    /**
     * @var string[]
     */
    private const EXCLUDED_METHOD_NAMES = [MethodName::CONSTRUCT, MethodName::DESCTRUCT, MethodName::CLONE];

    /**
     * @var bool
     */
    private $overrideExistingReturnTypes = true;

    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    /**
     * @var ReturnTypeAlreadyAddedChecker
     */
    private $returnTypeAlreadyAddedChecker;

    /**
     * @var NonInformativeReturnTagRemover
     */
    private $nonInformativeReturnTagRemover;

    /**
     * @var ChildReturnPopulator
     */
    private $childReturnPopulator;

    /**
     * @var ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;

    public function __construct(
        ReturnTypeInferer $returnTypeInferer,
        ChildReturnPopulator $childReturnPopulator,
        ReturnTypeAlreadyAddedChecker $returnTypeAlreadyAddedChecker,
        NonInformativeReturnTagRemover $nonInformativeReturnTagRemover,
        ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard,
        bool $overrideExistingReturnTypes = true
    ) {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->overrideExistingReturnTypes = $overrideExistingReturnTypes;
        $this->returnTypeAlreadyAddedChecker = $returnTypeAlreadyAddedChecker;
        $this->nonInformativeReturnTagRemover = $nonInformativeReturnTagRemover;
        $this->childReturnPopulator = $childReturnPopulator;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
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
        if ($node instanceof ClassMethod && $this->shouldSkip($node)) {
            return null;
        }

        $inferedType = $this->returnTypeInferer->inferFunctionLikeWithExcludedInferers(
            $node,
            [ReturnTypeDeclarationReturnTypeInferer::class]
        );

        if ($inferedType instanceof MixedType) {
            return null;
        }

        if ($this->returnTypeAlreadyAddedChecker->isSameOrBetterReturnTypeAlreadyAdded($node, $inferedType)) {
            return null;
        }

        return $this->processType($node, $inferedType);
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    private function processType(Node $node, Type $inferedType): ?Node
    {
        $inferredReturnNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $inferedType,
            PHPStanStaticTypeMapper::KIND_RETURN
        );

        // nothing to change in PHP code
        if ($inferredReturnNode === null) {
            return null;
        }

        if ($this->shouldSkipInferredReturnNode($node, $inferredReturnNode)) {
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

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return true;
        }

        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod)) {
            return true;
        }

        if (! $this->overrideExistingReturnTypes && $classMethod->returnType !== null) {
            return true;
        }

        if ($this->isNames($classMethod, self::EXCLUDED_METHOD_NAMES)) {
            return true;
        }

        return $this->vendorLockResolver->isReturnChangeVendorLockedIn($classMethod);
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function shouldSkipInferredReturnNode(FunctionLike $functionLike, Node $inferredReturnNode): bool
    {
        // already overridden by previous populateChild() method run
        if ($functionLike->returnType === null) {
            return false;
        }
        return (bool) $functionLike->returnType->getAttribute(AttributeKey::DO_NOT_CHANGE);
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function shouldSkipExistingReturnType(FunctionLike $functionLike, Type $inferedType): bool
    {
        if ($functionLike->returnType === null) {
            return false;
        }

        $currentType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);

        if ($functionLike instanceof ClassMethod && $this->vendorLockResolver->isReturnChangeVendorLockedIn(
            $functionLike
        )) {
            return true;
        }

        if ($this->isCurrentObjectTypeSubType($currentType, $inferedType)) {
            return true;
        }

        return $this->isNullableTypeSubType($currentType, $inferedType);
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     * @param Name|NullableType|PhpParserUnionType $inferredReturnNode
     */
    private function addReturnType(FunctionLike $functionLike, Node $inferredReturnNode): void
    {
        if ($functionLike->returnType === null) {
            $functionLike->returnType = $inferredReturnNode;
            return;
        }

        $isSubtype = $this->phpParserTypeAnalyzer->isSubtypeOf($inferredReturnNode, $functionLike->returnType);
        if ($this->isAtLeastPhpVersion(PhpVersionFeature::COVARIANT_RETURN) && $isSubtype) {
            $functionLike->returnType = $inferredReturnNode;
            return;
        }

        if (! $isSubtype) {
            // type override with correct one
            $functionLike->returnType = $inferredReturnNode;
            return;
        }
    }

    /**
     * E.g. current E, new type A, E extends A → true
     */
    private function isCurrentObjectTypeSubType(Type $currentType, Type $inferedType): bool
    {
        if (! $currentType instanceof ObjectType) {
            return false;
        }

        if (! $inferedType instanceof ObjectType) {
            return false;
        }

        return is_a($currentType->getClassName(), $inferedType->getClassName(), true);
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
}
