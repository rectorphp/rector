<?php

declare(strict_types=1);

namespace Rector\Compiler\PhpScoper;

final class StaticEasyPrefixer
{
    /**
     * @var string[]
     */
    public const EXCLUDED_CLASSES = [
        // part of public interface of configs.php
        'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
        // this is not prefixed on few places by php-scoper by default, probably some bug
        'Doctrine\Inflector\Inflector',
        // for SmartFileInfo
        'Symplify\SmartFileSystem\SmartFileInfo',
        // for ComposerJson because it is part of the public API. I.e. ComposerRectorInterface
        'Symplify\ComposerJsonManipulator\ValueObject\ComposerJson'
    ];

    /**
     * @var string[]
     */
    private const EXCLUDED_NAMESPACES = [
        // naturally
        'Rector\*',
        // we use this API a lot
        'PhpParser\*',

        // phpstan needs to be here, as phpstan-extracted/vendor autoload is statically generated and namespaces cannot be changed
        'PHPStan\*',

        // this is public API of a Rector rule
        'Symplify\RuleDocGenerator\*',

        // for configuring sets with ValueObjectInliner
        'Symplify\SymfonyPhpConfig\*',

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
