<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::EXCLUDE_PATHS, [
        '/tests/',
        // Individual classes that can be excluded because
        // they are not used by Rector, and they use classes
        // loaded with "require-dev" so it'd throw an error
        __DIR__ . '/vendor/symfony/cache/DoctrineProvider.php',
        __DIR__ . '/vendor/symfony/dependency-injection/Compiler/AbstractRecursivePass.php',
        __DIR__ . '/vendor/symfony/dependency-injection/Compiler/CheckTypeDeclarationsPass.php',
        // __DIR__ . '/vendor/symfony/dependency-injection/ContainerBuilder.php',
        __DIR__ . '/vendor/symfony/dependency-injection/Dumper/PhpDumper.php',
        __DIR__ . '/vendor/symfony/dependency-injection/ExpressionLanguage.php',
        __DIR__ . '/vendor/symfony/dependency-injection/ExpressionLanguageProvider.php',
        __DIR__ . '/vendor/symfony/http-kernel/HttpKernelBrowser.php',
        __DIR__ . '/vendor/symfony/string/Slugger/AsciiSlugger.php',
    ]);
};
