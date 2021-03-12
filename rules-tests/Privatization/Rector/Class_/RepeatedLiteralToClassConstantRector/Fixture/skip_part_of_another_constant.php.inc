<?php

namespace Rector\Tests\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class SkipPartOfAnotherConstant
{
    public const TREE_OF_VALUES = [
        'firstValue' => 'string',
        'seocnd' => 'string',
        'thirdValue' => 'string',
    ];
}
