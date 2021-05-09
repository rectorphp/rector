<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
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
final class ReturnTypeFromStrictTypedCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ReflectionTypeResolver
     */
    private $reflectionTypeResolver;
    /**
     * @var TypeNodeUnwrapper
     */
    private $typeNodeUnwrapper;
    public function __construct(\Rector\TypeDeclaration\Reflection\ReflectionTypeResolver $reflectionTypeResolver, \Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper $typeNodeUnwrapper)
    {
        $this->reflectionTypeResolver = $reflectionTypeResolver;
        $this->typeNodeUnwrapper = $typeNodeUnwrapper;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add return type from strict return type of call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->isSkipped($node)) {
            return null;
        }
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf((array) $node->stmts, \PhpParser\Node\Stmt\Return_::class);
        $returnedStrictTypes = $this->collectStrictReturnTypes($returns);
        if ($returnedStrictTypes === []) {
            return null;
        }
        if (\count($returnedStrictTypes) === 1) {
            return $this->refactorSingleReturnType($returns[0], $returnedStrictTypes[0], $node);
        }
        if ($this->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::UNION_TYPES)) {
            /** @var PhpParserUnionType[] $returnedStrictTypes */
            $unwrappedTypes = $this->typeNodeUnwrapper->unwrapNullableUnionTypes($returnedStrictTypes);
            $returnType = new \PhpParser\Node\UnionType($unwrappedTypes);
            $node->returnType = $returnType;
            return $node;
        }
        return null;
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    private function processSingleUnionType(\PhpParser\Node $node, \PHPStan\Type\UnionType $unionType, \PhpParser\Node\NullableType $nullableType) : \PhpParser\Node\FunctionLike
    {
        $types = $unionType->getTypes();
        $returnType = $types[0] instanceof \PHPStan\Type\ObjectType && $types[1] instanceof \PHPStan\Type\NullType ? new \PhpParser\Node\NullableType(new \PhpParser\Node\Name\FullyQualified($types[0]->getClassName())) : $nullableType;
        $node->returnType = $returnType;
        return $node;
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    private function isSkipped(\PhpParser\Node $node) : bool
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::SCALAR_TYPES)) {
            return \true;
        }
        if ($node->returnType !== null) {
            return \true;
        }
        return $node instanceof \PhpParser\Node\Stmt\ClassMethod && $node->isMagic();
    }
    /**
     * @param Return_[] $returns
     * @return array<Name|NullableType|UnionType>
     */
    private function collectStrictReturnTypes(array $returns) : array
    {
        $returnedStrictTypeNodes = [];
        foreach ($returns as $return) {
            if ($return->expr === null) {
                return [];
            }
            $returnedExpr = $return->expr;
            if ($returnedExpr instanceof \PhpParser\Node\Expr\MethodCall) {
                $returnNode = $this->resolveMethodCallReturnNode($returnedExpr);
            } elseif ($returnedExpr instanceof \PhpParser\Node\Expr\StaticCall) {
                $returnNode = $this->resolveStaticCallReturnNode($returnedExpr);
            } elseif ($returnedExpr instanceof \PhpParser\Node\Expr\FuncCall) {
                $returnNode = $this->resolveFuncCallReturnNode($returnedExpr);
            } else {
                return [];
            }
            if (!$returnNode instanceof \PhpParser\Node) {
                return [];
            }
            $returnedStrictTypeNodes[] = $returnNode;
        }
        return $this->typeNodeUnwrapper->uniquateNodes($returnedStrictTypeNodes);
    }
    private function resolveMethodCallReturnNode(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        $classMethod = $this->nodeRepository->findClassMethodByMethodCall($methodCall);
        if ($classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $classMethod->returnType;
        }
        $returnType = $this->reflectionTypeResolver->resolveMethodCallReturnType($methodCall);
        if (!$returnType instanceof \PHPStan\Type\Type) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
    }
    private function resolveStaticCallReturnNode(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PhpParser\Node
    {
        $classMethod = $this->nodeRepository->findClassMethodByStaticCall($staticCall);
        if ($classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $classMethod->returnType;
        }
        $returnType = $this->reflectionTypeResolver->resolveStaticCallReturnType($staticCall);
        if (!$returnType instanceof \PHPStan\Type\Type) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
    }
    /**
     * @return Name|NullableType|PhpParserUnionType|null
     */
    private function resolveFuncCallReturnNode(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node
    {
        $returnType = $this->reflectionTypeResolver->resolveFuncCallReturnType($funcCall);
        if (!$returnType instanceof \PHPStan\Type\Type) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
    }
    /**
     * @param ClassMethod|Function_|Closure $functionLike
     * @param Name|NullableType|PhpParserUnionType $returnedStrictTypeNode
     */
    private function refactorSingleReturnType(\PhpParser\Node\Stmt\Return_ $return, \PhpParser\Node $returnedStrictTypeNode, \PhpParser\Node\FunctionLike $functionLike) : \PhpParser\Node\FunctionLike
    {
        $resolvedType = $this->nodeTypeResolver->resolve($return);
        if ($resolvedType instanceof \PHPStan\Type\UnionType) {
            if (!$returnedStrictTypeNode instanceof \PhpParser\Node\NullableType) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            return $this->processSingleUnionType($functionLike, $resolvedType, $returnedStrictTypeNode);
        }
        /** @var Name $returnType */
        $returnType = $resolvedType instanceof \PHPStan\Type\ObjectType ? new \PhpParser\Node\Name\FullyQualified($resolvedType->getClassName()) : $returnedStrictTypeNode;
        $functionLike->returnType = $returnType;
        return $functionLike;
    }
}
