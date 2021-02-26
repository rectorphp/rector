<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

/**
 * @see https://github.com/rectorphp/rector/issues/5673
 */
class SkipArrowFunction
{
    public static function generateThing(string $name): string
    {
        $thingMaker = fn ($name) => $name . ' thing';

        return $thingMaker($name);
    }
}

?>
