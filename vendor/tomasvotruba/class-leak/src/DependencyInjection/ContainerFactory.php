<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\ClassLeak\DependencyInjection;

use RectorPrefix202608\Entropy\Container\Container;
use PhpParser\Parser;
use PhpParser\ParserFactory;
/**
 * @api
 */
final class ContainerFactory
{
    /**
     * @api
     */
    public function create(): Container
    {
        $container = new Container();
        $container->autodiscover(__DIR__ . '/..');
        $container->service(Parser::class, static function (): Parser {
            $parserFactory = new ParserFactory();
            return $parserFactory->createForHostVersion();
        });
        return $container;
    }
}
