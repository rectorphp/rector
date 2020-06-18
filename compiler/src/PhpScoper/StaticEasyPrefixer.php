<?php

declare(strict_types=1);

namespace Rector\Compiler\PhpScoper;

use Nette\Utils\Strings;

final class StaticEasyPrefixer
{
    /**
     * @var string[]
     */
    public const EXCLUDED_NAMESPACES_AND_CLASSES = [
        'Hoa\*',
        'PhpParser\*',
        'PHPStan\*',
        'Rector\*',
        'Symplify\SmartFileSystem\*',
        'Symfony\Component\EventDispatcher\EventSubscriberInterface',
        'Symfony\Component\Console\Style\SymfonyStyle',
    ];

    public static function prefixClass(string $class, string $prefix): string
    {
        foreach (self::EXCLUDED_NAMESPACES_AND_CLASSES as $excludedNamespace) {
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

    public static function unPrefixQuotedValues(string $prefix, string $content): string
    {
        $match = sprintf('\'%s\\\\r\\\\n\'', $prefix);
        $content = Strings::replace($content, '#' . $match . '#', '\'\\\\r\\\\n\'');

        $match = sprintf('\'%s\\\\', $prefix);

        return Strings::replace($content, '#' . $match . '#', "'");
    }

    public static function unPreSlashQuotedValues(string $content): string
    {
        return Strings::replace($content, '#\'\\\\(\w|@)#', "'$1");
    }
}
