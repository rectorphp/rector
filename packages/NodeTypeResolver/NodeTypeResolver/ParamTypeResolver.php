<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix202208\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ParamTypeResolver\ParamTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Param>
 */
final class ParamTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, PhpDocInfoFactory $phpDocInfoFactory, BetterNodeFinder $betterNodeFinder)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @required
     */
    public function autowire(NodeTypeResolver $nodeTypeResolver, StaticTypeMapper $staticTypeMapper) : void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Param::class];
    }
    /**
     * @param Param $node
     */
    public function resolve(Node $node) : Type
    {
        $paramType = $this->resolveFromParamType($node);
        if (!$paramType instanceof MixedType) {
            return $paramType;
        }
        $firstVariableUseType = $this->resolveFromFirstVariableUse($node);
        if (!$firstVariableUseType instanceof MixedType) {
            return $firstVariableUseType;
        }
        return $this->resolveFromFunctionDocBlock($node);
    }
    private function resolveFromParamType(Param $param) : Type
    {
        if ($param->type === null) {
            return new MixedType();
        }
        if ($param->type instanceof Identifier) {
            return new MixedType();
        }
        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
    }
    private function resolveFromFirstVariableUse(Param $param) : Type
    {
        $classMethod = $this->betterNodeFinder->findParentType($param, ClassMethod::class);
        if (!$classMethod instanceof ClassMethod) {
            return new MixedType();
        }
        $paramName = $this->nodeNameResolver->getName($param);
        $paramStaticType = new MixedType();
        // special case for param inside method/function
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use($paramName, &$paramStaticType) : ?int {
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $paramName)) {
                return null;
            }
            $paramStaticType = $this->nodeTypeResolver->getType($node);
            return NodeTraverser::STOP_TRAVERSAL;
        });
        return $paramStaticType;
    }
    private function resolveFromFunctionDocBlock(Param $param) : Type
    {
        $phpDocInfo = $this->getFunctionLikePhpDocInfo($param);
        $paramName = $this->nodeNameResolver->getName($param);
        return $phpDocInfo->getParamType($paramName);
    }
    private function getFunctionLikePhpDocInfo(Param $param) : PhpDocInfo
    {
        $parentNode = $param->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof FunctionLike) {
            throw new ShouldNotHappenException();
        }
        return $this->phpDocInfoFactory->createFromNodeOrEmpty($parentNode);
    }
}
