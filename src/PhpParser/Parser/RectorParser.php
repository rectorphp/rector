<?php

declare (strict_types=1);
namespace Rector\PhpParser\Parser;

use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPStan\Parser\Parser;
use Rector\PhpParser\ValueObject\StmtsAndTokens;
use Rector\Util\Reflection\PrivatesAccessor;
final class RectorParser
{
    /**
     * @readonly
     */
    private Parser $parser;
    /**
     * @readonly
     */
    private PrivatesAccessor $privatesAccessor;
    public function __construct(Parser $parser, PrivatesAccessor $privatesAccessor)
    {
        $this->parser = $parser;
        $this->privatesAccessor = $privatesAccessor;
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
    public function parseFileContentToStmtsAndTokens(string $fileContent, bool $forNewestSupportedVersion = \true) : StmtsAndTokens
    {
        if (!$forNewestSupportedVersion) {
            // don't directly change PHPStan Parser service
            // to avoid reuse on next file
            $phpstanParser = clone $this->parser;
            $parserFactory = new ParserFactory();
            $parser = $parserFactory->createForVersion(PhpVersion::fromString('7.0'));
            $this->privatesAccessor->setPrivateProperty($phpstanParser, 'parser', $parser);
            return $this->resolveStmtsAndTokens($phpstanParser, $fileContent);
        }
        return $this->resolveStmtsAndTokens($this->parser, $fileContent);
    }
    private function resolveStmtsAndTokens(Parser $parser, string $fileContent) : StmtsAndTokens
    {
        $stmts = $parser->parseString($fileContent);
        $innerParser = $this->privatesAccessor->getPrivateProperty($parser, 'parser');
        $tokens = $innerParser->getTokens();
        return new StmtsAndTokens($stmts, $tokens);
    }
}
