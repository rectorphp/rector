<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class DefaultValuePropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        $propertyProperty = $property->props[0];
        if ($propertyProperty->default === null) {
            return [];
        }

        $nodeStaticType = $this->nodeTypeResolver->getStaticType($propertyProperty->default);
        if ($nodeStaticType === null) {
            return [];
        }

        return $this->staticTypeMapper->mapPHPStanTypeToStrings($nodeStaticType);
    }

    public function getPriority(): int
    {
        return 700;
    }
}
