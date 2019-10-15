<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class VarDocPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function inferProperty(Property $property): Type
    {
        if ($property->getDocComment() === null) {
            return new MixedType();
        }

        $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($property);

        return $phpDocInfo->getVarType();
    }

    public function getPriority(): int
    {
        return 150;
    }
}
