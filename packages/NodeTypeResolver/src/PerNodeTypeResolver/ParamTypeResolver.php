<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;

final class ParamTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(TypeContext $typeContext)
    {
        $this->typeContext = $typeContext;
    }

    public function getNodeClass(): string
    {
        return Param::class;
    }

    /**
     * @param Param $paramNode
     */
    public function resolve(Node $paramNode): ?string
    {
        if ($paramNode->type === null) {
            return null;
        }

        $variableName = $paramNode->var->name;
        $variableType = $this->nodeTypeResolver->resolve($paramNode->type);

        if ($variableType) {
            $this->typeContext->addVariableWithType($variableName, $variableType);

            return $variableType;
        }

        return null;
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
}
