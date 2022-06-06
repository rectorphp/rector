<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\AST\Builder;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\Parser;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\ParserInterface;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinter;
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;
use RectorPrefix20220606\Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use RectorPrefix20220606\Helmich\TypoScriptParser\Tokenizer\TokenizerInterface;
use RectorPrefix20220606\Rector\RectorGenerator\FileSystem\ConfigFilesystem;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\TypoScriptFileProcessor;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\PostRector\FullQualifiedNamePostRector;
use RectorPrefix20220606\Symfony\Component\Console\Output\BufferedOutput;
use RectorPrefix20220606\Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../utils/**/config/config.php', null, \true);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    $services->load('Ssch\\TYPO3Rector\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Rector', __DIR__ . '/../src/Set', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/FileProcessor/TypoScript/Conditions', __DIR__ . '/../src/FileProcessor/TypoScript/Rector', __DIR__ . '/../src/FileProcessor/TypoScript/PostRector', __DIR__ . '/../src/FileProcessor/Yaml/Form/Rector', __DIR__ . '/../src/FileProcessor/Composer/Rector', __DIR__ . '/../src/FileProcessor/FlexForms/Rector', __DIR__ . '/../src/FileProcessor/Resources/Icons/Rector', __DIR__ . '/../src/FileProcessor/Resources/Files/Rector', __DIR__ . '/../src/FileProcessor/Fluid/Rector']);
    $services->set(Traverser::class);
    $services->set(Tokenizer::class);
    $services->alias(TokenizerInterface::class, Tokenizer::class);
    $services->set(PrettyPrinter::class);
    $services->alias(ASTPrinterInterface::class, PrettyPrinter::class);
    $services->set(Parser::class);
    $services->alias(ParserInterface::class, Parser::class);
    $services->set(BufferedOutput::class);
    $services->alias(OutputInterface::class, BufferedOutput::class);
    $services->set(Builder::class);
    $services->set(TypoScriptFileProcessor::class)->call('configure', [['typoscript', 'ts', 'txt', 'pagets', 'constantsts', 'setupts', 'tsconfig', 't3s', 't3c', 'typoscriptconstants', 'typoscriptsetupts']]);
    // custom generator
    $services->set(ConfigFilesystem::class);
    $services->set(\RectorPrefix20220606\Rector\RectorGenerator\TemplateFactory::class);
    $services->set(\RectorPrefix20220606\PhpParser\PrettyPrinter\Standard::class);
    $services->set(FullQualifiedNamePostRector::class);
};
