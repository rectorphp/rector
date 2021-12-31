<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use PhpParser\ConstExprEvaluator;
use PhpParser\NodeFinder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20211231\Symplify\Astral\PhpParser\SmartPhpParser;
use RectorPrefix20211231\Symplify\Astral\PhpParser\SmartPhpParserFactory;
use RectorPrefix20211231\Symplify\PackageBuilder\Php\TypeChecker;
use function RectorPrefix20211231\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->autowire()->autoconfigure()->public();
    $services->load('RectorPrefix20211231\Symplify\\Astral\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/StaticFactory', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/NodeVisitor', __DIR__ . '/../src/PhpParser/SmartPhpParser.php']);
    $services->set(\RectorPrefix20211231\Symplify\Astral\PhpParser\SmartPhpParser::class)->factory([\RectorPrefix20211231\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20211231\Symplify\Astral\PhpParser\SmartPhpParserFactory::class), 'create']);
    $services->set(\PhpParser\ConstExprEvaluator::class);
    $services->set(\RectorPrefix20211231\Symplify\PackageBuilder\Php\TypeChecker::class);
    $services->set(\PhpParser\NodeFinder::class);
};
