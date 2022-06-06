<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Symplify\Astral\PhpDocParser\StaticFactory;

use RectorPrefix20220606\PHPStan\PhpDocParser\Lexer\Lexer;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\ConstExprParser;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\PhpDocParser;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\TypeParser;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\SimplePhpDocParser;
/**
 * @api
 */
final class SimplePhpDocParserStaticFactory
{
    public static function create() : SimplePhpDocParser
    {
        $phpDocParser = new PhpDocParser(new TypeParser(), new ConstExprParser());
        return new SimplePhpDocParser($phpDocParser, new Lexer());
    }
}
