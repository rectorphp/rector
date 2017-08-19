<?php declare(strict_types=1);

namespace Rector\Parser;

use PhpParser\Node;
use PhpParser\Parser as NikicParser;
use Rector\Contract\Parser\ParserInterface;
use Rector\Event\AfterParseEvent;
use Rector\EventDispatcher\ClassBasedEventDispatcher;

final class Parser implements ParserInterface
{
    /**
     * @var NikicParser
     */
    private $nikicParser;

    /**
     * @var Node[][]
     */
    private $nodesByFile = [];

    /**
     * @var ClassBasedEventDispatcher
     */
    private $eventDispatcher;

    public function __construct(NikicParser $nikicParser, ClassBasedEventDispatcher $eventDispatcher)
    {
        $this->nikicParser = $nikicParser;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Node[]
     */
    public function parseFile(string $filePath): array
    {
        if (isset($this->nodesByFile[$filePath])) {
            return $this->nodesByFile[$filePath];
        }

        $fileContent = file_get_contents($filePath);

        $nodes = $this->nikicParser->parse($fileContent);

        $this->nodesByFile[$filePath] = $this->processByEventDisptacher($nodes);

        return $this->nodesByFile[$filePath];
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function processByEventDisptacher(array $nodes): array
    {
        $afterParseEvent = new AfterParseEvent($nodes);
        $this->eventDispatcher->dispatch($afterParseEvent);

        return $afterParseEvent->getNodes();
    }
}
