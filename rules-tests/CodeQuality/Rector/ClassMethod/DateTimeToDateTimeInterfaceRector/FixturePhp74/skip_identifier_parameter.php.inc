<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\FixturePhp74;

/**
 * @see https://github.com/rectorphp/rector/issues/5673
 */
class SkipIdentifierParameter
{
    public static function generateThing(string $name): string
    {
        $thingMaker = fn ($name) => $name . ' thing';

        return $thingMaker($name);
    }
}

?>
