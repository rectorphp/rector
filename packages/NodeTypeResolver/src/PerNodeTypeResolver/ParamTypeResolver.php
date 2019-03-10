<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;

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

    public function __construct(NameResolver $nameResolver, DocBlockManipulator $docBlockManipulator)
    {
        $this->nameResolver = $nameResolver;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Param::class];
    }

    /**
     * @param Param $paramNode
     * @return string[]
     */
    public function resolve(Node $paramNode): array
    {
        if ($paramNode->type) {
            $resolveTypeName = $this->nameResolver->resolve($paramNode->type);
            if ($resolveTypeName) {
                return [$resolveTypeName];
            }
        }

        /** @var FunctionLike $parentNode */
        $parentNode = $paramNode->getAttribute(Attribute::PARENT_NODE);

        return $this->resolveTypesFromFunctionDocBlock($paramNode, $parentNode);
    }

    /**
     * @return string[]
     */
    private function resolveTypesFromFunctionDocBlock(Param $param, FunctionLike $functionLike): array
    {
        $paramTypeInfos = $this->docBlockManipulator->getParamTypeInfos($functionLike);

        /** @var string $paramName */
        $paramName = $this->nameResolver->resolve($param->var);
        if (! isset($paramTypeInfos[$paramName])) {
            return [];
        }

        $fqnTypeNode = $paramTypeInfos[$paramName]->getFqnTypeNode();

        if ($fqnTypeNode instanceof NullableType) {
            return ['null', (string) $fqnTypeNode->type];
        }

        return [(string) $fqnTypeNode];
    }
}
