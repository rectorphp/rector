<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Type\MixedType;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper;
final class ReturnStrictTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper
     */
    private $typeNodeUnwrapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(ReflectionResolver $reflectionResolver, TypeNodeUnwrapper $typeNodeUnwrapper, StaticTypeMapper $staticTypeMapper)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->typeNodeUnwrapper = $typeNodeUnwrapper;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * @param Return_[] $returns
     * @return array<Identifier|Name|NullableType>
     */
    public function collectStrictReturnTypes(array $returns, Scope $scope) : array
    {
        $containsStrictCall = \false;
        $returnedStrictTypeNodes = [];
        foreach ($returns as $return) {
            if (!$return->expr instanceof Expr) {
                return [];
            }
            $returnedExpr = $return->expr;
            if ($returnedExpr instanceof MethodCall || $returnedExpr instanceof StaticCall || $returnedExpr instanceof FuncCall) {
                $containsStrictCall = \true;
                $returnNode = $this->resolveMethodCallReturnNode($returnedExpr);
            } elseif ($returnedExpr instanceof ClassConstFetch) {
                $returnNode = $this->resolveConstFetchReturnNode($returnedExpr, $scope);
            } elseif ($returnedExpr instanceof Array_ || $returnedExpr instanceof String_ || $returnedExpr instanceof LNumber || $returnedExpr instanceof DNumber) {
                $returnNode = $this->resolveLiteralReturnNode($returnedExpr, $scope);
            } else {
                return [];
            }
            if (!$returnNode instanceof Node) {
                return [];
            }
            if ($returnNode instanceof Identifier && $returnNode->toString() === 'void') {
                return [];
            }
            $returnedStrictTypeNodes[] = $returnNode;
        }
        if (!$containsStrictCall) {
            return [];
        }
        return $this->typeNodeUnwrapper->uniquateNodes($returnedStrictTypeNodes);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $call
     */
    public function resolveMethodCallReturnNode($call) : ?Node
    {
        $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($call);
        if ($methodReflection === null) {
            return null;
        }
        $parametersAcceptor = $methodReflection->getVariants()[0];
        if ($parametersAcceptor instanceof FunctionVariantWithPhpDocs) {
            // native return type is needed, as docblock can be false
            $returnType = $parametersAcceptor->getNativeReturnType();
        } else {
            $returnType = $parametersAcceptor->getReturnType();
        }
        if ($returnType instanceof MixedType) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
    }
    /**
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Scalar $returnedExpr
     */
    private function resolveLiteralReturnNode($returnedExpr, Scope $scope) : ?Node
    {
        $returnType = $scope->getType($returnedExpr);
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
    }
    private function resolveConstFetchReturnNode(ClassConstFetch $classConstFetch, Scope $scope) : ?Node
    {
        $constType = $scope->getType($classConstFetch);
        if ($constType instanceof MixedType) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($constType, TypeKind::RETURN);
    }
}
