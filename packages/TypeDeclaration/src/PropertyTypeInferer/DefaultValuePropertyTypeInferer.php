<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;
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

        $nodeStaticType = $this->nodeTypeResolver->getNodeStaticType($propertyProperty->default);
        if ($nodeStaticType === null) {
            return [];
        }

        return $this->staticTypeToStringResolver->resolveObjectType($nodeStaticType);
    }

    public function getPriority(): int
    {
        return 700;
    }
}
