<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use RectorPrefix20220209\Helmich\TypoScriptParser\Parser\AST\Builder;
use RectorPrefix20220209\Helmich\TypoScriptParser\Parser\Parser;
use RectorPrefix20220209\Helmich\TypoScriptParser\Parser\ParserInterface;
use RectorPrefix20220209\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface;
use RectorPrefix20220209\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinter;
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;
use RectorPrefix20220209\Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use RectorPrefix20220209\Helmich\TypoScriptParser\Tokenizer\TokenizerInterface;
use Rector\RectorGenerator\FileSystem\ConfigFilesystem;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\TypoScriptFileProcessor;
use Ssch\TYPO3Rector\Rector\PostRector\FullQualifiedNamePostRector;
use RectorPrefix20220209\Symfony\Component\Console\Output\BufferedOutput;
use RectorPrefix20220209\Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../utils/**/config/config.php', null, \true);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    $services->load('Ssch\\TYPO3Rector\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Rector', __DIR__ . '/../src/Set', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/FileProcessor/TypoScript/Conditions', __DIR__ . '/../src/FileProcessor/TypoScript/Rector', __DIR__ . '/../src/FileProcessor/TypoScript/PostRector', __DIR__ . '/../src/FileProcessor/Yaml/Form/Rector', __DIR__ . '/../src/FileProcessor/Composer/Rector', __DIR__ . '/../src/FileProcessor/FlexForms/Rector', __DIR__ . '/../src/FileProcessor/Resources/Icons/Rector', __DIR__ . '/../src/FileProcessor/Fluid/Rector']);
    $services->set(\Helmich\TypoScriptParser\Parser\Traverser\Traverser::class);
    $services->set(\RectorPrefix20220209\Helmich\TypoScriptParser\Tokenizer\Tokenizer::class);
    $services->alias(\RectorPrefix20220209\Helmich\TypoScriptParser\Tokenizer\TokenizerInterface::class, \RectorPrefix20220209\Helmich\TypoScriptParser\Tokenizer\Tokenizer::class);
    $services->set(\RectorPrefix20220209\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinter::class);
    $services->alias(\RectorPrefix20220209\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface::class, \RectorPrefix20220209\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinter::class);
    $services->set(\RectorPrefix20220209\Helmich\TypoScriptParser\Parser\Parser::class);
    $services->alias(\RectorPrefix20220209\Helmich\TypoScriptParser\Parser\ParserInterface::class, \RectorPrefix20220209\Helmich\TypoScriptParser\Parser\Parser::class);
    $services->set(\RectorPrefix20220209\Symfony\Component\Console\Output\BufferedOutput::class);
    $services->alias(\RectorPrefix20220209\Symfony\Component\Console\Output\OutputInterface::class, \RectorPrefix20220209\Symfony\Component\Console\Output\BufferedOutput::class);
    $services->set(\RectorPrefix20220209\Helmich\TypoScriptParser\Parser\AST\Builder::class);
    $services->set(\Ssch\TYPO3Rector\FileProcessor\TypoScript\TypoScriptFileProcessor::class)->call('configure', [['typoscript', 'ts', 'txt', 'pagets', 'constantsts', 'setupts', 'tsconfig', 't3s', 't3c', 'typoscriptconstants', 'typoscriptsetupts']]);
    // custom generator
    $services->set(\Rector\RectorGenerator\FileSystem\ConfigFilesystem::class);
    $services->set(\Rector\RectorGenerator\TemplateFactory::class);
    $services->set(\PhpParser\PrettyPrinter\Standard::class);
    $services->set(\Ssch\TYPO3Rector\Rector\PostRector\FullQualifiedNamePostRector::class);
};
