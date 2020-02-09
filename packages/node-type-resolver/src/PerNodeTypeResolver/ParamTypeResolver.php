<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver\ParamTypeResolverTest
 */
final class ParamTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeNameResolver $nodeNameResolver, CallableNodeTraverser $callableNodeTraverser)
    {
        $this->nodeNameResolver = $nodeNameResolver;
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
        $paramType = $this->resolveFromType($node);
        if (! $paramType instanceof MixedType) {
            return $paramType;
        }

        $firstVariableUseType = $this->resolveFromFirstVariableUse($node);
        if (! $firstVariableUseType instanceof MixedType) {
            return $firstVariableUseType;
        }

        return $this->resolveFromFunctionDocBlock($node);
    }

    private function resolveFromType(Node $node)
    {
        if ($node->type !== null && ! $node->type instanceof Identifier) {
            $resolveTypeName = $this->nodeNameResolver->getName($node->type);
            if ($resolveTypeName) {
                // @todo map the other way every type :)
                return new ObjectType($resolveTypeName);
            }
        }

        return new MixedType();
    }

    private function resolveFromFirstVariableUse(Param $param): Type
    {
        $classMethod = $param->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethod === null) {
            return new MixedType();
        }

        /** @var string $paramName */
        $paramName = $this->nodeNameResolver->getName($param);
        $paramStaticType = new MixedType();

        // special case for param inside method/function
        $this->callableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node) use ($paramName, &$paramStaticType): ?int {
                if (! $node instanceof Variable) {
                    return null;
                }

                if (! $this->nodeNameResolver->isName($node, $paramName)) {
                    return null;
                }

                $paramStaticType = $this->nodeTypeResolver->resolve($node);

                return NodeTraverser::STOP_TRAVERSAL;
            }
        );

        return $paramStaticType;
    }

    private function resolveFromFunctionDocBlock(Param $param): Type
    {
        /** @var FunctionLike $parentNode */
        $parentNode = $param->getAttribute(AttributeKey::PARENT_NODE);

        /** @var string $paramName */
        $paramName = $this->nodeNameResolver->getName($param);

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $parentNode->getAttribute(AttributeKey::PHP_DOC_INFO);

        return $phpDocInfo->getParamType($paramName);
    }
}
