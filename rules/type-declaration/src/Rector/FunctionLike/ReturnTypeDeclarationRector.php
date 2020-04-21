<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer;

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
    private const EXCLUDED_METHOD_NAMES = ['__construct', '__destruct', '__clone'];

    /**
     * @var string
     */
    private const DO_NOT_CHANGE = 'do_not_change';

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

    public function __construct(
        ReturnTypeInferer $returnTypeInferer,
        ReturnTypeAlreadyAddedChecker $returnTypeAlreadyAddedChecker,
        bool $overrideExistingReturnTypes = true
    ) {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->overrideExistingReturnTypes = $overrideExistingReturnTypes;
        $this->returnTypeAlreadyAddedChecker = $returnTypeAlreadyAddedChecker;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change @return types and type from static analysis to type declarations if not a BC-break',
            [
                new CodeSample(
                    <<<'PHP'
<?php

class SomeClass
{
    /**
     * @return int
     */
    public function getCount()
    {
    }
}
PHP
                    ,
                    <<<'PHP'
<?php

class SomeClass
{
    /**
     * @return int
     */
    public function getCount(): int
    {
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $inferedType = $this->returnTypeInferer->inferFunctionLikeWithExcludedInferers(
            $node,
            [ReturnTypeDeclarationReturnTypeInferer::class]
        );

        if ($inferedType instanceof MixedType) {
            return null;
        }

        if ($this->returnTypeAlreadyAddedChecker->isReturnTypeAlreadyAdded($node, $inferedType)) {
            return null;
        }

        $inferredReturnNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferedType);
        if ($this->shouldSkipInferredReturnNode($node, $inferredReturnNode)) {
            return null;
        }

        // should be previous overridden?
        if ($node->returnType !== null && $this->shouldSkipExistingReturnType($node, $inferedType)) {
            return null;
        }

        /** @var Name|NullableType|PhpParserUnionType $inferredReturnNode */
        $this->addReturnType($node, $inferredReturnNode);

        if ($node instanceof ClassMethod) {
            $this->populateChildren($node, $inferedType);
        }

        return $node;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function shouldSkip(FunctionLike $functionLike): bool
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return true;
        }

        if (! $this->overrideExistingReturnTypes && $functionLike->returnType !== null) {
            return true;
        }

        if (! $functionLike instanceof ClassMethod) {
            return false;
        }

        return $this->isNames($functionLike, self::EXCLUDED_METHOD_NAMES);
    }

    private function isVoidDueToThrow(Node $node, $inferredReturnNode): bool
    {
        if (! $inferredReturnNode instanceof Identifier) {
            return false;
        }

        if ($inferredReturnNode->name !== 'void') {
            return false;
        }

        return (bool) $this->betterNodeFinder->findFirstInstanceOf($node->stmts, Throw_::class);
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

        return $inferedType->isSubTypeOf($currentType)->yes();
    }

    /**
     * Add typehint to all children class methods
     */
    private function populateChildren(ClassMethod $classMethod, Type $returnType): void
    {
        $methodName = $this->getName($classMethod);
        if ($methodName === null) {
            throw new ShouldNotHappenException();
        }

        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if (! is_string($className)) {
            throw new ShouldNotHappenException();
        }

        $childrenClassLikes = $this->classLikeParsedNodesFinder->findChildrenOfClass($className);
        if ($childrenClassLikes === []) {
            return;
        }

        // update their methods as well
        foreach ($childrenClassLikes as $childClassLike) {
            $usedTraits = $this->classLikeParsedNodesFinder->findUsedTraitsInClass($childClassLike);
            foreach ($usedTraits as $trait) {
                $this->addReturnTypeToChildMethod($trait, $classMethod, $returnType);
            }

            $this->addReturnTypeToChildMethod($childClassLike, $classMethod, $returnType);
        }
    }

    private function addReturnTypeToChildMethod(
        ClassLike $classLike,
        ClassMethod $classMethod,
        Type $returnType
    ): void {
        $methodName = $this->getName($classMethod);

        $currentClassMethod = $classLike->getMethod($methodName);
        if ($currentClassMethod === null) {
            return;
        }

        $resolvedChildTypeNode = $this->resolveChildTypeNode($returnType);
        if ($resolvedChildTypeNode === null) {
            return;
        }

        $currentClassMethod->returnType = $resolvedChildTypeNode;

        // make sure the type is not overridden
        $currentClassMethod->returnType->setAttribute(self::DO_NOT_CHANGE, true);

        $this->notifyNodeFileInfo($currentClassMethod);
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function shouldSkipInferredReturnNode(FunctionLike $functionLike, ?Node $inferredReturnNode): bool
    {
        // nothing to change in PHP code - @todo add @var annotation fallback?
        if ($inferredReturnNode === null) {
            return true;
        }

        // prevent void overriding exception
        if ($this->isVoidDueToThrow($functionLike, $inferredReturnNode)) {
            return true;
        }
        // already overridden by previous populateChild() method run
        return $functionLike->returnType && $functionLike->returnType->getAttribute(self::DO_NOT_CHANGE);
    }

    private function shouldSkipExistingReturnType(Node $node, Type $inferedType): bool
    {
        $currentType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->returnType);

        if ($node instanceof ClassMethod && $this->vendorLockResolver->isReturnChangeVendorLockedIn($node)) {
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
        if ($functionLike->returnType !== null) {
            $isSubtype = $this->phpParserTypeAnalyzer->isSubtypeOf($inferredReturnNode, $functionLike->returnType);
            if ($this->isAtLeastPhpVersion(PhpVersionFeature::COVARIANT_RETURN) && $isSubtype) {
                $functionLike->returnType = $inferredReturnNode;
            } elseif (! $isSubtype) {
                // type override with correct one
                $functionLike->returnType = $inferredReturnNode;
            }
        } else {
            $functionLike->returnType = $inferredReturnNode;
        }
    }
}
