<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnStrictTypeAnalyzer;
use Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\ReturnTypeFromStrictTypedCallRectorTest
 */
final class ReturnTypeFromStrictTypedCallRector extends AbstractRector
{
    public function __construct(
        private TypeNodeUnwrapper $typeNodeUnwrapper,
        private ReturnStrictTypeAnalyzer $returnStrictTypeAnalyzer,
        private ReturnTypeInferer $returnTypeInferer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add return type from strict return type of call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getData()
    {
        return $this->getNumber();
    }

    private function getNumber(): int
    {
        return 1000;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getData(): int
    {
        return $this->getNumber();
    }

    private function getNumber(): int
    {
        return 1000;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isSkipped($node)) {
            return null;
        }

        if ($this->isUnionPossibleReturnsVoid($node)) {
            return null;
        }

        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->find((array) $node->stmts, function (Node $n) use ($node): bool {
            $currentFunctionLike = $this->betterNodeFinder->findParentType($n, FunctionLike::class);

            if ($currentFunctionLike === $node) {
                return $n instanceof Return_;
            }

            $currentReturn = $this->betterNodeFinder->findParentType($n, Return_::class);
            if (! $currentReturn instanceof Return_) {
                return false;
            }

            $currentFunctionLike = $this->betterNodeFinder->findParentType($currentReturn, FunctionLike::class);
            if ($currentFunctionLike !== $node) {
                return false;
            }

            return $n instanceof Return_;
        });

        $returnedStrictTypes = $this->returnStrictTypeAnalyzer->collectStrictReturnTypes($returns);
        if ($returnedStrictTypes === []) {
            return null;
        }

        if (count($returnedStrictTypes) === 1) {
            return $this->refactorSingleReturnType($returns[0], $returnedStrictTypes[0], $node);
        }

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
            /** @var PhpParserUnionType[] $returnedStrictTypes */
            $unwrappedTypes = $this->typeNodeUnwrapper->unwrapNullableUnionTypes($returnedStrictTypes);
            $returnType = new PhpParserUnionType($unwrappedTypes);
            $node->returnType = $returnType;
            return $node;
        }

        return null;
    }

    private function isUnionPossibleReturnsVoid(ClassMethod | Function_ | Closure $node): bool
    {
        $inferReturnType = $this->returnTypeInferer->inferFunctionLike($node);
        if ($inferReturnType instanceof UnionType) {
            foreach ($inferReturnType->getTypes() as $type) {
                if ($type instanceof VoidType) {
                    return true;
                }
            }
        }

        return false;
    }

    private function processSingleUnionType(
        ClassMethod | Function_ | Closure $node,
        UnionType $unionType,
        NullableType $nullableType
    ): Closure | ClassMethod | Function_ {
        $types = $unionType->getTypes();
        $returnType = $types[0] instanceof ObjectType && $types[1] instanceof NullType
            ? new NullableType(new FullyQualified($types[0]->getClassName()))
            : $nullableType;

        $node->returnType = $returnType;
        return $node;
    }

    private function isSkipped(ClassMethod | Function_ | Closure $node): bool
    {
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return true;
        }

        if ($node->returnType !== null) {
            return true;
        }

        return $node instanceof ClassMethod && $node->isMagic();
    }

    private function refactorSingleReturnType(
        Return_ $return,
        Identifier | Name | NullableType | PhpParserUnionType $returnedStrictTypeNode,
        ClassMethod | Function_ | Closure $functionLike
    ): Closure | ClassMethod | Function_ {
        $resolvedType = $this->nodeTypeResolver->resolve($return);

        if ($resolvedType instanceof UnionType) {
            if (! $returnedStrictTypeNode instanceof NullableType) {
                return $functionLike;
            }

            return $this->processSingleUnionType($functionLike, $resolvedType, $returnedStrictTypeNode);
        }

        /** @var Name $returnType */
        $returnType = $resolvedType instanceof ObjectType
            ? new FullyQualified($resolvedType->getClassName())
            : $returnedStrictTypeNode;

        $functionLike->returnType = $returnType;

        return $functionLike;
    }
}
