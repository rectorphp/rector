<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use PhpParser\ConstExprEvaluator;
use PhpParser\NodeFinder;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220531\Symplify\Astral\PhpParser\SmartPhpParser;
use RectorPrefix20220531\Symplify\Astral\PhpParser\SmartPhpParserFactory;
use RectorPrefix20220531\Symplify\PackageBuilder\Php\TypeChecker;
use function RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->autowire()->autoconfigure()->public();
    $services->load('RectorPrefix20220531\Symplify\\Astral\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/StaticFactory', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/NodeVisitor', __DIR__ . '/../src/PhpParser/SmartPhpParser.php', __DIR__ . '/../src/PhpDocParser/PhpDocNodeVisitor/CallablePhpDocNodeVisitor.php']);
    $services->set(\RectorPrefix20220531\Symplify\Astral\PhpParser\SmartPhpParser::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20220531\Symplify\Astral\PhpParser\SmartPhpParserFactory::class), 'create']);
    $services->set(\PhpParser\ConstExprEvaluator::class);
    $services->set(\RectorPrefix20220531\Symplify\PackageBuilder\Php\TypeChecker::class);
    $services->set(\PhpParser\NodeFinder::class);
    // phpdoc parser
    $services->set(\PHPStan\PhpDocParser\Parser\PhpDocParser::class);
    $services->set(\PHPStan\PhpDocParser\Lexer\Lexer::class);
    $services->set(\PHPStan\PhpDocParser\Parser\TypeParser::class);
    $services->set(\PHPStan\PhpDocParser\Parser\ConstExprParser::class);
};
