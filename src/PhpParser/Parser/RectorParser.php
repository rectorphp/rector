<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Lexer;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use Rector\Core\PhpParser\ValueObject\StmtsAndTokens;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class RectorParser
{
    /**
     * @var array<string, Stmt[]>
     */
    private array $nodesByFile = [];

    public function __construct(
        private Parser $parser,
        private SmartFileSystem $smartFileSystem,
        private Lexer $lexer
    ) {
    }

    /**
     * @return Stmt[]
     */
    public function parseFile(SmartFileInfo $smartFileInfo): array
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

    public function parseFileToStmtsAndTokens(SmartFileInfo $smartFileInfo): StmtsAndTokens
    {
        $stmts = $this->parseFile($smartFileInfo);
        $tokens = $this->lexer->getTokens();

        return new StmtsAndTokens($stmts, $tokens);
    }
}
