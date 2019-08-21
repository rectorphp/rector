<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\IntersectionType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;

final class AllAssignNodePropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;

    public function __construct(AssignToPropertyTypeInferer $assignToPropertyTypeInferer)
    {
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
    }

    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        /** @var ClassLike $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);

        $propertyName = $this->nameResolver->getName($property);

        $assignedExprStaticTypes = $this->assignToPropertyTypeInferer->inferPropertyInClassLike($propertyName, $class);
        if ($assignedExprStaticTypes === []) {
            return [];
        }

        $assignedExprStaticType = new IntersectionType($assignedExprStaticTypes);

        return $this->staticTypeToStringResolver->resolveObjectType($assignedExprStaticType);
    }

    public function getPriority(): int
    {
        return 500;
    }
}
