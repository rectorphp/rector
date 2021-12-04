<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Parser;

use PhpParser\Lexer;
use PhpParser\Node\Stmt;
use PHPStan\Parser\Parser;
use Rector\Core\PhpParser\ValueObject\StmtsAndTokens;
use Symplify\SmartFileSystem\SmartFileInfo;
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
    public function __construct(\PhpParser\Lexer $lexer, \PHPStan\Parser\Parser $parser)
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
    }
    /**
     * @return Stmt[]
     */
    public function parseFile(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        return $this->parser->parseFile($smartFileInfo->getRealPath());
    }
    public function parseFileToStmtsAndTokens(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : \Rector\Core\PhpParser\ValueObject\StmtsAndTokens
    {
        $stmts = $this->parseFile($smartFileInfo);
        $tokens = $this->lexer->getTokens();
        return new \Rector\Core\PhpParser\ValueObject\StmtsAndTokens($stmts, $tokens);
    }
}
