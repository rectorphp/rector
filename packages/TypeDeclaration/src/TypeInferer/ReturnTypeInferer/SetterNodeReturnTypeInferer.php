<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\IntersectionType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\FunctionLikeManipulator;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;

final class SetterNodeReturnTypeInferer extends AbstractTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @var FunctionLikeManipulator
     */
    private $functionLikeManipulator;

    /**
     * @var AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;

    public function __construct(
        FunctionLikeManipulator $functionLikeManipulator,
        AssignToPropertyTypeInferer $assignToPropertyTypeInferer
    ) {
        $this->functionLikeManipulator = $functionLikeManipulator;
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
    }

    /**
     * @return string[]
     */
    public function inferFunctionLike(FunctionLike $functionLike): array
    {
        $classNode = $functionLike->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return [];
        }

        $returnedPropertyNames = $this->functionLikeManipulator->getReturnedLocalPropertyNames($functionLike);

        $types = [];
        foreach ($returnedPropertyNames as $returnedPropertyName) {
            $freshTypes = $this->assignToPropertyTypeInferer->inferPropertyInClassLike(
                $returnedPropertyName,
                $classNode
            );
            $types = array_merge($types, $freshTypes);
        }

        $assignedExprStaticType = new IntersectionType($types);

        return $this->staticTypeToStringResolver->resolveObjectType($assignedExprStaticType);
    }
}
