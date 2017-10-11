<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue;

use PhpParser\Lexer;
use Rector\Contract\Parser\ParserInterface;
use Rector\FileSystem\CurrentFileProvider;
use Rector\NodeTraverser\CloningNodeTraverser;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\NodeTraverser\ShutdownNodeTraverser;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\NodeTraverserQueue\Exception\FileProcessingException;
use Rector\BetterReflection\Reflection\ReflectionClass;
use Rector\BetterReflection\Reflection\ReflectionFunction;
use Rector\BetterReflection\Reflector\Exception\IdentifierNotFound;
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

    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

    public function __construct(
        ParserInterface $parser,
        Lexer $lexer,
        CloningNodeTraverser $cloningNodeTraverser,
        RectorNodeTraverser $rectorNodeTraverser,
        ShutdownNodeTraverser $shutdownNodeTraverser,
        StandaloneTraverseNodeTraverser $standaloneTraverseNodeTraverser,
        CurrentFileProvider $currentFileProvider
    ) {
        $this->parser = $parser;
        $this->lexer = $lexer;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->cloningNodeTraverser = $cloningNodeTraverser;
        $this->shutdownNodeTraverser = $shutdownNodeTraverser;
        $this->standaloneTraverseNodeTraverser = $standaloneTraverseNodeTraverser;
        $this->currentFileProvider = $currentFileProvider;
    }

    /**
     * @return mixed[]
     */
    public function processFileInfo(SplFileInfo $fileInfo): array
    {
        $this->currentFileProvider->setCurrentFile($fileInfo);

        try {
            $oldStmts = $this->parser->parseFile($fileInfo->getRealPath());
            $oldTokens = $this->lexer->getTokens();

            $newStmts = $this->cloningNodeTraverser->traverse($oldStmts);
            $newStmts = $this->standaloneTraverseNodeTraverser->traverse($newStmts);

            $newStmts = $this->rectorNodeTraverser->traverse($newStmts);
            $newStmts = $this->shutdownNodeTraverser->traverse($newStmts);

            return [$newStmts, $oldStmts, $oldTokens];
        } catch (IdentifierNotFound $identifierNotFoundException) {
            // could not locate function, skip and keep original
            $identifierType = $identifierNotFoundException->getIdentifier()->getType()->getName();
            if (in_array($identifierType, [ReflectionFunction::class, ReflectionClass::class], true)) {
                // keep original
                return [$oldStmts, $oldStmts, $oldStmts];
            }
        } catch (Throwable $throwable) {
            throw new FileProcessingException($fileInfo, $throwable);
        }
    }
}
