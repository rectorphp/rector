<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\PhpParser;

use PhpParser\Node\Stmt;
use PHPStan\Parser\Parser;
/**
 * @see \Rector\PhpDocParser\PhpParser\SmartPhpParserFactory
 *
 * @api
 */
final class SmartPhpParser
{
    /**
     * @readonly
     * @var \PHPStan\Parser\Parser
     */
    private $parser;
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }
    /**
     * @return Stmt[]
     */
    public function parseFile(string $file) : array
    {
        return $this->parser->parseFile($file);
    }
    /**
     * @return Stmt[]
     */
    public function parseString(string $sourceCode) : array
    {
        return $this->parser->parseString($sourceCode);
    }
}
