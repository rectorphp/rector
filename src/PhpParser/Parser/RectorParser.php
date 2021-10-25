<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Parser;

use PhpParser\Lexer;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use Rector\Core\PhpParser\ValueObject\StmtsAndTokens;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20211025\Symplify\SmartFileSystem\SmartFileSystem;
final class RectorParser
{
    /**
     * @var array<string, Stmt[]>
     */
    private $nodesByFile = [];
    /**
     * @var \PhpParser\Parser
     */
    private $parser;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \PhpParser\Lexer
     */
    private $lexer;
    public function __construct(\PhpParser\Parser $parser, \RectorPrefix20211025\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \PhpParser\Lexer $lexer)
    {
        $this->parser = $parser;
        $this->smartFileSystem = $smartFileSystem;
        $this->lexer = $lexer;
    }
    /**
     * @return Stmt[]
     */
    public function parseFile(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        $fileRealPath = $smartFileInfo->getRealPath();
        if (isset($this->nodesByFile[$fileRealPath])) {
            return $this->nodesByFile[$fileRealPath];
        }
        $fileContent = $this->smartFileSystem->readFile($fileRealPath);
        $nodes = $this->parser->parse($fileContent);
        if ($nodes === null) {
            $nodes = [];
        }
        $this->nodesByFile[$fileRealPath] = $nodes;
        return $this->nodesByFile[$fileRealPath];
    }
    public function parseFileToStmtsAndTokens(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : \Rector\Core\PhpParser\ValueObject\StmtsAndTokens
    {
        $stmts = $this->parseFile($smartFileInfo);
        $tokens = $this->lexer->getTokens();
        return new \Rector\Core\PhpParser\ValueObject\StmtsAndTokens($stmts, $tokens);
    }
}
