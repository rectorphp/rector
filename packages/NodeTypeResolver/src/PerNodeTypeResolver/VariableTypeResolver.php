<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ThisType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class VariableTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var TypeToStringResolver
     */
    private $typeToStringResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(
        DocBlockAnalyzer $docBlockAnalyzer,
        TypeToStringResolver $typeToStringResolver,
        NameResolver $nameResolver
    ) {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
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
        $nodeScope = $variableNode->getAttribute(Attribute::SCOPE);
        if ($nodeScope === null) {
            throw new ShouldNotHappenException();
        }

        $variableName = $this->nameResolver->resolve($variableNode);
        if ($variableName === null) {
            return [];
        }

        if ($nodeScope->hasVariableType($variableName) === TrinaryLogic::createYes()) {
            $type = $nodeScope->getVariableType($variableName);

            // this
            if ($type instanceof ThisType) {
                return [$nodeScope->getClassReflection()->getName()];
            }

            return $this->typeToStringResolver->resolve($type);
        }

        // get from annotation
        $varTypeInfo = $this->docBlockAnalyzer->getVarTypeInfo($variableNode);
        if ($varTypeInfo === null) {
            return [];
        }

        $varType = $varTypeInfo->getFqnType();
        if ($varType === null) {
            return [];
        }

        return [$varType];
    }
}
