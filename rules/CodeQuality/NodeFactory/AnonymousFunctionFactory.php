<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\VoidType;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class AnonymousFunctionFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * @param Variable|PropertyFetch $expr
     */
    public function create(\PHPStan\Reflection\Php\PhpMethodReflection $phpMethodReflection, \PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr\Closure
    {
        /** @var FunctionVariantWithPhpDocs $functionVariantWithPhpDoc */
        $functionVariantWithPhpDoc = $phpMethodReflection->getVariants()[0];
        $anonymousFunction = new \PhpParser\Node\Expr\Closure();
        $newParams = $this->createParams($functionVariantWithPhpDoc->getParameters());
        $anonymousFunction->params = $newParams;
        $innerMethodCall = new \PhpParser\Node\Expr\MethodCall($expr, $phpMethodReflection->getName());
        $innerMethodCall->args = $this->nodeFactory->createArgsFromParams($newParams);
        if (!$functionVariantWithPhpDoc->getReturnType() instanceof \PHPStan\Type\MixedType) {
            $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($functionVariantWithPhpDoc->getReturnType());
            $anonymousFunction->returnType = $returnType;
        }
        // does method return something?
        if (!$functionVariantWithPhpDoc->getReturnType() instanceof \PHPStan\Type\VoidType) {
            $anonymousFunction->stmts[] = new \PhpParser\Node\Stmt\Return_($innerMethodCall);
        } else {
            $anonymousFunction->stmts[] = new \PhpParser\Node\Stmt\Expression($innerMethodCall);
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable && !$this->nodeNameResolver->isName($expr, 'this')) {
            $anonymousFunction->uses[] = new \PhpParser\Node\Expr\ClosureUse($expr);
        }
        return $anonymousFunction;
    }
    /**
     * @param ParameterReflection[] $parameterReflections
     * @return Param[]
     */
    private function createParams(array $parameterReflections) : array
    {
        $params = [];
        foreach ($parameterReflections as $parameterReflection) {
            $param = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable($parameterReflection->getName()));
            if (!$parameterReflection->getType() instanceof \PHPStan\Type\MixedType) {
                $paramType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parameterReflection->getType());
                $param->type = $paramType;
            }
            $params[] = $param;
        }
        return $params;
    }
}
