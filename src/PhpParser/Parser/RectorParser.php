<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\PhpParser\Parser;

use RectorPrefix20220606\PhpParser\Lexer;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PHPStan\Parser\Parser;
use RectorPrefix20220606\Rector\Core\PhpParser\ValueObject\StmtsAndTokens;
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
    public function __construct(Lexer $lexer, Parser $parser)
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
    }
    /**
     * @return Stmt[]
     */
    public function parseFile(SmartFileInfo $smartFileInfo) : array
    {
        return $this->parser->parseFile($smartFileInfo->getRealPath());
    }
    public function parseFileToStmtsAndTokens(SmartFileInfo $smartFileInfo) : StmtsAndTokens
    {
        $stmts = $this->parseFile($smartFileInfo);
        $tokens = $this->lexer->getTokens();
        return new StmtsAndTokens($stmts, $tokens);
    }
}
