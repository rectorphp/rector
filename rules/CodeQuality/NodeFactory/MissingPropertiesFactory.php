<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use Rector\CodeQuality\ValueObject\DefinedPropertyWithType;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
final class MissingPropertiesFactory
{
    /**
     * @readonly
     */
    private \Rector\CodeQuality\NodeFactory\PropertyTypeDecorator $propertyTypeDecorator;
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(\Rector\CodeQuality\NodeFactory\PropertyTypeDecorator $propertyTypeDecorator, PhpVersionProvider $phpVersionProvider, StaticTypeMapper $staticTypeMapper)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * @param DefinedPropertyWithType[] $definedPropertiesWithType
     * @return Property[]
     */
    public function create(array $definedPropertiesWithType): array
    {
        $newProperties = [];
        foreach ($definedPropertiesWithType as $definedPropertyWithType) {
            $visibilityModifier = $this->isFromAlwaysDefinedMethod($definedPropertyWithType) ? Modifiers::PRIVATE : Modifiers::PUBLIC;
            $property = new Property($visibilityModifier, [new PropertyItem($definedPropertyWithType->getName())]);
            if ($this->isFromAlwaysDefinedMethod($definedPropertyWithType) && $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
                $propertyType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($definedPropertyWithType->getType(), TypeKind::PROPERTY);
                if ($propertyType instanceof Node) {
                    $property->type = $propertyType;
                    $newProperties[] = $property;
                    continue;
                }
            }
            // fallback to docblock
            $this->propertyTypeDecorator->decorateProperty($property, $definedPropertyWithType->getType());
            $newProperties[] = $property;
        }
        return $newProperties;
    }
    private function isFromAlwaysDefinedMethod(DefinedPropertyWithType $definedPropertyWithType): bool
    {
        return in_array($definedPropertyWithType->getDefinedInMethodName(), [MethodName::CONSTRUCT, MethodName::SET_UP], \true);
    }
}
