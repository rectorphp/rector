<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipInitArray
{
    public function run(\PHPStan\Type\Type $type, ?\PHPStan\Type\Type $resolvedType)
    {
        $types[] = $type;

        if ($resolvedType !== null) {
            $types[] = $resolvedType;
        }

        return $types;
    }
}

