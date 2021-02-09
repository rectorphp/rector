<?php

declare(strict_types=1);

use Composer\Semver\VersionParser;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Rector\Core\Bootstrap\NoRectorsLoadedReporter;
use Rector\Core\Console\ConsoleApplication;
use Rector\Core\PhpParser\Parser\NikicPhpParserFactory;
use Rector\Core\PhpParser\Parser\PhpParserLexerFactory;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Descriptor\TextDescriptor;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Php\TypeChecker;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;
use Symplify\PackageBuilder\Strings\StringFormatConverter;
use Symplify\SmartFileSystem\FileSystemFilter;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\Json\JsonFileSystem;
use Symplify\SmartFileSystem\SmartFileSystem;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\Core\\', __DIR__ . '/../src')
        ->exclude([
            __DIR__ . '/../src/Rector',
            __DIR__ . '/../src/Exception',
            __DIR__ . '/../src/DependencyInjection/CompilerPass',
            __DIR__ . '/../src/DependencyInjection/Loader',
            __DIR__ . '/../src/HttpKernel',
            __DIR__ . '/../src/ValueObject',
            __DIR__ . '/../src/Bootstrap',
            __DIR__ . '/../src/PhpParser/Node/CustomNode',
        ]);

    $services->alias(SymfonyApplication::class, ConsoleApplication::class);

    $services->set(NoRectorsLoadedReporter::class);
    $services->set(\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser::class);

    $services->set(TextDescriptor::class);

    $services->set(ParserFactory::class);
    $services->set(BuilderFactory::class);
    $services->set(CloningVisitor::class);
    $services->set(NodeFinder::class);

    $services->set(Parser::class)
        ->factory([service(NikicPhpParserFactory::class), 'create']);

    $services->set(Lexer::class)
        ->factory([service(PhpParserLexerFactory::class), 'create']);

    // symplify/package-builder
    $services->set(Filesystem::class);
    $services->set(PrivatesAccessor::class);
    $services->set(PrivatesCaller::class);
    $services->set(FinderSanitizer::class);
    $services->set(FileSystemFilter::class);
    $services->set(ParameterProvider::class)
        ->arg('$container', service('service_container'));

    $services->set(CommandNaming::class);

    $services->set(SmartFileSystem::class);

    $services->set(StringFormatConverter::class);

    $services->set(SymfonyStyleFactory::class);
    $services->set(SymfonyStyle::class)
        ->factory([service(SymfonyStyleFactory::class), 'create']);

    $services->set(JsonFileSystem::class);

    $services->set(InflectorFactory::class);

    $services->set(Inflector::class)
        ->factory([service(InflectorFactory::class), 'build']);

    $services->set(VersionParser::class);

    $services->set(TypeChecker::class);
};
