<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'PhpSpec\ServiceContainer' => [
                    # @see http://www.phpspec.net/en/stable/manual/upgrading-to-phpspec-3.html
                    'set' => 'define',
                    'setShared' => 'define',
                ],
            ],
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'PhpSpec\Console\IO' => 'PhpSpec\Console\ConsoleIO',
                'PhpSpec\IO\IOInterface' => 'PhpSpec\IO\IO',
                'PhpSpec\Locator\ResourceInterface' => 'PhpSpec\Locator\Resource',
                'PhpSpec\Locator\ResourceLocatorInterface' => 'PhpSpec\Locator\ResourceLocator',
                'PhpSpec\Formatter\Presenter\PresenterInterface' => 'PhpSpec\Formatter\Presenter\Presenter',
                'PhpSpec\CodeGenerator\Generator\GeneratorInterface' => 'PhpSpec\CodeGenerator\Generator\Generator',
                'PhpSpec\Extension\ExtensionInterface' => 'PhpSpec\Extension',
                'Phpspec\CodeAnalysis\AccessInspectorInterface' => 'Phpspec\CodeAnalysis\AccessInspector',
                'Phpspec\Event\EventInterface' => 'Phpspec\Event\PhpSpecEvent',
                'PhpSpec\Formatter\Presenter\Differ\EngineInterface' => 'PhpSpec\Formatter\Presenter\Differ\DifferEngine',
                'PhpSpec\Matcher\MatcherInterface' => 'PhpSpec\Matcher\Matcher',
                'PhpSpec\Matcher\MatchersProviderInterface' => 'PhpSpec\Matcher\MatchersProvider',
                'PhpSpec\SpecificationInterface' => 'PhpSpec\Specification',
                'PhpSpec\Runner\Maintainer\MaintainerInterface' => 'PhpSpec\Runner\Maintainer\Maintainer',
            ],
        ]]);
};
