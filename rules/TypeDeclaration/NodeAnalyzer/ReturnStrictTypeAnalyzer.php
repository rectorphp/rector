<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\MixedType;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class ReturnStrictTypeAnalyzer
{
    public function __construct(
        private ReflectionResolver $reflectionResolver,
        private TypeNodeUnwrapper $typeNodeUnwrapper,
        private StaticTypeMapper $staticTypeMapper
    ) {
    }

    /**
     * @param Return_[] $returns
     * @return array<Identifier|Name|NullableType>
     */
    public function collectStrictReturnTypes(array $returns): array
    {
        $returnedStrictTypeNodes = [];

        foreach ($returns as $return) {
            if ($return->expr === null) {
                return [];
            }

            $returnedExpr = $return->expr;

            if ($returnedExpr instanceof MethodCall || $returnedExpr instanceof StaticCall || $returnedExpr instanceof FuncCall) {
                $returnNode = $this->resolveMethodCallReturnNode($returnedExpr);
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

    private function resolveMethodCallReturnNode(MethodCall | StaticCall | FuncCall $call): ?Node
    {
        $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($call);
        if ($methodReflection === null) {
            return null;
        }

        $parametersAcceptor = $methodReflection->getVariants()[0];
        $returnType = $parametersAcceptor->getReturnType();
        if ($returnType instanceof MixedType) {
            return null;
        }

        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN());
    }
}
