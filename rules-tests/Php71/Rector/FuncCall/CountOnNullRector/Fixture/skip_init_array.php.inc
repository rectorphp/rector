<?php

declare(strict_types=1);

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

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

