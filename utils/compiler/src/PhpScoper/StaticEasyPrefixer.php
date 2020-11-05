<?php

declare(strict_types=1);

namespace Rector\Compiler\PhpScoper;

use Nette\Utils\Strings;

final class StaticEasyPrefixer
{
    /**
     * @var string[]
     */
    public const EXCLUDED_CLASSES = [
        'Symfony\Component\EventDispatcher\EventSubscriberInterface',
        'Symfony\Component\Console\Style\SymfonyStyle',
        // part of public interface of configs.php
        'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
        // this is not prefixed by php-scoper by default, probably some bug
        'Doctrine\Inflector\Inflector',
    ];

    /**
     * @var string[]
     */
    private const EXCLUDED_NAMESPACES = [
        'Hoa\*',
        'PhpParser\*',
        'PHPStan\*',
        'Rector\*',
        'Symplify\*',
        // doctrine annotations to autocomplete
        'Doctrine\ORM\Mapping\*',
    ];

    /**
     * @var string
     * @see https://regex101.com/r/P8sXfr/1
     */
    private const QUOTED_VALUE_REGEX = '#\'\\\\(\w|@)#';

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

    public static function unPrefixQuotedValues(string $prefix, string $content): string
    {
        $match = sprintf('\'%s\\\\r\\\\n\'', $prefix);
        $content = Strings::replace($content, '#' . $match . '#', '\'\\\\r\\\\n\'');

        $match = sprintf('\'%s\\\\', $prefix);

        return Strings::replace($content, '#' . $match . '#', "'");
    }

    public static function unPreSlashQuotedValues(string $content): string
    {
        return Strings::replace($content, self::QUOTED_VALUE_REGEX, "'$1");
    }

    /**
     * @return string[]
     */
    public static function getExcludedNamespacesAndClasses(): array
    {
        return array_merge(self::EXCLUDED_NAMESPACES, self::EXCLUDED_CLASSES);
    }
}
