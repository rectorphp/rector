<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Symplify\Astral\PhpParser;

use PhpParser\Lexer\Emulative;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPStan\Parser\CachedParser;
use PHPStan\Parser\SimpleParser;
/**
 * Based on PHPStan-based PHP-Parser best practices:
 *
 * @see https://github.com/rectorphp/rector/issues/6744#issuecomment-950282826
 * @see https://github.com/phpstan/phpstan-src/blob/99e4ae0dced58fe0be7a7aec3168a5e9d639240a/conf/config.neon#L1669-L1691
 */
final class SmartPhpParserFactory
{
    public function create() : \RectorPrefix20220531\Symplify\Astral\PhpParser\SmartPhpParser
    {
        $nativePhpParser = $this->createNativePhpParser();
        $cachedParser = $this->createPHPStanParser($nativePhpParser);
        return new \RectorPrefix20220531\Symplify\Astral\PhpParser\SmartPhpParser($cachedParser);
    }
    private function createNativePhpParser() : \PhpParser\Parser
    {
        $parserFactory = new \PhpParser\ParserFactory();
        $lexerEmulative = new \PhpParser\Lexer\Emulative();
        return $parserFactory->create(\PhpParser\ParserFactory::PREFER_PHP7, $lexerEmulative);
    }
    private function createPHPStanParser(\PhpParser\Parser $parser) : \PHPStan\Parser\CachedParser
    {
        $nameResolver = new \PhpParser\NodeVisitor\NameResolver();
        $simpleParser = new \PHPStan\Parser\SimpleParser($parser, $nameResolver);
        return new \PHPStan\Parser\CachedParser($simpleParser, 1024);
    }
}
