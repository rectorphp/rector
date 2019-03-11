<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PHPStan\Analyser\Scope;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ThisType;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class VariableTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var TypeToStringResolver
     */
    private $typeToStringResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        TypeToStringResolver $typeToStringResolver,
        NameResolver $nameResolver
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->typeToStringResolver = $typeToStringResolver;
        $this->nameResolver = $nameResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Variable::class];
    }

    /**
     * @param Variable $variableNode
     * @return string[]
     */
    public function resolve(Node $variableNode): array
    {
        $parentNode = $variableNode->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof Param) {
            return $this->nodeTypeResolver->resolve($parentNode);
        }

        $variableName = $this->nameResolver->resolve($variableNode);
        if ($variableName === null) {
            return [];
        }

        $scopeType = $this->resolveTypesFromScope($variableNode, $variableName);
        if ($scopeType !== []) {
            return $scopeType;
        }

        // get from annotation
        $varTypeInfo = $this->docBlockManipulator->getVarTypeInfo($variableNode);
        if ($varTypeInfo === null) {
            return [];
        }

        $varType = $varTypeInfo->getFqnType();

        return $varType === null ? [] : [$varType];
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    private function resolveNodeScope(Node $variableNode): ?Scope
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $variableNode->getAttribute(Attribute::SCOPE);
        if ($nodeScope instanceof Scope) {
            return $nodeScope;
        }

        $parentNode = $variableNode->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof Node) {
            $nodeScope = $parentNode->getAttribute(Attribute::SCOPE);
            if ($nodeScope instanceof Scope) {
                return $nodeScope;
            }
        }

        // get nearest variable scope
        $method = $variableNode->getAttribute(Attribute::METHOD_NODE);
        if ($method instanceof Node) {
            $nodeScope = $method->getAttribute(Attribute::SCOPE);
            if ($nodeScope instanceof Scope) {
                return $nodeScope;
            }
        }

        // unknown scope
        return null;
    }

    /**
     * @return string[]
     */
    private function resolveTypesFromScope(Variable $variable, string $variableName): array
    {
        $nodeScope = $this->resolveNodeScope($variable);
        if ($nodeScope === null) {
            return [];
        }

        if ($nodeScope->hasVariableType($variableName) !== TrinaryLogic::createYes()) {
            return [];
        }

        $type = $nodeScope->getVariableType($variableName);

        // this
        if ($type instanceof ThisType) {
            return [$nodeScope->getClassReflection()->getName()];
        }

        return $this->typeToStringResolver->resolve($type);
    }
}
