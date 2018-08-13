<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Broker\Broker;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;
use Rector\Php\TypeAnalyzer;

final class PropertyTypeResolver implements PerNodeTypeResolverInterface
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
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    public function __construct(
        ClassReflectionTypesResolver $classReflectionTypesResolver,
        DocBlockAnalyzer $docBlockAnalyzer,
        Broker $broker,
        TypeAnalyzer $typeAnalyzer
    ) {
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->broker = $broker;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $propertyNode
     * @return string[]
     */
    public function resolve(Node $propertyNode): array
    {
        // doc
        $propertyTypes = $this->docBlockAnalyzer->getVarTypes($propertyNode);
        if ($propertyTypes === []) {
            return [];
        }

        $propertyTypes = $this->filterOutScalarTypes($propertyTypes);

        foreach ($propertyTypes as $propertyType) {
            $propertyClassReflection = $this->broker->getClass($propertyType);
            $propertyTypes += $this->classReflectionTypesResolver->resolve($propertyClassReflection);
        }

        return $propertyTypes;
    }

    /**
     * @param string[] $propertyTypes
     * @return string[]
     */
    private function filterOutScalarTypes(array $propertyTypes): array
    {
        foreach ($propertyTypes as $key => $type) {
            if (! $this->typeAnalyzer->isPhpReservedType($type)) {
                continue;
            }
            unset($propertyTypes[$key]);
        }

        if ($propertyTypes === ['null']) {
            return [];
        }

        return $propertyTypes;
    }
}
