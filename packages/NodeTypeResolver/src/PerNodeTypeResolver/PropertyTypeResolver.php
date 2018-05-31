<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use Rector\BetterPhpDocParser\NodeAnalyzer\DocBlockAnalyzer;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\TypeContext;
use Rector\Php\TypeAnalyzer;

final class PropertyTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

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
        SmartClassReflector $smartClassReflector,
        TypeAnalyzer $typeAnalyzer
    ) {
        $this->typeContext = $typeContext;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->smartClassReflector = $smartClassReflector;
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
        /** @var VarLikeIdentifier $varLikeIdentifierNode */
        $varLikeIdentifierNode = $propertyNode->props[0]->name;

        $propertyName = $varLikeIdentifierNode->toString();

        $classNode = $propertyNode->getAttribute(Attribute::CLASS_NODE);
        $this->typeContext->enterClassLike($classNode);

        $propertyTypes = $this->typeContext->getTypesForProperty($propertyName);

        if ($propertyTypes) {
            return $propertyTypes;
        }

        $propertyTypes = $this->docBlockAnalyzer->getVarTypes($propertyNode);
        if ($propertyTypes === [] || $propertyTypes === ['string']) {
            return [];
        }

        $propertyTypes = $this->filterOutScalarTypes($propertyTypes);
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

        return array_values(array_unique($propertyTypes));
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
