<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use RectorPrefix20220418\Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function RectorPrefix20220418\Symfony\Component\DependencyInjection\Loader\Configurator\service;
use RectorPrefix20220418\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix20220418\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20220418\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use RectorPrefix20220418\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use RectorPrefix20220418\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20220418\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use RectorPrefix20220418\Symplify\SmartFileSystem\Json\JsonFileSystem;
use RectorPrefix20220418\Symplify\SmartFileSystem\SmartFileSystem;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/parameters.php');
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\RectorGenerator\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/ValueObject']);
    $services->set(\RectorPrefix20220418\Symplify\PackageBuilder\Parameter\ParameterProvider::class)->arg('$container', \RectorPrefix20220418\Symfony\Component\DependencyInjection\Loader\Configurator\service('service_container'));
    // console
    $services->set(\RectorPrefix20220418\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class);
    $services->set(\RectorPrefix20220418\Symfony\Component\Console\Style\SymfonyStyle::class)->factory([\RectorPrefix20220418\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20220418\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class), 'create']);
    // filesystem
    $services->set(\RectorPrefix20220418\Symplify\SmartFileSystem\Json\JsonFileSystem::class);
    $services->set(\RectorPrefix20220418\Symplify\SmartFileSystem\SmartFileSystem::class);
    $services->set(\RectorPrefix20220418\Symplify\SmartFileSystem\Finder\FinderSanitizer::class);
    $services->set(\RectorPrefix20220418\Symplify\SmartFileSystem\FileSystemGuard::class);
    // privates
    $services->set(\RectorPrefix20220418\Symplify\PackageBuilder\Reflection\PrivatesCaller::class);
    $services->set(\RectorPrefix20220418\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
    // php-parser
    $services->set(\PhpParser\PrettyPrinter\Standard::class)->arg('$options', ['shortArraySyntax' => \true]);
    $services->set(\PhpParser\ParserFactory::class);
    $services->set(\PhpParser\Parser::class)->factory([\RectorPrefix20220418\Symfony\Component\DependencyInjection\Loader\Configurator\service(\PhpParser\ParserFactory::class), 'create'])->arg('$kind', \PhpParser\ParserFactory::PREFER_PHP7);
};
