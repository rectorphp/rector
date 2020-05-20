<?php

declare(strict_types=1);

namespace Rector\Compiler\PhpScoper;

use Nette\Utils\Strings;

final class StaticEasyPrefixer
{
    /**
     * @var string[]
     */
    public const EXCLUDED_NAMESPACES = ['Hoa\*', 'PhpParser\*', 'PHPStan\*', 'Rector\*'];

    public static function prefixClass(string $class, string $prefix): string
    {
        foreach (self::EXCLUDED_NAMESPACES as $excludedNamespace) {
            $excludedNamespace = Strings::substring($excludedNamespace, 0, -2) . '\\';
            if (Strings::startsWith($class, $excludedNamespace)) {
                return $class;
            }
        }

        if (Strings::startsWith($class, '@')) {
            return $class;
        }

        return $prefix . '\\' . $class;
    }

    public static function unprefixQuotedValues(string $prefix, string $content): string
    {
        $match = sprintf('\'%s\\\\r\\\\n\'', $prefix);
        $content = Strings::replace($content, '#' . $match . '#', '\'\\\\r\\\\n\'');

        $match = sprintf('\'%s\\\\', $prefix);

        return Strings::replace($content, '#' . $match . '#', "'");
    }
}
