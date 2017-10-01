<?php declare(strict_types=1);

namespace Rector\Naming;

use Nette\Utils\Strings;

final class CommandNaming
{
    /**
     * Converts "SomeClass\SomeSuperCommand"
     * to "some-super"
     */
    public static function classToName(string $class): string
    {
        $shortClassName = self::getShortClassName($class);
        $rawCommandName = Strings::substring($shortClassName, 0, -strlen('Command'));

        $rawCommandName = lcfirst($rawCommandName);

        return Strings::replace($rawCommandName, '#[A-Z]#', function (array $matches) {
            return '-' . strtolower($matches[0]);
        });
    }

    private static function getShortClassName(string $class): string
    {
        $classParts = explode('\\', $class);

        return array_pop($classParts);
    }
}
