<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;
use Rector\ReflectionDocBlock\NodeAnalyzer\NamespaceAnalyzer;

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

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    public function __construct(
        TypeContext $typeContext,
        DocBlockAnalyzer $docBlockAnalyzer,
        NamespaceAnalyzer $namespaceAnalyzer
    ) {
        $this->typeContext = $typeContext;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
    }

    public function getNodeClass(): string
    {
        return Param::class;
    }

    /**
     * @param Param $paramNode
     * @return string[]
     */
    public function resolve(Node $paramNode): array
    {
        $variableName = $paramNode->var->name;

        // 1. method(ParamType $param)
        if ($paramNode->type) {
            $variableTypes = $this->nodeTypeResolver->resolve($paramNode->type);
            if ($variableTypes) {
                $this->typeContext->addVariableWithTypes($variableName, $variableTypes);

                return $variableTypes;
            }
        }

        // 2. @param ParamType $param
        /* @var \PhpParser\Node\Stmt\ClassMethod $classMethod */
        $classMethod = $paramNode->getAttribute(Attribute::PARENT_NODE);

        // resolve param type from docblock
        $paramType = $this->docBlockAnalyzer->getParamTypeFor($classMethod, $variableName);
        if ($paramType === null) {
            return null;
        }

        // resolve to FQN
        $paramType = $this->namespaceAnalyzer->resolveTypeToFullyQualified($paramType, $paramNode);
        if ($paramType) {
            $this->typeContext->addVariableWithTypes($variableName, [$paramType]);
        }

        return [$paramType];
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
}
