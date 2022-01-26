<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector\Fixture;

final class SkipNonFinalConst
{
    public const NAME = 'value';
}
