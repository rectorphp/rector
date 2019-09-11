<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
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

    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        $classNode = $functionLike->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return new MixedType();
        }

        $returnedPropertyNames = $this->functionLikeManipulator->getReturnedLocalPropertyNames($functionLike);

        $types = [];
        foreach ($returnedPropertyNames as $returnedPropertyName) {
            $types[] = $this->assignToPropertyTypeInferer->inferPropertyInClassLike($returnedPropertyName, $classNode);
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    public function getPriority(): int
    {
        return 600;
    }
}
