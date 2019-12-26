<?php

namespace Rector\Php71\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class SkipConstantValue
{
    const ROLE_DEFAULT = 'default';

    public function run(int $role)
    {
        if ($role !== self::ROLE_DEFAULT) {
        }
    }
}
