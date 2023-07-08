<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix202307\Symfony\Contracts\Service\Attribute\Required;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ParamTypeResolver\ParamTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Param>
 */
final class ParamTypeResolver implements NodeTypeResolverInterface
{
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
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @required
     */
    public function autowire(NodeTypeResolver $nodeTypeResolver) : void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
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
        return $this->resolveFromFunctionDocBlock($node);
    }
    private function resolveFromParamType(Param $param) : Type
    {
        if ($param->type === null) {
            return new MixedType();
        }
        return $this->nodeTypeResolver->getType($param->type);
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
