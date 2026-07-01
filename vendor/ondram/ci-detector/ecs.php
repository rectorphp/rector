<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use RectorPrefix202607\PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use RectorPrefix202607\PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use RectorPrefix202607\PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer;
use RectorPrefix202607\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix202607\Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use RectorPrefix202607\Symplify\EasyCodingStandard\ValueObject\Option;
return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SKIP, [__DIR__ . '/tests/PropertiesPrinterHelper.php']);
    $containerConfigurator->import(__DIR__ . '/vendor/lmc/coding-standard/ecs.php');
    $services = $containerConfigurator->services();
    // Use single-line phpdoc where possible
    $services->set(PhpdocLineSpanFixer::class)->call('configure', [['property' => 'single']]);
    // Tests must have @test annotation
    $services->set(PhpUnitTestAnnotationFixer::class)->call('configure', [['style' => 'annotation']]);
    $services->set(OrderedClassElementsFixer::class);
    // Force line length
    $services->set(LineLengthFixer::class)->call('configure', [['line_length' => 120, 'break_long_lines' => \true, 'inline_short_lines' => \false]]);
};
