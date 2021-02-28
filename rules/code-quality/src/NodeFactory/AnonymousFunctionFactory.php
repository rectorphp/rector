<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node;
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

    public function __construct(
        NodeFactory $nodeFactory,
        NodeNameResolver $nodeNameResolver,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    /**
     * @param Variable|PropertyFetch $node
     */
    public function create(PhpMethodReflection $phpMethodReflection, Node $node): Closure
    {
        /** @var FunctionVariantWithPhpDocs $functionVariantWithPhpDoc */
        $functionVariantWithPhpDoc = $phpMethodReflection->getVariants()[0];

        $anonymousFunction = new Closure();
        $newParams = $this->createParams($functionVariantWithPhpDoc->getParameters());

        $anonymousFunction->params = $newParams;

        $innerMethodCall = new MethodCall($node, $phpMethodReflection->getName());
        $innerMethodCall->args = $this->nodeFactory->createArgsFromParams($newParams);

        if (! $functionVariantWithPhpDoc->getReturnType() instanceof MixedType) {
            $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
                $functionVariantWithPhpDoc->getReturnType()
            );
            $anonymousFunction->returnType = $returnType;
        }

        // does method return something?

        if (! $functionVariantWithPhpDoc->getReturnType() instanceof VoidType) {
            $anonymousFunction->stmts[] = new Return_($innerMethodCall);
        } else {
            $anonymousFunction->stmts[] = new Expression($innerMethodCall);
        }

        if ($node instanceof Variable && ! $this->nodeNameResolver->isName($node, 'this')) {
            $anonymousFunction->uses[] = new ClosureUse($node);
        }

        return $anonymousFunction;
    }

    /**
     * @param ParameterReflection[] $parameterReflections
     * @return Param[]
     */
    private function createParams(array $parameterReflections): array
    {
        $params = [];
        foreach ($parameterReflections as $parameterReflection) {
            $param = new Param(new Variable($parameterReflection->getName()));

            if (! $parameterReflection->getType() instanceof MixedType) {
                $paramType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parameterReflection->getType());
                $param->type = $paramType;
            }

            $params[] = $param;
        }

        return $params;
    }
}
