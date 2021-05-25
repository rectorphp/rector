<?php

declare (strict_types=1);
namespace RectorPrefix20210525;

use RectorPrefix20210525\Helmich\TypoScriptParser\Parser\Parser;
use RectorPrefix20210525\Helmich\TypoScriptParser\Parser\ParserInterface;
use RectorPrefix20210525\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface;
use RectorPrefix20210525\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinter;
use RectorPrefix20210525\Helmich\TypoScriptParser\Parser\Traverser\Traverser;
use RectorPrefix20210525\Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use RectorPrefix20210525\Helmich\TypoScriptParser\Tokenizer\TokenizerInterface;
use Ssch\TYPO3Rector\Rector\Composer\ExtensionComposerRector;
use Ssch\TYPO3Rector\Rector\General\ConvertTypo3ConfVarsRector;
use Ssch\TYPO3Rector\Rector\General\ExtEmConfRector;
use Ssch\TYPO3Rector\TypoScript\TypoScriptProcessor;
use RectorPrefix20210525\Symfony\Component\Console\Output\BufferedOutput;
use RectorPrefix20210525\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210525\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\RectorPrefix20210525\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../utils/**/config/config.php', null, \true);
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire();
    $services->load('Ssch\\TYPO3Rector\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Rector', __DIR__ . '/../src/Set', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/TypoScript/Conditions', __DIR__ . '/../src/TypoScript/Visitors', __DIR__ . '/../src/FlexForms/Rector', __DIR__ . '/../src/Yaml/Form/Rector', __DIR__ . '/../src/Resources/Icons/Rector']);
    // Add some general TYPO3 rules
    $services->set(\Ssch\TYPO3Rector\Rector\General\ConvertTypo3ConfVarsRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\General\ExtEmConfRector::class);
    $services->set(\Ssch\TYPO3Rector\Rector\Composer\ExtensionComposerRector::class);
    $services->set(\RectorPrefix20210525\Helmich\TypoScriptParser\Parser\Traverser\Traverser::class);
    $services->set(\RectorPrefix20210525\Helmich\TypoScriptParser\Tokenizer\Tokenizer::class);
    $services->alias(\RectorPrefix20210525\Helmich\TypoScriptParser\Tokenizer\TokenizerInterface::class, \RectorPrefix20210525\Helmich\TypoScriptParser\Tokenizer\Tokenizer::class);
    $services->set(\RectorPrefix20210525\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinter::class);
    $services->alias(\RectorPrefix20210525\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface::class, \RectorPrefix20210525\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinter::class);
    $services->set(\RectorPrefix20210525\Helmich\TypoScriptParser\Parser\Parser::class);
    $services->alias(\RectorPrefix20210525\Helmich\TypoScriptParser\Parser\ParserInterface::class, \RectorPrefix20210525\Helmich\TypoScriptParser\Parser\Parser::class);
    $services->set(\RectorPrefix20210525\Symfony\Component\Console\Output\BufferedOutput::class);
    $services->alias(\RectorPrefix20210525\Symfony\Component\Console\Output\OutputInterface::class, \RectorPrefix20210525\Symfony\Component\Console\Output\BufferedOutput::class);
    $services->set(\Ssch\TYPO3Rector\TypoScript\TypoScriptProcessor::class)->call('configure', [[\Ssch\TYPO3Rector\TypoScript\TypoScriptProcessor::ALLOWED_FILE_EXTENSIONS => ['typoscript', 'ts', 'txt', 'pagets', 'constantsts', 'setupts', 'tsconfig', 't3s', 't3c', 'typoscriptconstants', 'typoscriptsetupts']]]);
};
