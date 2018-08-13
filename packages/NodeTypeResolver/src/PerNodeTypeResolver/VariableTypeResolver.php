<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ThisType;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\TypeAttribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;

final class VariableTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var ClassReflectionTypesResolver
     */
    private $classReflectionTypesResolver;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var TypeToStringResolver
     */
    private $typeToStringResolver;

    public function __construct(DocBlockAnalyzer $docBlockAnalyzer, TypeToStringResolver $typeToStringResolver)
    {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->typeToStringResolver = $typeToStringResolver;
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
        $nodeScope = $variableNode->getAttribute(TypeAttribute::SCOPE);

        $variableName = (string) $variableNode->name;

        if ($nodeScope->hasVariableType($variableName) === TrinaryLogic::createYes()) {
            $type = $nodeScope->getVariableType($variableName);

            // this
            if ($type instanceof ThisType) {
                return $this->classReflectionTypesResolver->resolve($nodeScope->getClassReflection());
            }

            return $this->typeToStringResolver->resolve($type);
        }

        // get from annotation
        return $this->docBlockAnalyzer->getVarTypes($variableNode);
    }
}
