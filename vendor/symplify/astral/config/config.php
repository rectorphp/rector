<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\PhpParser\ConstExprEvaluator;
use RectorPrefix20220606\PhpParser\NodeFinder;
use RectorPrefix20220606\PHPStan\PhpDocParser\Lexer\Lexer;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\ConstExprParser;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\PhpDocParser;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\TypeParser;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220606\Symplify\Astral\PhpParser\SmartPhpParser;
use RectorPrefix20220606\Symplify\Astral\PhpParser\SmartPhpParserFactory;
use RectorPrefix20220606\Symplify\PackageBuilder\Php\TypeChecker;
use function RectorPrefix20220606\Symfony\Component\DependencyInjection\Loader\Configurator\service;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->autowire()->autoconfigure()->public();
    $services->load('RectorPrefix20220606\Symplify\\Astral\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/StaticFactory', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/NodeVisitor', __DIR__ . '/../src/PhpParser/SmartPhpParser.php', __DIR__ . '/../src/PhpDocParser/PhpDocNodeVisitor/CallablePhpDocNodeVisitor.php']);
    $services->set(SmartPhpParser::class)->factory([service(SmartPhpParserFactory::class), 'create']);
    $services->set(ConstExprEvaluator::class);
    $services->set(TypeChecker::class);
    $services->set(NodeFinder::class);
    // phpdoc parser
    $services->set(PhpDocParser::class);
    $services->set(Lexer::class);
    $services->set(TypeParser::class);
    $services->set(ConstExprParser::class);
};
