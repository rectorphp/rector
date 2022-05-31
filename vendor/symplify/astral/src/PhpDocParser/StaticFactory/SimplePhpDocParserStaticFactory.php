<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Symplify\Astral\PhpDocParser\StaticFactory;

use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use RectorPrefix20220531\Symplify\Astral\PhpDocParser\SimplePhpDocParser;
/**
 * @api
 */
final class SimplePhpDocParserStaticFactory
{
    public static function create() : \RectorPrefix20220531\Symplify\Astral\PhpDocParser\SimplePhpDocParser
    {
        $phpDocParser = new \PHPStan\PhpDocParser\Parser\PhpDocParser(new \PHPStan\PhpDocParser\Parser\TypeParser(), new \PHPStan\PhpDocParser\Parser\ConstExprParser());
        return new \RectorPrefix20220531\Symplify\Astral\PhpDocParser\SimplePhpDocParser($phpDocParser, new \PHPStan\PhpDocParser\Lexer\Lexer());
    }
}
