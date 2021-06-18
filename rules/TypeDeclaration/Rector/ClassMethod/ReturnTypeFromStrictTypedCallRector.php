<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
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
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper;
use Rector\TypeDeclaration\Reflection\ReflectionTypeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\ReturnTypeFromStrictTypedCallRectorTest
 */
final class ReturnTypeFromStrictTypedCallRector extends AbstractRector
{
    public function __construct(
        private ReflectionTypeResolver $reflectionTypeResolver,
        private TypeNodeUnwrapper $typeNodeUnwrapper
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

        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf((array) $node->stmts, Return_::class);

        $returnedStrictTypes = $this->collectStrictReturnTypes($returns);
        if ($returnedStrictTypes === []) {
            return null;
        }

        if (count($returnedStrictTypes) === 1) {
            return $this->refactorSingleReturnType($returns[0], $returnedStrictTypes[0], $node);
        }

        if ($this->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
            /** @var PhpParserUnionType[] $returnedStrictTypes */
            $unwrappedTypes = $this->typeNodeUnwrapper->unwrapNullableUnionTypes($returnedStrictTypes);
            $returnType = new PhpParserUnionType($unwrappedTypes);
            $node->returnType = $returnType;
            return $node;
        }

        return null;
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    private function processSingleUnionType(
        Node $node,
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

    /**
     * @param Return_[] $returns
     * @return array<Identifier|Name|NullableType|PhpParserUnionType>
     */
    private function collectStrictReturnTypes(array $returns): array
    {
        $returnedStrictTypeNodes = [];

        foreach ($returns as $return) {
            if ($return->expr === null) {
                return [];
            }

            $returnedExpr = $return->expr;

            if ($returnedExpr instanceof MethodCall) {
                $returnNode = $this->resolveMethodCallReturnNode($returnedExpr);
            } elseif ($returnedExpr instanceof StaticCall) {
                $returnNode = $this->resolveStaticCallReturnNode($returnedExpr);
            } elseif ($returnedExpr instanceof FuncCall) {
                $returnNode = $this->resolveFuncCallReturnNode($returnedExpr);
            } else {
                return [];
            }

            if (! $returnNode instanceof Node) {
                return [];
            }

            $returnedStrictTypeNodes[] = $returnNode;
        }

        return $this->typeNodeUnwrapper->uniquateNodes($returnedStrictTypeNodes);
    }

    private function resolveMethodCallReturnNode(MethodCall $methodCall): ?Node
    {
        $classMethod = $this->nodeRepository->findClassMethodByMethodCall($methodCall);
        if ($classMethod instanceof ClassMethod) {
            return $classMethod->returnType;
        }

        $returnType = $this->reflectionTypeResolver->resolveMethodCallReturnType($methodCall);
        if (! $returnType instanceof Type) {
            return null;
        }

        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
    }

    private function resolveStaticCallReturnNode(StaticCall $staticCall): ?Node
    {
        $classMethod = $this->nodeRepository->findClassMethodByStaticCall($staticCall);
        if ($classMethod instanceof ClassMethod) {
            return $classMethod->returnType;
        }

        $returnType = $this->reflectionTypeResolver->resolveStaticCallReturnType($staticCall);
        if (! $returnType instanceof Type) {
            return null;
        }

        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
    }

    private function resolveFuncCallReturnNode(FuncCall $funcCall): Name | NullableType | PhpParserUnionType | null
    {
        $returnType = $this->reflectionTypeResolver->resolveFuncCallReturnType($funcCall);
        if (! $returnType instanceof Type) {
            return null;
        }

        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
    }

    private function refactorSingleReturnType(
        Return_ $return,
        Identifier | Name | NullableType | PhpParserUnionType $returnedStrictTypeNode,
        ClassMethod | Function_ | Closure $functionLike
    ): Closure | ClassMethod | Function_ {
        $resolvedType = $this->nodeTypeResolver->resolve($return);

        if ($resolvedType instanceof UnionType) {
            if (! $returnedStrictTypeNode instanceof NullableType) {
                throw new ShouldNotHappenException();
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
