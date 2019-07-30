<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;

final class DefaultValuePropertyTypeInferer extends AbstractPropertyTypeInferer implements PropertyTypeInfererInterface
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
}
