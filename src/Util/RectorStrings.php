<?php declare(strict_types=1);

namespace Rector\Util;

use Nette\Utils\Strings;
use Symfony\Component\Console\Input\StringInput;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class RectorStrings
{
    /**
     * @return string[]
     */
    public static function splitCommandToItems(string $command): array
    {
        $privatesCaller = new PrivatesCaller();

        return $privatesCaller->callPrivateMethod(new StringInput(''), 'tokenize', $command);
    }

    public static function camelCaseToDashes(string $input): string
    {
        return self::camelCaseToGlue($input, '-');
    }

    public static function camelCaseToUnderscore(string $input): string
    {
        return self::camelCaseToGlue($input, '_');
    }

    private static function camelCaseToGlue(string $input, string $glue): string
    {
        $matches = Strings::matchAll($input, '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!');

        $parts = [];
        foreach ($matches as $match) {
            $parts[] = $match[0] === strtoupper($match[0]) ? strtolower($match[0]) : lcfirst($match[0]);
        }

        return implode($glue, $parts);
    }
}
