<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\TypeContext;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

final class PropertyTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    public function __construct(
        TypeContext $typeContext,
        DocBlockAnalyzer $docBlockAnalyzer,
        SmartClassReflector $smartClassReflector
    ) {
        $this->typeContext = $typeContext;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->smartClassReflector = $smartClassReflector;
    }

    public function getNodeClass(): string
    {
        return Property::class;
    }

    /**
     * @param Property $propertyNode
     * @return string[]
     */
    public function resolve(Node $propertyNode): array
    {
        $propertyName = $propertyNode->props[0]->name->toString();
        $propertyTypes = $this->typeContext->getTypesForProperty($propertyName);
        if ($propertyTypes) {
            return $propertyTypes;
        }

        $propertyTypes = $this->docBlockAnalyzer->getVarTypes($propertyNode);
        if ($propertyTypes === null) {
            return [];
        }

        $propertyTypes = $this->addParentClasses($propertyTypes);

        $this->typeContext->addPropertyTypes($propertyName, $propertyTypes);

        return $propertyTypes;
    }

    /**
     * @param string[] $propertyTypes
     * @return string[]
     */
    private function addParentClasses(array $propertyTypes): array
    {
        foreach ($propertyTypes as $propertyType) {
            $classReflection = $this->smartClassReflector->reflect($propertyType);

            if ($classReflection && $classReflection->getParentClassNames()) {
                $propertyTypes = array_merge($propertyTypes, $classReflection->getParentClassNames());
            }
        }

        return $propertyTypes;
    }
}
