<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ThisType;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\TypeAttribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;

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
     * @var Broker
     */
    private $broker;

    /**
     * @var TypeToStringResolver
     */
    private $typeToStringResolver;

    public function __construct(
        ClassReflectionTypesResolver $classReflectionTypesResolver,
        DocBlockAnalyzer $docBlockAnalyzer,
        Broker $broker,
        TypeToStringResolver $typeToStringResolver
    ) {
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->broker = $broker;
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

            $types = $this->typeToStringResolver->resolve($type);

            // complete parents
            foreach ($types as $type) {
                $propertyClassReflection = $this->broker->getClass($type);
                $types = array_merge($types, $this->classReflectionTypesResolver->resolve($propertyClassReflection));
            }

            return array_unique($types);
        }

        // get from annotation
        $variableTypes = $this->docBlockAnalyzer->getVarTypes($variableNode);

        foreach ($variableTypes as $i => $type) {
            if (! class_exists($type)) {
                unset($variableTypes[$i]);
                continue;
            }
            $propertyClassReflection = $this->broker->getClass($type);
            $variableTypes = array_merge(
                $variableTypes,
                $this->classReflectionTypesResolver->resolve($propertyClassReflection)
            );
        }

        return array_unique($variableTypes);
    }
}
