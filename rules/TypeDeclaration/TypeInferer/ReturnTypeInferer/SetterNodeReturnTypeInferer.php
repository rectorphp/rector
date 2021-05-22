<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\NodeManipulator\FunctionLikeManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;

final class SetterNodeReturnTypeInferer implements ReturnTypeInfererInterface
{
    public function __construct(
        private AssignToPropertyTypeInferer $assignToPropertyTypeInferer,
        private FunctionLikeManipulator $functionLikeManipulator,
        private TypeFactory $typeFactory
    ) {
    }

    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        $classLike = $functionLike->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return new MixedType();
        }

        $returnedPropertyNames = $this->functionLikeManipulator->getReturnedLocalPropertyNames($functionLike);

        $types = [];
        foreach ($returnedPropertyNames as $returnedPropertyName) {
            $inferredPropertyType = $this->assignToPropertyTypeInferer->inferPropertyInClassLike(
                $returnedPropertyName,
                $classLike
            );
            if (! $inferredPropertyType instanceof Type) {
                continue;
            }
            $types[] = $inferredPropertyType;
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    public function getPriority(): int
    {
        return 600;
    }
}
