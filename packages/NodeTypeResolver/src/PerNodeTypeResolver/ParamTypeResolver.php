<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver\ParamTypeResolverTest
 */
final class ParamTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        NameResolver $nameResolver,
        DocBlockManipulator $docBlockManipulator,
        CallableNodeTraverser $callableNodeTraverser
    ) {
        $this->nameResolver = $nameResolver;
        $this->docBlockManipulator = $docBlockManipulator;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @required
     */
    public function autowirePropertyTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Param::class];
    }

    /**
     * @param Param $node
     */
    public function resolve(Node $node): Type
    {
        if ($node->type !== null) {
            if (! $node->type instanceof Node\Identifier) {
                $resolveTypeName = $this->nameResolver->getName($node->type);
                if ($resolveTypeName) {
                    // @todo map the other way every type :)
                    return new ObjectType($resolveTypeName);
                }
            }
        }

        $resolvedType = $this->resolveParamStaticType($node);
        if (! $resolvedType instanceof MixedType) {
            return $resolvedType;
        }

        /** @var FunctionLike $parentNode */
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        return $this->resolveTypesFromFunctionDocBlock($node, $parentNode);
    }

    private function resolveTypesFromFunctionDocBlock(Param $param, FunctionLike $functionLike): Type
    {
        /** @var string $paramName */
        $paramName = $this->nameResolver->getName($param);

        return $this->docBlockManipulator->getParamTypeByName($functionLike, '$' . $paramName);
    }

    private function resolveParamStaticType(Param $param): Type
    {
        $classMethod = $param->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethod === null) {
            return new MixedType();
        }

        /** @var string $paramName */
        $paramName = $this->nameResolver->getName($param);
        $paramStaticType = new MixedType();

        // special case for param inside method/function
        $this->callableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node) use ($paramName, &$paramStaticType): ?int {
                if (! $node instanceof Variable) {
                    return null;
                }

                if (! $this->nameResolver->isName($node, $paramName)) {
                    return null;
                }

                $paramStaticType = $this->nodeTypeResolver->resolve($node);

                return NodeTraverser::STOP_TRAVERSAL;
            }
        );

        return $paramStaticType;
    }
}
