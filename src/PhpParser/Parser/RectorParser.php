<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Lexer;
use PhpParser\Node\Stmt;
use PHPStan\Parser\Parser;
use Rector\Core\PhpParser\ValueObject\StmtsAndTokens;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorParser
{
    public function __construct(
        private Lexer $lexer,
        private Parser $parser,
    ) {
    }

    /**
     * @return Stmt[]
     */
    public function parseFile(SmartFileInfo $smartFileInfo): array
    {
        return $this->parser->parseFile($smartFileInfo->getRealPath());
    }

    public function parseFileToStmtsAndTokens(SmartFileInfo $smartFileInfo): StmtsAndTokens
    {
        $stmts = $this->parseFile($smartFileInfo);
        $tokens = $this->lexer->getTokens();

        return new StmtsAndTokens($stmts, $tokens);
    }
}
