<?php

declare (strict_types=1);
namespace Rector\PhpParser\Parser;

use PhpParser\Lexer;
use PhpParser\Node\Stmt;
use PHPStan\Parser\Parser;
use Rector\PhpParser\ValueObject\StmtsAndTokens;
final class RectorParser
{
    /**
     * @readonly
     * @var \PhpParser\Lexer
     */
    private $lexer;
    /**
     * @readonly
     * @var \PHPStan\Parser\Parser
     */
    private $parser;
    public function __construct(Lexer $lexer, Parser $parser)
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
    }
    /**
     * @api used by rector-symfony
     *
     * @return Stmt[]
     */
    public function parseFile(string $filePath) : array
    {
        return $this->parser->parseFile($filePath);
    }
    /**
     * @return Stmt[]
     */
    public function parseString(string $fileContent) : array
    {
        return $this->parser->parseString($fileContent);
    }
    public function parseFileContentToStmtsAndTokens(string $fileContent) : StmtsAndTokens
    {
        $stmts = $this->parser->parseString($fileContent);
        $tokens = $this->lexer->getTokens();
        return new StmtsAndTokens($stmts, $tokens);
    }
}
