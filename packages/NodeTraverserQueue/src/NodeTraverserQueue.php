<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue;

use PhpParser\Lexer;
use Rector\Contract\Parser\ParserInterface;
use Rector\NodeTraverser\CloningNodeTraverser;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\NodeTraverser\ShutdownNodeTraverser;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\NodeTraverserQueue\Exception\FileProcessingException;
use SplFileInfo;
use Throwable;

final class NodeTraverserQueue
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var RectorNodeTraverser
     */
    private $mainNodeTraverser;

    /**
     * @var CloningNodeTraverser
     */
    private $cloningNodeTraverser;

    /**
     * @var ShutdownNodeTraverser
     */
    private $shutdownNodeTraverser;

    /**
     * @var StandaloneTraverseNodeTraverser
     */
    private $standaloneTraverseNodeTraverser;

    public function __construct(
        ParserInterface $parser,
        Lexer $lexer,
        CloningNodeTraverser $cloningNodeTraverser,
        RectorNodeTraverser $mainNodeTraverser,
        ShutdownNodeTraverser $shutdownNodeTraverser,
        StandaloneTraverseNodeTraverser $standaloneTraverseNodeTraverser
    ) {
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->mainNodeTraverser = $mainNodeTraverser;
        $this->cloningNodeTraverser = $cloningNodeTraverser;
        $this->shutdownNodeTraverser = $shutdownNodeTraverser;
        $this->standaloneTraverseNodeTraverser = $standaloneTraverseNodeTraverser;
    }

    /**
     * @return mixed[]
     */
    public function processFileInfo(SplFileInfo $fileInfo): array
    {
        try {
            $oldStmts = $this->parser->parseFile($fileInfo->getRealPath());
            $oldTokens = $this->lexer->getTokens();

            $newStmts = $this->cloningNodeTraverser->traverse($oldStmts);
            $newStmts = $this->standaloneTraverseNodeTraverser->traverse($newStmts);
            $newStmts = $this->mainNodeTraverser->traverse($newStmts);
            $newStmts = $this->shutdownNodeTraverser->traverse($newStmts);

            return [$newStmts, $oldStmts, $oldTokens];
        } catch (Throwable $throwable) {
            throw new FileProcessingException(sprintf(
                'Processing file "%s" failed due to: "%s"',
                $fileInfo->getRealPath(),
                $throwable->getMessage()
            ));
        }
    }
}
