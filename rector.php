<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Injection\Rector\StaticCall\StaticCallToAnotherServiceConstructorInjectionRector;
use Rector\Injection\ValueObject\StaticCallToMethodCall;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/create-rector.php', null, 'not_found');

    $services = $containerConfigurator->services();

    // @todo improve this
    $services->set('value_object', StaticCallToMethodCall::class)
        ->args(['Nette\Utils\FileSystem', 'write', 'Symplify\SmartFileSystem\SmartFileSystem', 'dumpFile'])
        ->autowire(false);

    $services->set(StaticCallToAnotherServiceConstructorInjectionRector::class)
        ->arg('$staticCallsToMethodCalls', [ref('value_object')]);

    $parameters = $containerConfigurator->parameters();

    #    Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector: null
    #    Rector\Autodiscovery\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector: null
    # bleeding edge feature
    # is_cache_enabled: true
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::SETS, [SetList::NAMING]);

    $parameters->set(
        Option::PATHS,
        [
            __DIR__ . '/src',
            __DIR__ . '/tests',
            __DIR__ . '/rules',
            __DIR__ . '/utils',
            __DIR__ . '/packages',
            __DIR__ . '/bin/rector',
        ]
    );

    $parameters->set(
        Option::EXCLUDE_PATHS,
        [
            '/Source/',
            '/*Source/',
            '/Fixture/',
            '/Expected/',
            __DIR__ . '/packages/doctrine-annotation-generated/src/*',
            '*.php.inc',
        ]
    );

    # so Rector code is still PHP 7.2 compatible
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.2');
};
