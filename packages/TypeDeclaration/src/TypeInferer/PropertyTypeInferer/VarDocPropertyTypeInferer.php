<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class VarDocPropertyTypeInferer implements PropertyTypeInfererInterface
{
    public function inferProperty(Property $property): Type
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);

        return $phpDocInfo ? $phpDocInfo->getVarType() : new MixedType();
    }

    public function getPriority(): int
    {
        return 150;
    }
}
