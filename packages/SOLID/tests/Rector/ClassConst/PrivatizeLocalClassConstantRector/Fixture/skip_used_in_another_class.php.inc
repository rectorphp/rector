<?php

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class UsesConstant
{
    private const SOME = ConstainsConstant::KEEP_PUBLIC . '_pcs';
}

class ConstainsConstant
{
    public const KEEP_PUBLIC = 'cms';
}
