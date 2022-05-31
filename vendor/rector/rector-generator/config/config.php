<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use RectorPrefix20220531\Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service;
use RectorPrefix20220531\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix20220531\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use RectorPrefix20220531\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use RectorPrefix20220531\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20220531\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use RectorPrefix20220531\Symplify\SmartFileSystem\Json\JsonFileSystem;
use RectorPrefix20220531\Symplify\SmartFileSystem\SmartFileSystem;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\RectorGenerator\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/ValueObject', __DIR__ . '/../src/Enum']);
    // console
    $services->set(\RectorPrefix20220531\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class);
    $services->set(\RectorPrefix20220531\Symfony\Component\Console\Style\SymfonyStyle::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20220531\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory::class), 'create']);
    // filesystem
    $services->set(\RectorPrefix20220531\Symplify\SmartFileSystem\Json\JsonFileSystem::class);
    $services->set(\RectorPrefix20220531\Symplify\SmartFileSystem\SmartFileSystem::class);
    $services->set(\RectorPrefix20220531\Symplify\SmartFileSystem\Finder\FinderSanitizer::class);
    $services->set(\RectorPrefix20220531\Symplify\SmartFileSystem\FileSystemGuard::class);
    // privates
    $services->set(\RectorPrefix20220531\Symplify\PackageBuilder\Reflection\PrivatesCaller::class);
    $services->set(\RectorPrefix20220531\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
    // php-parser
    $services->set(\PhpParser\PrettyPrinter\Standard::class)->arg('$options', ['shortArraySyntax' => \true]);
    $services->set(\PhpParser\ParserFactory::class);
    $services->set(\PhpParser\Parser::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\PhpParser\ParserFactory::class), 'create'])->arg('$kind', \PhpParser\ParserFactory::PREFER_PHP7);
};
