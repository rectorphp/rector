<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\FunctionLike;

use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use Rector\Downgrade\Contract\Rector\DowngradeTypeRectorInterface;

abstract class AbstractDowngradeParamTypeDeclarationRector extends AbstractDowngradeParamDeclarationRector implements DowngradeTypeRectorInterface
{
    public function shouldRemoveParamDeclaration(Param $param): bool
    {
        // It can either be the type, or the nullable type (eg: ?object)
        $isNullableType = $param->type instanceof NullableType;
        if (! $param->type instanceof Identifier && ! $isNullableType) {
            return false;
        }

        // If it is the NullableType, extract the name from its inner type
        if ($isNullableType) {
            /** @var NullableType */
            $nullableType = $param->type;
            $typeName = $this->getName($nullableType->type);
        } else {
            $typeName = $this->getName($param->type);
        }

        // Check it is the type to be removed
        return $typeName === $this->getTypeNameToRemove();
    }
}
