<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Param;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;
use Rector\NodeTypeResolver\UseStatements;

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

    public function __construct(TypeContext $typeContext, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->typeContext = $typeContext;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
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
        $variableName = $paramNode->var->name;

        // 1. method(ParamType $param)
        if ($paramNode->type) {
            $variableType = $this->nodeTypeResolver->resolve($paramNode->type);
            if ($variableType) {
                $this->typeContext->addVariableWithType($variableName, $variableType);

                return $variableType;
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
        $paramType = $this->resolveTypeWithNamespaceAndUseStatments(
            $paramType,
            (string) $paramNode->getAttribute(Attribute::NAMESPACE),
            $paramNode->getAttribute(Attribute::USE_STATEMENTS)
        );

        if ($paramType) {
            $this->typeContext->addVariableWithType($variableName, $paramType);
        }

        return $paramType;
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @todo decouple to FqnResolver, if needed; phpdocumentor might handle this though
     */
    private function resolveTypeWithNamespaceAndUseStatments(
        string $type,
        string $namespace,
        UseStatements $useStatements
    ): string {
        foreach ($useStatements->getUseStatements() as $useStatement) {
            if (Strings::endsWith($useStatement, '\\' . $type)) {
                return $useStatement;
            }
        }

        return ($namespace ? $namespace . '\\' : '') . $type;
    }
}
