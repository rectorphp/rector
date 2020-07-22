<?php

declare(strict_types=1);

namespace Rector\Compiler\PhpScoper;

use Nette\Utils\Strings;

/**
 * @see \Rector\Compiler\Tests\PhpScoper\StaticEasyPrefixerTest
 */
final class StaticEasyPrefixer
{
    /**
     * @var string[]
     */
    public const EXCLUDED_CLASSES = [
        'Symfony\Component\EventDispatcher\EventSubscriberInterface',
        'Symfony\Component\Console\Style\SymfonyStyle',
        // doctrine annotations to autocomplete
        'JMS\DiExtraBundle\Annotation\Inject',
        // part of public interface of configs.php
        'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
        // well, this is a function
        'Symfony\Component\DependencyInjection\Loader\Configurator\ref',
    ];

    /**
     * @var string[]
     */
    private const EXCLUDED_NAMESPACES = [
        'Hoa\*',
        'PhpParser\*',
        'PHPStan\*',
        'Rector\*',
        'Symplify\SmartFileSystem\*',
        'Symplify\ConsoleColorDiff\*',
        // doctrine annotations to autocomplete
        'Doctrine\ORM\Mapping\*',
    ];

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
        return Strings::replace($content, '#\'\\\\(\w|@)#', "'$1");
    }

    /**
     * @return string[]
     */
    public static function getExcludedNamespacesAndClasses(): array
    {
        return array_merge(self::EXCLUDED_NAMESPACES, self::EXCLUDED_CLASSES);
    }
}
