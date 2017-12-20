<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use Rector\BetterReflection\Reflection\ReflectionFunction;
use Rector\BetterReflection\Reflector\Exception\IdentifierNotFound;
use Rector\Contract\Parser\ParserInterface;
use Rector\NodeTraverser\CloningNodeTraverser;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\NodeTraverser\ShutdownNodeTraverser;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use SplFileInfo;

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
    private $rectorNodeTraverser;

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
        RectorNodeTraverser $rectorNodeTraverser,
        ShutdownNodeTraverser $shutdownNodeTraverser,
        StandaloneTraverseNodeTraverser $standaloneTraverseNodeTraverser
    ) {
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->cloningNodeTraverser = $cloningNodeTraverser;
        $this->shutdownNodeTraverser = $shutdownNodeTraverser;
        $this->standaloneTraverseNodeTraverser = $standaloneTraverseNodeTraverser;
    }

    /**
     * @return mixed[]
     */
    public function processFileInfo(SplFileInfo $fileInfo): array
    {
        $oldStmts = $this->parser->parseFile($fileInfo->getRealPath());
        $oldTokens = $this->lexer->getTokens();

        try {
            $newStmts = $this->cloningNodeTraverser->traverse($oldStmts);
            $newStmts = $this->standaloneTraverseNodeTraverser->traverse($newStmts);

            $newStmts = $this->rectorNodeTraverser->traverse($newStmts);
            $newStmts = $this->shutdownNodeTraverser->traverse($newStmts);

            return [$newStmts, $oldStmts, $oldTokens];
        } catch (IdentifierNotFound $identifierNotFoundException) {
            // could not locate function, skip and keep original
            $identifierType = $identifierNotFoundException->getIdentifier()->getType()->getName();
            if ($identifierType === ReflectionFunction::class) {
                // keep original
                return [$oldStmts, $oldStmts, $oldStmts];
            }

            $identifierName = $identifierNotFoundException->getIdentifier()->getName();

            // is single class? - probably test class
            if (! Strings::contains($identifierName, '\\')) {
                // keep original
                return [$oldStmts, $oldStmts, $oldStmts];
            }

            throw $identifierNotFoundException;
        }
    }
}
