<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use RectorPrefix202208\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202208\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function RectorPrefix202208\Symfony\Component\DependencyInjection\Loader\Configurator\service;
use RectorPrefix202208\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix202208\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use RectorPrefix202208\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use RectorPrefix202208\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix202208\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use RectorPrefix202208\Symplify\SmartFileSystem\Json\JsonFileSystem;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileSystem;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\RectorGenerator\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/ValueObject', __DIR__ . '/../src/Enum']);
    // console
    $services->set(SymfonyStyleFactory::class);
    $services->set(SymfonyStyle::class)->factory([service(SymfonyStyleFactory::class), 'create']);
    // filesystem
    $services->set(JsonFileSystem::class);
    $services->set(SmartFileSystem::class);
    $services->set(FinderSanitizer::class);
    $services->set(FileSystemGuard::class);
    // privates
    $services->set(PrivatesCaller::class);
    $services->set(PrivatesAccessor::class);
    // php-parser
    $services->set(Standard::class)->arg('$options', ['shortArraySyntax' => \true]);
    $services->set(ParserFactory::class);
    $services->set(Parser::class)->factory([service(ParserFactory::class), 'create'])->arg('$kind', ParserFactory::PREFER_PHP7);
};
