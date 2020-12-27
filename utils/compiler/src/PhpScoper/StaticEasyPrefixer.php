<?php

declare(strict_types=1);

namespace Rector\Compiler\PhpScoper;

final class StaticEasyPrefixer
{
    /**
     * @var string[]
     */
    public const EXCLUDED_CLASSES = [
        'Symfony\Component\Console\Style\SymfonyStyle',
        // part of public interface of configs.php
        'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
        // this is not prefixed on few places by php-scoper by default, probably some bug
        'Doctrine\Inflector\Inflector',
    ];

    /**
     * @var string[]
     */
    private const EXCLUDED_NAMESPACES = [
        // naturally
        'Rector\*',
        // we use this API a lot
        'PhpParser\*',

        // 'PHPStan\*', - use the minimal scope as possible
        'PHPStan\PhpDocParser\Ast\*',
        'PHPStan\Type\*',

        // doctrine annotations to autocomplete
        'Doctrine\ORM\Mapping\*',
    ];

    /**
     * @return string[]
     */
    public static function getExcludedNamespacesAndClasses(): array
    {
        return array_merge(self::EXCLUDED_NAMESPACES, self::EXCLUDED_CLASSES);
    }
}
