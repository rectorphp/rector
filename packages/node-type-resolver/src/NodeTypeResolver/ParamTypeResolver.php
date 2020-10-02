<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver\ParamTypeResolverTest
 */
final class ParamTypeResolver implements NodeTypeResolverInterface
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

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @required
     */
    public function autowireParamTypeResolver(
        NodeTypeResolver $nodeTypeResolver,
        StaticTypeMapper $staticTypeMapper
    ): void {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
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

    private function resolveFromType(Node $node): Type
    {
        if ($node->type !== null && ! $node->type instanceof Identifier) {
            return $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->type);
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
        $phpDocInfo = $this->getFunctionLikePhpDocInfo($param);
        if ($phpDocInfo === null) {
            return new MixedType();
        }

        /** @var string $paramName */
        $paramName = $this->nodeNameResolver->getName($param);
        return $phpDocInfo->getParamType($paramName);
    }

    private function getFunctionLikePhpDocInfo(Param $param): ?PhpDocInfo
    {
        $parentNode = $param->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof FunctionLike) {
            throw new ShouldNotHappenException();
        }

        return $parentNode->getAttribute(AttributeKey::PHP_DOC_INFO);
    }
}
